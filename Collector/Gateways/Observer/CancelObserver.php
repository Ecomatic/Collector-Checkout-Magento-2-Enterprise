<?php
namespace Collector\Gateways\Observer;

use Magento\Framework\DataObject;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Payment\Model\InfoInterface;

class CancelObserver extends AbstractDataAssignObserver{
	
	protected $clientFactory;
	protected $helper;
	
	public function __construct(\Collector\Gateways\Helper\Data $_helper, \Magento\Framework\Webapi\Soap\ClientFactory $clientFactory){
		$this->helper = $_helper;
		$this->clientFactory = $clientFactory;
	}
	
	public function execute(Observer $observer){
		$order = $observer->getOrder();
		$payment = $order->getPayment();
		$method = $payment->getMethodInstance();
		if (strpos($method->getCode(), "collector") !== false){			
			$client = $this->clientFactory->create($this->helper->getInvoiceWSDL(), ['soap_version' => SOAP_1_1,
				'exceptions' => 1, 'trace' => true
			]);
			$header['Username'] = $this->helper->getUsername();
			$header['Password'] = $this->helper->getPassword();
			$headerList = array();
			foreach ($header as $k => $v) {
				$headerList[] = new \SoapHeader($this->helper->getHeaderUrl(), $k, $v);
			}
			$storeId = 0;
			if ($this->helper->getEnable()){
				if ($order->getData('collector_ssn') !== null){
					$storeId = $this->helper->getB2BStoreID();
				}
				else {
					$storeId = $this->helper->getB2CStoreID();
				}
			}
			else {
				$storeId = $this->helper->getB2CStoreID();
			}
			$client->__setSoapHeaders($headerList);
			$req = array(
				'CorrelationId' => $order->getIncrementId(),
				'CountryCode' => $this->helper->getCountryCode(),
				'InvoiceNo' => $order->getData('collector_invoice_id'),
				'StoreId' => $storeId,
			);
			ob_start();
			print_r($req);
			file_put_contents(BP . "/var/log/req.log", ob_get_clean() . "\n", FILE_APPEND);
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
	}
}