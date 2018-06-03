<?php

namespace Collector\Iframe\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper {
	
	protected $storeManager;
    protected $checkoutSession;
	protected $objectManager;
    protected $shippingRate;
	protected $coupon;
	protected $messageManager;
	protected $productConfigHelper;
	
	public function __construct(
		\Magento\Framework\ObjectManagerInterface $_objectManager,
		\Magento\Store\Model\StoreManagerInterface $_storeManager, 
		\Magento\Checkout\Model\Session $_checkoutSession, 
		\Magento\Quote\Model\Quote\Address\Rate $_shippingRate,
		\Magento\Framework\App\Helper\Context $context,
		\Magento\SalesRule\Model\Coupon $_coupon,
		\Magento\Catalog\Helper\Product\Configuration $_productConfigHelper,
		\Magento\Framework\Message\ManagerInterface $_messageManager
	){
		$this->shippingRate = $_shippingRate;
        $this->checkoutSession = $_checkoutSession;
        parent::__construct($context);
		$this->productConfigHelper = $_productConfigHelper;
		$this->messageManager = $_messageManager;
        $this->storeManager = $_storeManager;
		$this->coupon = $_coupon;
		$this->objectManager = $_objectManager;
	}
	
	public function getEnable(){
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue('collector_collectorcheckout/general/active', $storeScope);
    }

    public function getAcceptStatus(){
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue('collector_collectorcheckout/general/acceptstatus', $storeScope);
    }
    public function getHoldStatus(){
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue('collector_collectorcheckout/general/holdstatus', $storeScope);
    }
    public function getDeniedStatus(){
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue('collector_collectorcheckout/general/deniedstatus', $storeScope);
    }

	public function getTestMode(){
		$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue('collector_collectorcheckout/general/testmode', $storeScope);
	}
	
	public function getShowOptions(){
		return true;
	}
	
	public function getShippingTaxClass(){
		$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue('tax/classes/shipping_tax_class', $storeScope);
	}
	
	public function getB2BInvoiceFeeTaxClass(){
		$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue('collector_collectorcheckout/invoice/invoice_fee_b2b_tax_class', $storeScope);
	}
	
	public function getB2CInvoiceFeeTaxClass(){
		$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue('collector_collectorcheckout/invoice/invoice_fee_b2c_tax_class', $storeScope);
	}
	
	public function getUsername(){
		$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue('collector_collectorcheckout/general/username', $storeScope);
	}
	
	public function getCustomerType(){
		$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue('collector_collectorcheckout/general/customer_type', $storeScope);
	}
	
	public function getPassword(){
		$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue('collector_collectorcheckout/general/sharedkey', $storeScope);
	}
	
	public function getB2CStoreID(){
		$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue('collector_collectorcheckout/general/b2c_storeid', $storeScope);
	}
	
	public function getB2BStoreID(){
		$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue('collector_collectorcheckout/general/b2b_storeid', $storeScope);
	}
	
	public function getCountryCode(){
		$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue('general/country/default', $storeScope);
	}
	
	public function getSuccessPageUrl(){
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
		return $storeManager->getStore()->getBaseUrl()."collectorcheckout/success";
	}
	
	public function getTermsUrl(){
		$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue('collector_collectorcheckout/general/terms_url', $storeScope);
	}
	
	public function getNotificationUrl(){
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
		return $storeManager->getStore()->getBaseUrl()."collectorcheckout/notification";
	}
	
	public function getDiscount(){		
		$priceHelper = $this->objectManager->create('Magento\Framework\Pricing\Helper\Data');
		$cart = $this->objectManager->get('\Magento\Checkout\Model\Cart');
		return $priceHelper->currency(($cart->getQuote()->getSubtotal() - $cart->getQuote()->getSubtotalWithDiscount()), true, false);
	}
	
	public function hasDiscount(){
		$cart = $this->objectManager->get('\Magento\Checkout\Model\Cart');
		return ($cart->getQuote()->getSubtotal() != $cart->getQuote()->getSubtotalWithDiscount());
	}
	
