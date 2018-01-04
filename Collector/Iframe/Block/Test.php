<?php

namespace Collector\Iframe\Block;
 
class Test extends \Magento\Checkout\Block\Onepage {
	protected $objectManager;
	protected $storeManager;
	protected $helper;
    protected $shippingRate;
	
	private $initialized = false;

    public function __construct(
		\Magento\Framework\View\Element\Template\Context $context, 
		array $data = [],
		\Magento\Framework\ObjectManagerInterface $_objectManager,
		\Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Checkout\Helper\Data $checkoutHelper,
		\Magento\Quote\Model\Quote\Address\Rate $_shippingRate,
		\Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Checkout\Model\CompositeConfigProvider $configProvider,
		\Collector\Iframe\Helper\Data $_helper,
        array $layoutProcessors = []
	){
        parent::__construct($context, $formKey, $configProvider, $layoutProcessors, $data);
		$this->objectManager = $_objectManager;
		$this->helper = $_helper;
		$this->shippingRate = $_shippingRate;
        $this->storeManager = $context->getStoreManager();
		$this->init();
	}
	
	protected function init(){
		if ($this->initialized){
			return;
		}
		$cart = $this->objectManager->get('\Magento\Checkout\Model\Cart');
		$cart->getQuote()->getBillingAddress()->addData(array(
			'firstname' => 'Kalle',
			'lastname' => 'Anka',
			'street' => 'Ankgatan',
			'city' => 'Ankeborg',
			'country_id' => 'SE',
			'postcode' => '12345',
			'telephone' => '0123456789'
		));
		$cart->getQuote()->getShippingAddress()->addData(array(
			'firstname' => 'Kalle',
			'lastname' => 'Anka',
			'street' => 'Ankgatan',
			'city' => 'Ankeborg',
			'country_id' => 'SE',
			'postcode' => '12345'
		));
		$cart->getQuote()->getShippingAddress()->save();
		$cart->getQuote()->collectTotals();
		$cart->getQuote()->save();
	}
	
	protected function _toHtml(){
		return parent::_toHtml();
	}
	
	public function getProducts(){
		return $this->helper->getBlockProducts();
	}
	
	public function getBtnEnable(){
		return $this->helper->getBtnEnable();
	}
	
	public function getShippingPrice(){
		return $this->helper->getShippingPrice();
	}
	
	public function hasDiscount(){
		return $this->helper->hasDiscount();
	}
	
	public function getDiscount(){
		return $this->helper->getDiscount();
	}
	
	public function getTax(){
		return $this->helper->getTax();
	}

	public function getGrandTotal(){
		return $this->helper->getGrandTotal();
	}
	
	public function getAjaxUrl() {
		return $this->getUrl('collectorcheckout/cajax/cajax');
	}
	
	public function getShippingMethods(){
		return $this->helper->getShippingMethods();
	}
}