<?php

namespace Collector\Iframe\Block;
 
class Success extends \Magento\Checkout\Block\Onepage {
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
	}
}