	public function getTax(){
		$cart = $this->objectManager->get('\Magento\Checkout\Model\Cart');
		$priceHelper = $this->objectManager->create('Magento\Framework\Pricing\Helper\Data');
		$cart->getQuote()->collectTotals();
		$cartTotals = $cart->getQuote()->getTotals();
		return $priceHelper->currency($cartTotals['tax']->getData()['value'], true, false);
	}
	
	public function getGrandTotal(){
		$priceHelper = $this->objectManager->create('Magento\Framework\Pricing\Helper\Data');
		$cart = $this->objectManager->get('\Magento\Checkout\Model\Cart');
		
		if (!isset($_SESSION['curr_shipping_tax'])){
			$this->getShippingPrice();
		}
		$cart->getQuote()->collectTotals();
		return $priceHelper->currency($cart->getQuote()->getGrandTotal(), true, false);
	}
	
	public function getShippingMethods(){
		$currentStore = $this->storeManager->getStore();
		$currentStoreId = $currentStore->getId();
		$taxCalculation = $this->objectManager->get('\Magento\Tax\Model\Calculation');
		$request = $taxCalculation->getRateRequest(null, null, null, $currentStoreId);
		$cart = $this->objectManager->get('\Magento\Checkout\Model\Cart');
		$shippingAddress = $cart->getQuote()->getShippingAddress();
		$shippingAddress->setCollectShippingRates(true)->collectShippingRates();
		$shippingTaxClass = $this->getShippingTaxClass();
		$shippingTax = $taxCalculation->getRate($request->setProductClassId($shippingTaxClass));
		$priceHelper = $this->objectManager->create('Magento\Framework\Pricing\Helper\Data');
		$shippingMethods = array();
		$first = true;
		$methods = $shippingAddress->getGroupedAllShippingRates();
		if (isset($_SESSION['curr_shipping_code'])){
			foreach ($methods as $method){
				foreach ($method as $rate){
					if ($rate->getCode() == $_SESSION['curr_shipping_code']){
						if ($shippingTax == 0){
							$shipMethod = array(
								'first' => true,
								'code' => $rate->getCode(),
								'content' => $rate->getMethodTitle() . ": " . $priceHelper->currency($rate->getPrice(), true, false)
							);
							$this->setShippingMethod($rate->getCode());
							array_push($shippingMethods, $shipMethod);
						}
						else {
							$shipMethod = array(
								'first' => true,
								'code' => $rate->getCode(),
								'content' => $rate->getMethodTitle() . ": " . $priceHelper->currency(($rate->getPrice()*(1+($shippingTax/100))), true, false)
							);
							$this->setShippingMethod($rate->getCode());
							array_push($shippingMethods, $shipMethod);
						}
					}
					else {
						if ($shippingTax == 0){
							$shipMethod = array(
								'first' => false,
								'code' => $rate->getCode(),
								'content' => $rate->getMethodTitle() . ": " . $priceHelper->currency($rate->getPrice(), true, false)
							);
							array_push($shippingMethods, $shipMethod);
						}
						else {
							$shipMethod = array(
								'first' => false,
								'code' => $rate->getCode(),
								'content' => $rate->getMethodTitle() . ": " . $priceHelper->currency(($rate->getPrice()*(1+($shippingTax/100))), true, false)
							);
							array_push($shippingMethods, $shipMethod);
						}
					}
				}
			}
		}
		else {
			foreach ($methods as $method){
				foreach ($method as $rate){
					if ($first){
						$first = false;
						if ($shippingTax == 0){
							$shipMethod = array(
								'first' => true,
								'code' => $rate->getCode(),
								'content' => $rate->getMethodTitle() . ": " . $priceHelper->currency($rate->getPrice(), true, false)
							);
							$this->setShippingMethod($rate->getCode());
							array_push($shippingMethods, $shipMethod);
						}
						else {
							$shipMethod = array(
								'first' => true,
								'code' => $rate->getCode(),
								'content' => $rate->getMethodTitle() . ": " . $priceHelper->currency(($rate->getPrice()*(1+($shippingTax/100))), true, false)
							);
							$this->setShippingMethod($rate->getCode());
							array_push($shippingMethods, $shipMethod);
						}
					}
					else {
						if ($shippingTax == 0){
							$shipMethod = array(
								'first' => false,
								'code' => $rate->getCode(),
								'content' => $rate->getMethodTitle() . ": " . $priceHelper->currency($rate->getPrice(), true, false)
							);
							array_push($shippingMethods, $shipMethod);
						}
						else {
							$shipMethod = array(
								'first' => false,
								'code' => $rate->getCode(),
								'content' => $rate->getMethodTitle() . ": " . $priceHelper->currency(($rate->getPrice()*(1+($shippingTax/100))), true, false)
							);
							array_push($shippingMethods, $shipMethod);
						}
					}
				}
			}
		}
		return $shippingMethods;
	}
	
