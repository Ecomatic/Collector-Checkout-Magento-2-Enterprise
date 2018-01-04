<?php
/**
 * A Magento 2 module named Collector/Iframe
 * Copyright (C) 2017 Collector
 * 
 * This file is part of Collector/Iframe.
 * 
 * Collector/Iframe is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Collector\Iframe\Controller\Cajax;

class Cajax extends \Magento\Framework\App\Action\Action
{

    protected $resultPageFactory;
    protected $jsonHelper;
	protected $layoutFactory;
    protected $objectManager;
	protected $resultJsonFactory;
	protected $helper;
    protected $formKey;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context  $context
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    public function __construct(
		\Magento\Framework\View\Result\LayoutFactory $_layoutFactory,
		\Collector\Iframe\Helper\Data $_helper,
        \Magento\Framework\App\Action\Context $context,
		\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
        $this->formKey = $formKey;
		$this->helper = $_helper;
		$this->layoutFactory = $_layoutFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->jsonHelper = $jsonHelper;
		$this->resultJsonFactory = $resultJsonFactory;
		$this->objectManager = $context->getObjectManager();
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute(){
		$result = $this->resultJsonFactory->create();
		$updateCart = false;
		$updateFees = false;
		$changeLanguage = false;
		if ($this->getRequest()->isAjax()) {
			$changed = false;
			if ($_POST['field2'] == 'sub'){
				$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
				$cart = $objectManager->get('\Magento\Checkout\Model\Cart');
				$product = $objectManager->get('\Magento\Catalog\Model\Product');
				$allItems = $cart->getQuote()->getAllVisibleItems();
				$id = explode('_', $_POST['field3'])[1];
				foreach ($allItems as $item) {
					if ($item->getId() == $id){
						$qty = $item->getQty();
						if ($qty > 1){
							$item->setQty($qty-1);
						}
						else {
							if (count($allItems) == 1){
								$this->helper->clearSession();
								return $result->setData("redirect");
							}
							else {
								$cart->getQuote()->removeItem($item->getId());
							}
						}
						$cart->save();
						$updateCart = true;
						$updateFees = true;
						$changed = true;
					}
				}
			}
			else if ($_POST['field2'] == 'inc'){
				$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
				$cart = $objectManager->get('\Magento\Checkout\Model\Cart');
				$product = $objectManager->get('\Magento\Catalog\Model\Product');
				$allItems = $cart->getQuote()->getAllVisibleItems();
				$id = explode('_', $_POST['field3'])[1];
				foreach ($allItems as $item){
					if ($item->getId() == $id){
						if ($item->getProduct()->getTypeId() == 'configurable'){
							$params = array(
								'form_key' => $this->formKey->getFormKey(),
								'product' => $item->getProduct()->getId(),
								'super_attribute' => $item->getBuyRequest()->getData()['super_attribute'],
								'qty' => 1,
								'price' => $item->getProduct()->getPrice()
							);
						}
						else {
							$params = array(
								'form_key' => $this->formKey->getFormKey(),
								'product' => $item->getProduct()->getId(),
								'qty' => 1,
								'price' => $item->getProduct()->getPrice()
							);
						}
						$_product = $product->load($item->getProduct()->getId());
						$cart->addProduct($_product, $params);
						$cart->save();
						$changed = true;
						$updateCart = true;
						$updateFees = true;
					}
				}
			}
			else if ($_POST['field2'] == 'radio'){
				$price = $this->helper->setShippingMethod($_POST['field3']);
				$changed = true;
				$updateFees = true;
			}	
			else if ($_POST['field2'] == 'submit'){
			//	if ($this->helper->setDiscountCode($_POST['field3'])){
					if (isset($_SESSION['collector_applied_discount_code'])){
						$this->helper->unsetDiscountCode();
					}
					else {
						$this->helper->setDiscountCode($_POST['field3']);
					}
					$changed = true;
					$updateCart = true;
					$updateFees = true;
			//	}
			//	else {

			//	}
			}
			else if ($_POST['field2'] == 'del'){
				$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
				$cart = $objectManager->get('\Magento\Checkout\Model\Cart');
				$product = $objectManager->get('\Magento\Catalog\Model\Product');
				$allItems = $cart->getQuote()->getAllVisibleItems();
				$id = explode('_', $_POST['field3'])[1];
				foreach ($allItems as $item) {
					if ($item->getId() == $id){
						if (count($allItems) == 1){
							$this->helper->clearSession();
							return $result->setData("redirect");
						}
						else {
							$cart->getQuote()->removeItem($item->getId());
						}
						$cart->save();
						$changed = true;
						$updateCart = true;
						$updateFees = true;
					}
				}
			}
			else if ($_POST['field2'] == 'update'){
				$changed = true;
			}
			else if ($_POST['field2'] == 'btype'){
				$_SESSION['btype'] = $_POST['field3'];
				unset($_SESSION['collector_public_token']);
				$changeLanguage = true;
				$changed = true;
				$updateCart = true;
				$updateFees = true;
			}
			if ($changed){
				if ($updateCart){
					$this->updateCart();
				}
				if ($updateFees){
					$this->updateFees();
				}
				$page = $this->resultPageFactory->create();
				$layout = $page->getLayout();
				$block = $layout->getBlock('collectorcart');
				$block->setTemplate('Collector_Iframe::Cart.phtml');
				$html = $block->toHtml();
				if ($changeLanguage){
					$checkoutBlock = $layout->getBlock('collectorcheckout');
					$checkoutBlock->setTemplate('Collector_Iframe::Checkout.phtml');
					$checkoutHtml = $checkoutBlock->toHtml();
					$return = array(
						'cart'=>$html,
						'checkout'=>$checkoutHtml
					);
					return $result->setData($return);
				}
				else {
					$return = array(
						'cart'=>$html
					);
					return $result->setData($return);
				}
			}
		}
		return $result->setData("testing");
    }
	
	private function updateCart(){
		$this->helper->updateCart();
	}
	
	private function updateFees(){
		$this->helper->updateFees();
	}

    /**
     * Create json response
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function jsonResponse($response = '')
    {
        return $this->getResponse()->representJson(
            $this->jsonHelper->jsonEncode($response)
        );
    }
}
