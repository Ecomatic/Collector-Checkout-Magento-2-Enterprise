<?php

namespace Collector\Iframe\Block;
 
class Checkout extends \Magento\Checkout\Block\Onepage {
	protected $objectManager;
	protected $helper;
	protected $cart;

    public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
		array $data = [],
		\Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Checkout\Helper\Data $checkoutHelper,
		\Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Checkout\Model\CompositeConfigProvider $configProvider,
		\Collector\Iframe\Helper\Data $_helper,
		\Magento\Checkout\Model\Cart $_cart,
        array $layoutProcessors = []
	){
        parent::__construct($context, $formKey, $configProvider, $layoutProcessors, $data);
		$this->helper = $_helper;
		$this->cart = $_cart;
	}
	
	protected function _toHtml(){
		return parent::_toHtml();
	}
	
	public function getCheckoutUrl(){
		if ($this->helper->getTestMode()){
			$_SESSION['collector_url'] = "https://checkout-uat.collector.se/collector-checkout-loader.js";
			return "https://checkout-uat.collector.se/collector-checkout-loader.js";
		}
		else {
			$_SESSION['collector_url'] = "https://checkout.collector.se/collector-checkout-loader.js";
			return "https://checkout.collector.se/collector-checkout-loader.js";
		}
	}
	
	public function getLanguage(){
		$lang = $this->helper->getCountryCode();
		if ($lang == "NO"){
			$_SESSION['collector_language'] = "nb-NO";
			return "nb-NO";
		}
		else if ($lang == "SE"){
			$_SESSION['collector_language'] = "sv";
			return "sv";
		}
		else {
			return null;
		}
	}
	
	public function getDataVariant(){
		if (isset($_SESSION['btype'])){
			if ($_SESSION['btype'] == 'b2b'){
				$dataVariant = 'data-variant="b2b" async';
			}
			else {
				$dataVariant = ' async';
			}
		}
		else {
			switch ($this->helper->getCustomerType()){
				case 1:
					$_SESSION['btype'] = 'b2c';
					$dataVariant = ' async';
				break;
				case 2:
					$_SESSION['btype'] = 'b2b';
					$dataVariant = 'data-variant="b2b" async';
				break;
				case 3:
					$_SESSION['btype'] = 'b2c';
					$dataVariant = ' async';
				break;
			}
		}
		$_SESSION['collector_data_variant'] = $dataVariant;
		return $dataVariant;
	}
	
	public function getPublicToken(){
		if (isset($_SESSION['collector_public_token'])){
			$this->helper->updateCart();
			$this->helper->updateFees();
			return $_SESSION['collector_public_token'];
		}
		if (empty($this->cart->getQuote()->getReservedOrderId())){
            $this->cart->getQuote()->reserveOrderId()->save();
        }
		$username = $this->helper->getUsername();
		$path = '/checkout';
		$sharedSecret = $this->helper->getPassword();
		$req = array();
		if (isset($_SESSION['btype'])){
			if ($_SESSION['btype'] == 'b2b'){
				$req['storeId'] = $this->helper->getB2BStoreID();
			}
			else {
				$req['storeId'] = $this->helper->getB2CStoreID();
			}
		}
		else {
			switch ($this->helper->getCustomerType()){
				case 1:
					$_SESSION['btype'] = 'b2c';
					$req['storeId'] = $this->helper->getB2CStoreID();
				break;
				case 2:
					$_SESSION['btype'] = 'b2b';
					$req['storeId'] = $this->helper->getB2BStoreID();
				break;
				case 3:
					$_SESSION['btype'] = 'b2c';
					$req['storeId'] = $this->helper->getB2CStoreID();
				break;
			}
		}
		$req['countryCode'] = $this->helper->getCountryCode();
		$req['reference'] = $this->cart->getQuote()->getReservedOrderId();
		$req['redirectPageUri'] = $this->helper->getSuccessPageUrl();
		$req['merchantTermsUri'] = $this->helper->getTermsUrl();
		$req['notificationUri'] = $this->helper->getNotificationUrl();
		$req["cart"] = $this->helper->getProducts();
		$req["fees"] = $this->helper->getFees();
		$json = json_encode($req);
		$hash = $username.":".hash("sha256",$json.$path.$sharedSecret);
		$hashstr = 'SharedKey '.base64_encode($hash);
		$ch = curl_init($this->helper->getWSDL()."checkout");
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'charset=utf-8','Authorization:'.$hashstr));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		$output = curl_exec($ch);
		ob_start();
		var_dump($output);
		echo "\n";
		var_dump(curl_getinfo($ch));
		file_put_contents("test", ob_get_clean() . "\n", FILE_APPEND);
		$result = json_decode($output,true);
		$_SESSION['collector_public_token'] = $result["data"]["publicToken"];
		$_SESSION['collector_private_id'] = $result['data']['privateId'];
		$this->cart->getQuote()->setData('collector_private_id', $result['data']['privateId']);
		$this->cart->getQuote()->setData('collector_btype', $_SESSION['btype']);
		$this->cart->getQuote()->save();
		curl_close($ch);
		return $publicToken = $result["data"]["publicToken"];
	}
}