	public function setDiscountCode($code){
		$cart = $this->objectManager->get('\Magento\Checkout\Model\Cart');
		$ruleId = $this->coupon->loadByCode($code)->getRuleId();
		if (!empty($ruleId)){
			$this->checkoutSession->getQuote()->setCouponCode($code)->collectTotals()->save();
			$_SESSION['collector_applied_discount_code'] = $code;
			$cart->getQuote()->setData('collector_applied_discount_code', $code);
			$cart->getQuote()->save();
			$this->messageManager->addSuccess(__('You used coupon code "%1".',$code));
		}
		else {
			$this->messageManager->addError(__('The coupon code "%1" is not valid.',$code));
		}
	}
	
	public function unsetDiscountCode(){
		$cart = $this->objectManager->get('\Magento\Checkout\Model\Cart');
		unset($_SESSION['collector_applied_discount_code']);
		$cart->getQuote()->setData('collector_applied_discount_code', NULL);
		$cart->getQuote()->save();
		$this->messageManager->addSuccess(__('You canceled the coupon code.'));
		$this->checkoutSession->getQuote()->setCouponCode()->collectTotals()->save();
	}
	
	public function setShippingMethod($methodInput){
		$priceHelper = $this->objectManager->create('Magento\Framework\Pricing\Helper\Data');
		$currentStore = $this->storeManager->getStore();
		$currentStoreId = $currentStore->getId();
		$taxCalculation = $this->objectManager->get('\Magento\Tax\Model\Calculation');
		$request = $taxCalculation->getRateRequest(null, null, null, $currentStoreId);
		$cart = $this->objectManager->get('\Magento\Checkout\Model\Cart');
		$shippingAddress = $cart->getQuote()->getShippingAddress();
		$shippingAddress->setCollectShippingRates(true)->collectShippingRates();
		$methods = $shippingAddress->getGroupedAllShippingRates();
		$shippingTaxClass = $this->getShippingTaxClass();
		$shippingTax = $taxCalculation->getRate($request->setProductClassId($shippingTaxClass));
		$first = true;
		foreach ($methods as $method){
			foreach ($method as $rate){
				if ($rate->getCode() == $methodInput){
					$_SESSION['curr_shipping_description'] = $rate->getMethodTitle();
					$_SESSION['curr_shipping_tax_rate'] = $shippingTax;
				//	if ($shippingTax == 0){
						$_SESSION['curr_shipping_price'] = $rate->getPrice();
						$_SESSION['curr_shipping_tax'] = 0; 
				/*	}
					else {
						$_SESSION['curr_shipping_price'] = ($rate->getPrice()*(1+($shippingTax/100)));
						$_SESSION['curr_shipping_tax'] = ($rate->getPrice()*(1+($shippingTax/100))) - $rate->getPrice();
					}*/
					$cart->getQuote()->getShippingAddress()->setCollectShippingRates(true)->collectShippingRates()->setShippingMethod($rate->getCode());
					$this->shippingRate->setCode($rate->getCode())->getPrice();
					try {
						$cart->getQuote()->getShippingAddress()->addShippingRate($this->shippingRate);
					}
					catch (\Exception $e){}
					$cart->getQuote()->getShippingAddress()->save();
					$cart->getQuote()->collectTotals();
					$cart->getQuote()->save();
					$cart->getQuote()->collectTotals();
					$t = $cart->getQuote()->getTotals();
					$first = false;
					$_SESSION['curr_shipping_code'] = $rate->getCode();
					$cart->getQuote()->setData('curr_shipping_code', $rate->getCode());
					$cart->getQuote()->save();
					break;
				}
			}
			if (!$first){
				break;
			}
		}
		return $priceHelper->currency($_SESSION['curr_shipping_price'], true, false);
	}
	
