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
						$cart->addProduct($item->getProduct(), $params);
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
			else if ($_POST['field2'] == 'newsletter'){
				if ($_POST['field3'] == "true"){
					$_SESSION['newsletter_signup'] = true;
				}
				else {
					$_SESSION['newsletter_signup'] = false;
				}
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
							$cart->removeItem($item->getId())->save();
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
			else if ($_POST['field2'] == 'updatecustomer'){
				$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
				$cart = $objectManager->get('\Magento\Checkout\Model\Cart');
				try {
					$resp = $this->getCheckoutData();
					if (isset($resp['data']['businessCustomer']['invoiceAddress'])){
						$scompany	= $resp['data']['businessCustomer']['deliveryAddress']['companyName'];
						$sfirstname	= $resp['data']['businessCustomer']['firstName'];
						$slastname	= $resp['data']['businessCustomer']['lastName'];
						if (isset($resp['data']['businessCustomer']['deliveryAddress']['address'])){
							$sstreet = $resp['data']['businessCustomer']['deliveryAddress']['address'];
						}
						else {
							$sstreet = $resp['data']['businessCustomer']['deliveryAddress']['postalCode'];
						}
						$sstreet	= $resp['data']['businessCustomer']['deliveryAddress']['address'];
						$scity		= $resp['data']['businessCustomer']['deliveryAddress']['city'];
						$spostcode	= $resp['data']['businessCustomer']['deliveryAddress']['postalCode'];
						$stelephone = $resp['data']['businessCustomer']['mobilePhoneNumber'];

						$bcompany	= $resp['data']['businessCustomer']['invoiceAddress']['companyName'];
						$bfirstname	= $resp['data']['businessCustomer']['firstName'];
						$blastname	= $resp['data']['businessCustomer']['lastName'];
						if (isset($resp['data']['businessCustomer']['invoiceAddress']['address'])){
							$bstreet = $resp['data']['businessCustomer']['invoiceAddress']['address'];
						}
						else {
							$bstreet = $resp['data']['businessCustomer']['invoiceAddress']['postalCode'];
						}
						$bcity		= $resp['data']['businessCustomer']['invoiceAddress']['city'];
						$bpostcode	= $resp['data']['businessCustomer']['invoiceAddress']['postalCode'];
						$btelephone = $resp['data']['businessCustomer']['mobilePhoneNumber'];
					}
					else {
						$scompany	= '';
						$sfirstname	= $resp['data']['customer']['deliveryAddress']['firstName'];
						$slastname	= $resp['data']['customer']['deliveryAddress']['lastName'];
						$sstreet	= $resp['data']['customer']['deliveryAddress']['address'];
						$scity		= $resp['data']['customer']['deliveryAddress']['city'];
						$spostcode	= $resp['data']['customer']['deliveryAddress']['postalCode'];
						$stelephone = $resp['data']['customer']['mobilePhoneNumber'];

						$bcompany	= '';
						$bfirstname	= $resp['data']['customer']['billingAddress']['firstName'];
						$blastname	= $resp['data']['customer']['billingAddress']['lastName'];
						$bstreet	= $resp['data']['customer']['billingAddress']['address'];
						$bcity		= $resp['data']['customer']['billingAddress']['city'];
						$bpostcode	= $resp['data']['customer']['billingAddress']['postalCode'];
						$btelephone = $resp['data']['customer']['mobilePhoneNumber'];
					}
					$cart = $this->objectManager->get('\Magento\Checkout\Model\Cart');
					$cart->getQuote()->getBillingAddress()->addData(array(
						'firstname' => $bfirstname,
						'lastname' => $blastname,
						'street' => $bstreet,
						'city' => $bcity,
						'postcode' => $bpostcode,
						'telephone' => $btelephone
					));
					$cart->getQuote()->getShippingAddress()->addData(array(
						'firstname' => $sfirstname,
						'lastname' => $slastname,
						'street' => $sstreet,
						'city' => $scity,
						'postcode' => $spostcode
					));
					$cart->getQuote()->getShippingAddress()->save();
					$cart->getQuote()->collectTotals();
					$this->helper->getShippingMethods();
					$cart->getQuote()->save();
					$updateCart = true;
					$updateFees = true;
				}
				catch (\Exception $e){}
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
	
	private function getCheckoutData(){
		$pid = $_SESSION['collector_private_id'];
		$pusername = $this->helper->getUsername();
		$psharedSecret= $this->helper->getPassword();
		$array = array();
		$array['countryCode'] = $this->helper->getCountryCode();
		$storeId = 0;
		if (isset($_SESSION['btype'])){
			if ($_SESSION['btype'] == 'b2b'){
				$storeId = $this->helper->getB2BStoreID();
			}
			else {
				$storeId = $this->helper->getB2CStoreID();
			}
		}
		else {
			switch ($this->getCustomerType()){
				case 1:
					$_SESSION['btype'] = 'b2c';
					$storeId = $this->helper->getB2CStoreID();
				break;
				case 2:
					$_SESSION['btype'] = 'b2b';
					$storeId = $this->helper->getB2BStoreID();
				break;
				case 3:
					$_SESSION['btype'] = 'b2c';
					$storeId = $this->helper->getB2CStoreID();
				break;
			}
		}
		$path = '/merchants/'.$storeId.'/checkouts/'.$pid;
		$json = json_encode($array);
		$hash = $pusername.":".hash("sha256",$path.$psharedSecret);
		$hashstr = 'SharedKey '.base64_encode($hash); 
		$ch = curl_init($this->helper->getWSDL()."merchants/".$storeId."/checkouts/".$pid);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('charset=utf-8','Authorization:'.$hashstr));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$output = curl_exec($ch);
		$data = json_decode($output,true);
		curl_close($ch);
		return $data;
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
