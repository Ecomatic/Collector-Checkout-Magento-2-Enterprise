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

namespace Collector\Iframe\Model\Payment;

class Card extends \Magento\Payment\Model\Method\AbstractMethod
{

    protected $_code = "collector_card";
	protected $_isGateway                   = true;
    protected $_canCapture                  = true;
    protected $_canCapturePartial           = true;
    protected $_canRefund                   = true;
    protected $_canRefundInvoicePartial     = true;
	protected $_isOffline					= false;
	protected $_canAuthorize				= true;
	protected $_canCancel					= true;

	public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
		\Magento\Framework\Webapi\Soap\ClientFactory $clientFactory,
		\Collector\Gateways\Helper\Data $_helper,
		\Magento\Framework\View\Asset\Repository $_assetRepo,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null, 
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
		$this->helper = $_helper;
		$this->clientFactory = $clientFactory;
		$this->assetRepo = $_assetRepo;
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data
        );
    }
	
	public function canRefund(){
		return true;
	}
	
	public function canCapture(){
		return true;
	}
	
	public function canVoid(){
		return true;
	}
	
	public function isOffline(){
		return false;
	}
	
	public function canCancel(){
		return true;
	}
	
	public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount){
		//create soapclient, get details
		//get details
		//send addinvoice request
		//if error throw error
		//spara, corelation id och invoice id
		$info = $this->getInfoInstance();
		$paymentInfo = $info->getAdditionalInformation();
		$order = $payment->getOrder();
		$quote = $order->getQuote();
		$isIframe = false;
		if (isset($_SESSION['is_iframe'])){
			$isIframe = true;
			$payment->setIsTransactionClosed(false);
		}
		if (!$isIframe){
			$client = $this->clientFactory->create($this->helper->getInvoiceWSDL(), ['soap_version' => SOAP_1_1,
				'exceptions' => 1, 'trace' => true
			]);
			if($order->getBillingAddress()->getCompany()) {
				$storeID = $this->helper->getB2BStoreID();
			}
			else {
				$storeID = $this->helper->getB2CStoreID();
			}
			$header = array(
				'Username' => $this->helper->getUsername(),
				'Password' => $this->helper->getPassword(),
				'ClientIpAddress' => $this->helper->getRemoteIp($payment)
			);
			$req = array(
				'ActivationOption' => "0",
				'CorrelationId' => $order->getIncrementId(),
				'CountryCode' => $this->helper->getCountryCode(),
				'Currency' => 'SEK',
				'DeliveryAddress' => $this->helper->getDeliveryAddress($order),
				'InvoiceAddress' => $this->helper->getInvoiceAddress($order),
				'InvoiceDeliveryMethod' => $this->helper->getInvoiceDeliveryMethod(),
				'InvoiceRows' => $this->helper->getInvoiceRows($order),
				'InvoiceType' => $this->helper->getInvoiceType(),
				'OrderDate' => date("Y-m-d"),
				'OrderNo' => $order->getIncrementId(),
				'PurchaseType' => '0',
				'RegNo' => $paymentInfo['ssn'],
				'StoreId' => $storeID
			);
			$header['Username'] = $this->helper->getUsername();
			$header['Password'] = $this->helper->getPassword();
			$header['ClientIpAddress'] = $this->helper->getRemoteIp($payment);
			$headerList = array();
			foreach ($header as $k => $v) {
				$headerList[] = new \SoapHeader($this->helper->getHeaderUrl(), $k, $v);
			}
			$client->__setSoapHeaders($headerList);
			
			ob_start();
			print_r($req);
			file_put_contents(BP . "/var/log/req.log", "auth " . $payment->getOrder()->getIncrementId() . ": " . ob_get_clean() . "\n", FILE_APPEND);
			try {
				$resp = $client->AddInvoice($req);
				if ($resp->InvoiceStatus < 5){
					$order->setData('collector_invoice_id', $resp->InvoiceNo);
					$order->setData('collector_ssn', $paymentInfo['ssn']);
					$order->setData('fee_amount', $quote->getData('fee_amount'));
					$order->setData('base_fee_amount', $quote->getData('base_fee_amount'));
					$payment->setIsTransactionClosed(false);
				}
			}
			catch (\Exception $e){
				ob_start();
				print_r($e->getMessage());
				echo "\n";
				print_r($e->getTraceAsString());
				file_put_contents(BP . "/var/log/collector.log", "exception: " . ob_get_clean() . "\n", FILE_APPEND);
			}
		}
		$_SESSION['is_iframe'] = false;
	}
	
	public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount){
		$order = $payment->getOrder();
		$client = $this->clientFactory->create($this->helper->getInvoiceWSDL(), ['soap_version' => SOAP_1_1,
            'exceptions' => 1, 'trace' => true
        ]);
		$header['Username'] = $this->helper->getUsername();
        $header['Password'] = $this->helper->getPassword();
        $headerList = array();
        foreach ($header as $k => $v) {
            $headerList[] = new \SoapHeader($this->helper->getHeaderUrl(), $k, $v);
        }
        $client->__setSoapHeaders($headerList);
		
		if($order->getBillingAddress()->getCompany()) {
			$storeID = $this->helper->getB2BStoreID();
		}
		else {
			$storeID = $this->helper->getB2CStoreID();
		}
		
		if ($order->getGrandTotal() - $order->getTotalInvoiced() == $amount){
			$req = array(
				'CorrelationId' => $payment->getOrder()->getIncrementId(),
				'CountryCode' => $this->helper->getCountryCode(),
				'InvoiceNo' => $order->getData('collector_invoice_id'),
				'StoreId' => $storeID,
			);
			try {
				$client->ActivateInvoice($req);
				$payment->setTransactionId($order->getData('collector_invoice_id'));
				$payment->setParentTransactionId($payment->getTransactionId());
				$transaction = $payment->addTransaction(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_AUTH, null, true, "");
				$transaction->setIsClosed(true);
				$order->setData('fee_amount_invoiced', $order->getData('fee_amount'));
				$order->setData('base_fee_amount_invoiced', $order->getData('base_fee_amount'));
			}
			catch (\Exception $e){
				ob_start();
				var_dump($req);
				file_put_contents(BP . "/var/log/req.log", "capture " . $payment->getOrder()->getIncrementId() . ": " . ob_get_clean() . "\n", FILE_APPEND);
				ob_start();
				print_r($e->getMessage());
				echo "\n";
				print_r($e->getTraceAsString());
				file_put_contents(BP . "/var/log/collector.log", "exception: " . ob_get_clean() . "\n", FILE_APPEND);
			}
		}
		else {
			foreach ($payment->getOrder()->getInvoiceCollection() as $invoice){
				if ($invoice->getState() == null){
					$req = array(
						'CorrelationId' => $payment->getOrder()->getIncrementId(),
						'CountryCode' => $this->helper->getCountryCode(),
						'InvoiceNo' => $order->getData('collector_invoice_id'),
						'StoreId' => $storeID,
						'ArticleList' => array()
					);
					$bundlesWithFixedPrice = array();
					foreach ($invoice->getItemsCollection() as $item){
						if ($item->getOrderItem()->getProductType() == 'configurable') {
							continue;
						}
						elseif (in_array($item->getParentItemId(), $bundlesWithFixedPrice)) {
							continue;
						}
						elseif ($item->getOrderItem()->getProductType() == 'bundle') {
							$product = $item->getOrderItem()->getProduct();
							if ($product->getPriceType() == \Magento\Bundle\Model\Product\Price::PRICE_TYPE_FIXED) {
								$bundlesWithFixedPrice[] = $item->getItemId();
							}
							elseif ($product->getPriceType() == \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC) {
								continue;
							}
						}
						if ($item->getQty() < 1){
							continue;
						}
						array_push($req['ArticleList'], array(
							'ArticleId' => $item->getSku(),
							'Description' => $item->getName(),
							'Quantity' => $item->getQty()
						));
					}
					if ($order->getData('shipping_invoiced') == 0){
						array_push($req['ArticleList'], array(
							'ArticleId' => "shipping",
							'Description' => $order->getShippingMethod(),
							'Quantity' => 1
						));
					}
					if ($order->getData('fee_amount_invoiced') == 0){
						array_push($req['ArticleList'], array(
							'ArticleId' => "invoice_fee",
							'Description' => 'Invoice Fee',
							'Quantity' => 1
						));
					}
					ob_start();
					print_r($req);
					file_put_contents(BP . "/var/log/req.log", "part-capture ". $payment->getOrder()->getIncrementId() . ": " . ob_get_clean() . "\n", FILE_APPEND);
					try {
						$resp = $client->PartActivateInvoice($req);
						$payment->setTransactionId($order->getData('collector_invoice_id'));
						$payment->setParentTransactionId($payment->getTransactionId());
						$transaction = $payment->addTransaction(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_AUTH, null, true, "");
						$transaction->setIsClosed(true);
						$order->setData('fee_amount_invoiced', $order->getData('fee_amount'));
						$order->setData('base_fee_amount_invoiced', $order->getData('base_fee_amount'));
						$order->setData('collector_invoice_id', $resp->NewInvoiceNo);
					}
					catch (\Exception $e){
						ob_start();
						print_r($e->getMessage());
						echo "\n";
						print_r($e->getTraceAsString());
						file_put_contents(BP . "/var/log/collector.log", "exception: " . ob_get_clean() . "\n", FILE_APPEND);
					}
				}
			}
		}
    }
	
	public function void(\Magento\Payment\Model\InfoInterface $payment){
		$order = $payment->getOrder();
		if($order->getBillingAddress()->getCompany()) {
			$storeID = $this->helper->getB2BStoreID();
		}
		else {
			$storeID = $this->helper->getB2CStoreID();
		}
		$client = $this->clientFactory->create($this->helper->getInvoiceWSDL(), ['soap_version' => SOAP_1_1,
            'exceptions' => 1, 'trace' => true
        ]);
		$header['Username'] = $this->helper->getUsername();
        $header['Password'] = $this->helper->getPassword();
        $headerList = array();
        foreach ($header as $k => $v) {
            $headerList[] = new \SoapHeader($this->helper->getHeaderUrl(), $k, $v);
        }
        $client->__setSoapHeaders($headerList);
		$req = array(
			'CorrelationId' => $order->getIncrementId(),
			'CountryCode' => $this->helper->getCountryCode(),
			'InvoiceNo' => $order->getData('collector_invoice_id'),
			'StoreId' => $storeID,
		);
		try {
			$client->CancelInvoice($req);
		}
		catch (\Exception $e){
			ob_start();
			print_r($e->getMessage());
			echo "\n";
			print_r($e->getTraceAsString());
			file_put_contents(BP . "/var/log/collector.log", "exception: " . ob_get_clean() . "\n", FILE_APPEND);
			
		}
    }
	
	public function cancel(\Magento\Payment\Model\InfoInterface $payment){
		$order = $payment->getOrder();
		if($order->getBillingAddress()->getCompany()) {
			$storeID = $this->helper->getB2BStoreID();
		}
		else {
			$storeID = $this->helper->getB2CStoreID();
		}
		$client = $this->clientFactory->create($this->helper->getInvoiceWSDL(), ['soap_version' => SOAP_1_1,
            'exceptions' => 1, 'trace' => true
        ]);
		$header['Username'] = $this->helper->getUsername();
        $header['Password'] = $this->helper->getPassword();
        $headerList = array();
        foreach ($header as $k => $v) {
            $headerList[] = new \SoapHeader($this->helper->getHeaderUrl(), $k, $v);
        }
        $client->__setSoapHeaders($headerList);
		$req = array(
			'CorrelationId' => $order->getIncrementId(),
			'CountryCode' => $this->helper->getCountryCode(),
			'InvoiceNo' => $order->getData('collector_invoice_id'),
			'StoreId' => $storeID,
		);
		try {
			$client->CancelInvoice($req);
		}
		catch (\Exception $e){
			ob_start();
			print_r($e->getMessage());
			echo "\n";
			print_r($e->getTraceAsString());
			file_put_contents(BP . "/var/log/collector.log", "exception: " . ob_get_clean() . "\n", FILE_APPEND);
		}
	}
	
	public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount){
		$order = $payment->getOrder();
		if($order->getBillingAddress()->getCompany()) {
			$storeID = $this->helper->getB2BStoreID();
		}
		else {
			$storeID = $this->helper->getB2CStoreID();
		}
		$client = $this->clientFactory->create($this->helper->getInvoiceWSDL(), ['soap_version' => SOAP_1_1,
            'exceptions' => 1, 'trace' => true
        ]);
		$header['Username'] = $this->helper->getUsername();
        $header['Password'] = $this->helper->getPassword();
        $headerList = array();
        foreach ($header as $k => $v) {
            $headerList[] = new \SoapHeader($this->helper->getHeaderUrl(), $k, $v);
        }
        $client->__setSoapHeaders($headerList);
		if ($order->getGrandTotal() == $amount){
			$req = array(
				'CorrelationId' => $order->getIncrementId(),
				'CountryCode' => $this->helper->getCountryCode(),
				'InvoiceNo' => $order->getData('collector_invoice_id'),
				'StoreId' => $storeID,
				'CreditDate' => date("Y-m-d")
			);
			ob_start();
			print_r($req);
			file_put_contents(BP . "/var/log/req.log", "refund " . $payment->getOrder()->getIncrementId() . ": " . ob_get_clean() . "\n", FILE_APPEND);
			try {
				$client->CreditInvoice($req);
			}
			catch (\Exception $e){
				ob_start();
				print_r($e->getMessage());
				echo "\n";
				print_r($e->getTraceAsString());
				file_put_contents(BP . "/var/log/collector.log", "exception: " . ob_get_clean() . "\n", FILE_APPEND);
			}
		}
		else {
		//	while($payment->getCreditmemo() != null){}
		/*	$req = array(
				'CorrelationId' => $order->getIncrementId(),
				'CountryCode' => $this->helper->getCountryCode(),
				'InvoiceNo' => $order->getData('collector_invoice_id'),
				'StoreId' => $storeID,
				'CreditDate' => date("Y-m-d"),
				'ArticleList' => array()
			);
			foreach ($payment->getCreditmemo()->getItemsCollection() as $item){
				$article = array(
					'ArticleId' => $item->getSku(),
					'Description' => $item->getName(),
					'Quantity' => $item->getQty()
				);
				array_push($req['ArticleList'], $article);
			}
			ob_start();
			print_r($req);
			file_put_contents(BP . "/var/log/req.log", "part refund " . $payment->getOrder()->getIncrementId() . ": " . ob_get_clean() . "\n", FILE_APPEND);
			try {
				$client->PartCreditInvoice($req);
			}
			catch (\Exception $e){
			}*/
		}
    }
}