	public function getShippingPrice($inclFormatting = true){
		$priceHelper = $this->objectManager->create('Magento\Framework\Pricing\Helper\Data');
		$currentStore = $this->storeManager->getStore();
		$currentStoreId = $currentStore->getId();
		$taxCalculation = $this->objectManager->get('\Magento\Tax\Model\Calculation');
		$request = $taxCalculation->getRateRequest(null, null, null, $currentStoreId);
		$cart = $this->objectManager->get('\Magento\Checkout\Model\Cart');
		$shippingAddress = $cart->getQuote()->getShippingAddress();
		$shippingAddress->setCollectShippingRates(true)->collectShippingRates();
		$methods = $shippingAddress->getGroupedAllShippingRates();
		$shippingTaxClass = $this->getShippingTaxClass();
		$shippingTax = $taxCalculation->getRate($request->setProductClassId($shippingTaxClass));
		$first = true;
		if (isset($_SESSION['curr_shipping_code'])){
			foreach ($methods as $method){
				foreach ($method as $rate){
					if ($rate->getCode() == $_SESSION['curr_shipping_code']){
						$_SESSION['curr_shipping_description'] = $rate->getMethodTitle();
						$_SESSION['curr_shipping_tax_rate'] = $shippingTax;
					//	if ($shippingTax == 0){
							$_SESSION['curr_shipping_price'] = $rate->getPrice();
							$_SESSION['curr_shipping_tax'] = 0; 
					//	}
					/*	else {
							$_SESSION['curr_shipping_price'] = ($rate->getPrice()*(1+($shippingTax/100)));
							$_SESSION['curr_shipping_tax'] = ($rate->getPrice()*(1+($shippingTax/100))) - $rate->getPrice();
						}*/
						$cart->getQuote()->getShippingAddress()->setCollectShippingRates(true)->collectShippingRates()->setShippingMethod($rate->getCode());
						$this->shippingRate->setCode($rate->getCode())->getPrice();
						try {
							$cart->getQuote()->getShippingAddress()->addShippingRate($this->shippingRate);
						}
						catch (\Exception $e){}
						$cart->getQuote()->getShippingAddress()->save();
						$cart->getQuote()->collectTotals();
						$cart->getQuote()->save();
						$t = $cart->getQuote()->getTotals();
						$this->setShippingMethod($rate->getCode());
						$_SESSION['curr_shipping_code'] = $rate->getCode();
						$cart->getQuote()->setData('curr_shipping_code', $rate->getCode());
						$cart->getQuote()->save();
						break;
					}
				}
			}
		}
		else {
			foreach ($methods as $method){
				foreach ($method as $rate){
					if ($first){
						$_SESSION['curr_shipping_description'] = $rate->getMethodTitle();
						$_SESSION['curr_shipping_tax_rate'] = $shippingTax;
					//	if ($shippingTax == 0){
							$_SESSION['curr_shipping_price'] = $rate->getPrice();
							$_SESSION['curr_shipping_tax'] = 0; 
					//	}
					/*	else {
							$_SESSION['curr_shipping_price'] = ($rate->getPrice()*(1+($shippingTax/100)));
							$_SESSION['curr_shipping_tax'] = ($rate->getPrice()*(1+($shippingTax/100))) - $rate->getPrice();
						}*/
						$cart->getQuote()->getShippingAddress()->setCollectShippingRates(true)->collectShippingRates()->setShippingMethod($rate->getCode());
						$this->shippingRate->setCode($rate->getCode())->getPrice();
						try {
							$cart->getQuote()->getShippingAddress()->addShippingRate($this->shippingRate);
						}
						catch (\Exception $e){}
						$cart->getQuote()->getShippingAddress()->save();
						$cart->getQuote()->collectTotals();
						$cart->getQuote()->save();
						$t = $cart->getQuote()->getTotals();
						$first = false;
						$this->setShippingMethod($rate->getCode());
						$_SESSION['curr_shipping_code'] = $rate->getCode();
						$cart->getQuote()->setData('curr_shipping_code', $rate->getCode());
						$cart->getQuote()->save();
						break;
					}
				}
				if (!$first){
					break;
				}
			}
		}
		if (!isset($_SESSION['curr_shipping_price'])){
			$_SESSION['curr_shipping_price'] = 0;
		}
		if($inclFormatting){
			return $priceHelper->currency($_SESSION['curr_shipping_price'], true, false);
		}
		else {
			return $_SESSION['curr_shipping_price'];
		}
	}
	
	public function getBlockProducts(){
		$cartItems = $this->objectManager->get('\Magento\Checkout\Model\Cart')->getQuote()->getAllVisibleItems();
		$currentStore = $this->storeManager->getStore();
		$currentStoreId = $currentStore->getId();
		$taxCalculation = $this->objectManager->get('\Magento\Tax\Model\Calculation');
		$request = $taxCalculation->getRateRequest(null, null, null, $currentStoreId);
		$productLoader = $this->objectManager->get('\Magento\Catalog\Api\ProductRepositoryInterface');
        $imageHelper  = $this->objectManager->get('\Magento\Catalog\Helper\Image');
		$priceHelper = $this->objectManager->create('Magento\Framework\Pricing\Helper\Data');
		$items = array();
		
		$imgs = array();
        foreach ($cartItems as $item){
			$productLoader2 = \Magento\Framework\App\ObjectManager::getInstance()->get('\Magento\Catalog\Api\ProductRepositoryInterface');
			$product = $item->getProduct();
			array_push($imgs, $imageHelper->init($product, 'product_page_image_small')->setImageFile($product->getFile())->resize(80, 80)->getUrl());
		}
		$i = 0;
		foreach ($cartItems as $cartItem){
			$product = $cartItem->getProduct();
            $taxClassId = $product->getTaxClassId();
            $percent = $taxCalculation->getRate($request->setProductClassId($taxClassId));
			$options = "";
			if ($this->getShowOptions()){
				$options = "<dl>";
				$op = $cartItem->getProduct()->getTypeInstance(true)->getOrderOptions($cartItem->getProduct());
				if ($cartItem->getProductType() == 'configurable') {
					foreach ($op['attributes_info'] as $option){
						$options .= "<dd>";
						$options .= $option['label'] . ": " . $option['value'];
						$options .= "</dd>";
					}
				}
				else if ($cartItem->getProductType() == 'bundle'){
					foreach ($op['bundle_options'] as $option){
						$options .= "<dd>";
						$options .= $option['value'][0]['title'];
						$options .= "</dd>";
					}
				}
				else {
					
				}
				$options .= '</dl>';
			}
			
			array_push($items, array(
				'name' => $cartItem->getName(),
				'options' => $options,
				'id' => $cartItem->getId(),
				'unitPrice' => $priceHelper->currency(($cartItem->getPrice()*(1+($percent/100))), true, false),
				'qty' => $cartItem->getQty(),
				'sum' => $priceHelper->currency(($cartItem->getPrice()*$cartItem->getQty()*(1+($percent/100))), true, false),
				'img' => $imgs[$i]
			));
			$i++;
		}
		return $items;
	}
	
	public function getProducts(){
		$cart = $this->objectManager->get('\Magento\Checkout\Model\Cart');
		$cartItems = $cart->getQuote()->getAllItems();
		$currentStore = $this->storeManager->getStore();
		$currentStoreId = $currentStore->getId();
		$taxCalculation = $this->objectManager->get('\Magento\Tax\Model\Calculation');
		$request = $taxCalculation->getRateRequest(null, null, null, $currentStoreId);
		$productLoader = $this->objectManager->get('\Magento\Catalog\Api\ProductRepositoryInterface');
		$cartTotals = $cart->getQuote()->getTotals();
		$items = array('items' => array());
		$bundlesWithFixedPrice = array();
		
		foreach ($cartItems as $cartItem){
			if ($cartItem->getProductType() == 'configurable') {
				continue;
			}
			elseif (in_array($cartItem->getParentItemId(), $bundlesWithFixedPrice)) {
				continue;
			}
			elseif ($cartItem->getProductType() == 'bundle') {
				$product = $cartItem->getProduct();
				if ($product->getPriceType() == \Magento\Bundle\Model\Product\Price::PRICE_TYPE_FIXED) {
					$bundlesWithFixedPrice[] = $cartItem->getItemId();
				}
				elseif ($product->getPriceType() == \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC) {
					continue;
				}
			}
			$product = $productLoader->get($cartItem->getSku());
            $taxClassId = $product->getTaxClassId();
            $percent = $taxCalculation->getRate($request->setProductClassId($taxClassId));
			$qty = 0;
			if ($cartItem->getParentItem()){
				$qty = $cartItem->getParentItem()->getQty();
			}
			else {
				$qty = $cartItem->getQty();
			}
			$price = $cartItem->getPriceInclTax();
			if ($cartItem->getPriceInclTax() == 0){
				$price = $cartItem->getParentItem()->getPriceInclTax();
			}
			array_push($items['items'], array(
				'id' => $cartItem->getSku(),
				'description' => $cartItem->getName(),
				'unitPrice' => round($price, 2),
				'quantity' => $qty,
				'vat' => $percent
			));
		}
		$fee = 0;
		if (array_key_exists('fee', $cartTotals)){
			$fee = $cartTotals['fee']->getData()['value'];
		}
		if (array_key_exists('value_incl_tax', $cartTotals['subtotal']->getData())){
			if ($cart->getQuote()->getGrandTotal() < ($cartTotals['subtotal']->getData()['value_incl_tax'] + $fee + $this->getShippingInclTax()['unitPrice'])){
				if ($cart->getQuote()->getCouponCode() != null){
					$coupon = $cart->getQuote()->getCouponCode();
				}
				else  {
					$coupon = "no_code";
				}
				$code = array(
					'id' => 'discount',
					'description' => $coupon,
					'quantity' => 1,
					'unitPrice' => sprintf("%01.2f", $cart->getQuote()->getGrandTotal() - ($cartTotals['subtotal']->getData()['value_incl_tax'] + $fee + $this->getShippingInclTax()['unitPrice'])),
					'vat' => '25',
				);
				array_push($items['items'], $code);
			}
		}
		else {
			if ($cart->getQuote()->getGrandTotal() < ($cartTotals['subtotal']->getData()['value'] + $fee + $this->getShippingInclTax()['unitPrice'])){
				if ($cart->getQuote()->getCouponCode() != null){
					$coupon = $cart->getQuote()->getCouponCode();
				}
				else  {
					$coupon = "no_code";
				}
				$code = array(
					'id' => 'discount',
					'description' => $coupon,
					'quantity' => 1,
					'unitPrice' => sprintf("%01.2f", $cart->getQuote()->getGrandTotal() - ($cartTotals['subtotal']->getData()['value'] + $fee + $this->getShippingInclTax()['unitPrice'])),
					'vat' => '25',
				);
				array_push($items['items'], $code);
			}
		}
		return $items;
	}
	
	public function getFees(){
		$cart = $this->objectManager->get('\Magento\Checkout\Model\Cart');
		$cart->getQuote()->collectTotals();
		$cartTotals = $cart->getQuote()->getTotals();
		$currentStore = $this->storeManager->getStore();
		$currentStoreId = $currentStore->getId();
		$taxCalculation = $this->objectManager->get('\Magento\Tax\Model\Calculation');
		$request = $taxCalculation->getRateRequest(null, null, null, $currentStoreId);
		$shippingTaxClass = $this->getShippingTaxClass();
		$shippingTax = $taxCalculation->getRate($request->setProductClassId($shippingTaxClass));
		if (empty($cartTotals['shipping']->getData()['title']->getArguments())){
			if (isset($_SESSION['curr_shipping_code'])){
				if ($_SESSION['curr_shipping_price'] == 0){
					$ret = array(
						'shipping' => array(
							'id' => "shipping",
							'description' => $_SESSION['curr_shipping_code'],
							'unitPrice' => 0,
							'vat' => 0
						)
					);
				}
				else {
					$ret = array(
						'shipping' => array(
							'id' => "shipping",
							'description' => $_SESSION['curr_shipping_code'],
							'unitPrice' => $_SESSION['curr_shipping_price'],
							'vat' => ($_SESSION['curr_shipping_price']/($_SESSION['curr_shipping_price']-$_SESSION['curr_shipping_tax'])-1)*100
						)
					);
				}
			}
			else {
				$ret = array(
					'shipping' => array(
						'id' => 'shipping',
						'description' => 'freeshipping_freeshipping',
						'unitPrice' => 0,
						'vat' => '0'
					)
				);
			}
		}
		else {
			$ret = array(
				'shipping' => array(
					'id' => 'shipping',
					'description' => $_SESSION['curr_shipping_code'],
					'unitPrice' => $cartTotals['shipping']->getData()['value'],
					'vat' => '25'
				)
			);
		}
		$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
		$fee = \Magento\Framework\App\ObjectManager::getInstance()->get('\Magento\Framework\App\Config\ScopeConfigInterface')->getValue('collector_collectorcheckout/invoice/invoice_fee_b2b', $storeScope);
		$taxCalculation = $this->objectManager->get('\Magento\Tax\Model\Calculation');
		$request = $taxCalculation->getRateRequest(null, null, null, $currentStoreId);
		$feeTaxClass = $this->getB2CInvoiceFeeTaxClass();
		$feeTax = $taxCalculation->getRate($request->setProductClassId($feeTaxClass));
		if ($fee > 0){
			$iFee = array(
				'id' => 'invoice_fee',
				'description' => 'Invoice Fee',
				'unitPrice' => $fee,
				'vat' => $feeTax
			);
			$ret['directinvoicenotification'] = $iFee;
		}
		return $ret;
	}
	
	public function getShippingInclTax(){
		$cart = $this->objectManager->get('\Magento\Checkout\Model\Cart');
		$cart->getQuote()->collectTotals();
		$cartTotals = $cart->getQuote()->getTotals();
		$currentStore = $this->storeManager->getStore();
		$currentStoreId = $currentStore->getId();
		$taxCalculation = $this->objectManager->get('\Magento\Tax\Model\Calculation');
		$request = $taxCalculation->getRateRequest(null, null, null, $currentStoreId);
		$shippingTaxClass = $this->getShippingTaxClass();
		$shippingTax = $taxCalculation->getRate($request->setProductClassId($shippingTaxClass));
		$ret = array(
			'description' => $cartTotals['shipping']->getData()['title']->getArguments(),
			'unitPrice' => $cartTotals['shipping']->getData()['value']*(1+$shippingTax/100),
		);
		return $ret;
	}
	
	public function getWSDL(){
		if ($this->getTestMode()){
			return "https://checkout-api-uat.collector.se/";
		}
		else {
			return "https://checkout-api.collector.se/";
		}
	}
		
	public function updateFees(){
		$quote = $this->objectManager->get('\Magento\Checkout\Model\Cart')->getQuote();
		if (isset($_SESSION['collector_private_id'])){
			$pid = $_SESSION['collector_private_id'];
		}
		else {
			$pid = $quote->getData('collector_private_id');
		}
		$pusername = $this->getUsername();
		$psharedSecret= $this->getPassword();
		if (isset($_SESSION['col_curr_fee'])){
			if ($_SESSION['col_curr_fee'] == $this->getFees()){
				return;
			}
			else {
				$array = $this->getFees();
				$_SESSION['col_curr_fee'] = $array;
			}
		}
		else {
			$array = $this->getFees();
			$_SESSION['col_curr_fee'] = $array;
		}
		$storeId = 0;
		if (isset($_SESSION['btype'])){
			if ($_SESSION['btype'] == 'b2b'){
				$storeId = $this->getB2BStoreID();
			}
			else {
				$storeId = $this->getB2CStoreID();
			}
		}
		else {
			switch ($this->getCustomerType()){
				case 1:
					$_SESSION['btype'] = 'b2c';
					$storeId = $this->getB2CStoreID();
				break;
				case 2:
					$_SESSION['btype'] = 'b2b';
					$storeId = $this->getB2BStoreID();
				break;
				case 3:
					$_SESSION['btype'] = 'b2c';
					$storeId = $this->getB2CStoreID();
				break;
			}
		}
		$path = '/merchants/'.$storeId.'/checkouts/'.$pid.'/fees';
		$json = json_encode($array);
		$hash = $pusername.":".hash("sha256",$json.$path.$psharedSecret);
		$hashstr = 'SharedKey '.base64_encode($hash); 
		$ch = curl_init($this->getWSDL()."merchants/".$storeId."/checkouts/".$pid."/fees");
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'charset=utf-8','Authorization:'.$hashstr));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		$output = curl_exec($ch);
		
		$data = json_decode($output,true);
		curl_close($ch);
	}
	
	public function updateCart(){
		$quote = $this->objectManager->get('\Magento\Checkout\Model\Cart')->getQuote();
		if (isset($_SESSION['collector_private_id'])){
			$pid = $_SESSION['collector_private_id'];
		}
		else {
			$pid = $quote->getData('collector_private_id');
		}
		$pusername = $this->getUsername();
		$psharedSecret= $this->getPassword();
		$array = array();
		$array['countryCode'] = $this->getCountryCode();
		$array['items'] = $this->getProducts()['items'];
		$storeId = 0;
		if (isset($_SESSION['btype'])){
			if ($_SESSION['btype'] == 'b2b'){
				$storeId = $this->getB2BStoreID();
			}
			else {
				$storeId = $this->getB2CStoreID();
			}
		}
		else {
			switch ($this->getCustomerType()){
				case 1:
					$_SESSION['btype'] = 'b2c';
					$storeId = $this->getB2CStoreID();
				break;
				case 2:
					$_SESSION['btype'] = 'b2b';
					$storeId = $this->getB2BStoreID();
				break;
				case 3:
					$_SESSION['btype'] = 'b2c';
					$storeId = $this->getB2CStoreID();
				break;
			}
		}
		$path = '/merchants/'.$storeId.'/checkouts/'.$pid.'/cart';
		$json = json_encode($array);
		$hash = $pusername.":".hash("sha256",$json.$path.$psharedSecret);
		$hashstr = 'SharedKey '.base64_encode($hash); 
		$ch = curl_init($this->getWSDL()."merchants/".$storeId."/checkouts/".$pid."/cart");
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'charset=utf-8','Authorization:'.$hashstr));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		$output = curl_exec($ch);
		$data = json_decode($output,true);
		curl_close($ch);
	}
	
	public function getOrderResponse(){
		$quote = $this->objectManager->get('\Magento\Checkout\Model\Cart')->getQuote();
		if (isset($_SESSION['collector_private_id'])){
			$pid = $_SESSION['collector_private_id'];
		}
		else {
			$pid = $quote->getData('collector_private_id');
		}
		$pusername = $this->getUsername();
		$psharedSecret = $this->getPassword();
		$array = array();
		$array['countryCode'] = $this->getCountryCode();
		$array['items'] = $this->getProducts()['items'];
		$storeId = 0;
		if (isset($_SESSION['btype'])){
			if ($_SESSION['btype'] == 'b2b'){
				$storeId = $this->getB2BStoreID();
			}
			else {
				$storeId = $this->getB2CStoreID();
			}
		}
		else {
			switch ($this->getCustomerType()){
				case 1:
					$_SESSION['btype'] = 'b2c';
					$storeId = $this->getB2CStoreID();
				break;
				case 2:
					$_SESSION['btype'] = 'b2b';
					$storeId = $this->getB2BStoreID();
				break;
				case 3:
					$_SESSION['btype'] = 'b2c';
					$storeId = $this->getB2CStoreID();
				break;
			}
		}
		$path = '/merchants/'.$storeId.'/checkouts/'.$pid;
		$hash = $pusername.":".hash("sha256",$path.$psharedSecret);
		$hashstr = 'SharedKey '.base64_encode($hash);
		
		$ch = curl_init($this->getWSDL()."merchants/".$storeId."/checkouts/".$pid);
		curl_setopt($ch, CURLOPT_HTTPGET, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization:'.$hashstr));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);

		$output = curl_exec($ch);
		$data = json_decode($output,true);
		
		if($data["data"]){
			$result['code'] = 1;
			$result['id'] = $data["id"];
			$result['data'] = $data["data"];
			
		} else {
			$result['code'] = 0;
			$result['error'] = $data["error"];
		}			
		return $result;
	}
}