<?php
 
namespace Collector\Gateways\Model\Payment;
 
/**
 * Pay In Store payment method model
 */


class Partpay extends \Magento\Payment\Model\Method\AbstractMethod {
    protected $_code = 'collector_partpay';
	protected $_isGateway                   = true;
    protected $_canCapture                  = true;
    protected $_canCapturePartial           = true;
    protected $_canRefund                   = true;
    protected $_canRefundInvoicePartial     = true;
	protected $_isOffline					= false;
	protected $clientFactory;
	protected $helper;
	
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
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
		$this->helper = $_helper;
		$this->clientFactory = $clientFactory;
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
	
	public function canAuthorize(){
		return $this->_canAuthorize;
	}
	
	public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount){
		//create soapclient, get details
		//get details
		//send addinvoice request
		//if error throw error
		//spara, corelation id och invoice id
	//	$info = $this->getInfoInstance();
	//	$paymentInfo = $info->getAdditionalInformation();
	//	var_dump($paymentInfo);
		
		$client = $this->clientFactory->create($this->helper->getInvoiceWSDL(), ['soap_version' => SOAP_1_1,
            'exceptions' => 1, 'trace' => true
        ]);
		
		$header = array(
			'Username' => $this->helper->getUsername(),
			'Password' => $this->helper->getPassword(),
			'ClientIpAddress' => $this->helper->getRemoteIp($payment)
		);
	/*	$req = array(
			'ActivationOption' => "0",
			'AdditionalInformation' => array(
				'Key' => '',
				'Value' => ''
			),
		);
		/*
         <inv:ActivationOption>?</inv:ActivationOption>
         <inv:AdditionalInformation>
            <inv:Information>
               <inv:Key>?</inv:Key>
               <inv:Value>?</inv:Value>
            </inv:Information>
         </inv:AdditionalInformation>
         <inv:CorrelationId>?</inv:CorrelationId>
         <inv:CostCenter>?</inv:CostCenter>
         <inv:CountryCode>?</inv:CountryCode>
         <inv:CreditTime>?</inv:CreditTime>
         <inv:Currency>?</inv:Currency>
         <inv:CustomerNo>?</inv:CustomerNo>
         <inv:DeliveryAddress>
            <inv:Address1>?</inv:Address1>
            <inv:Address2>?</inv:Address2>
            <inv:COAddress>?</inv:COAddress>
            <inv:City>?</inv:City>
            <inv:CountryCode>?</inv:CountryCode>
            <inv:PostalCode>?</inv:PostalCode>
            <inv:CellPhoneNumber>?</inv:CellPhoneNumber>
            <inv:CompanyName>?</inv:CompanyName>
            <inv:Email>?</inv:Email>
            <inv:Firstname>?</inv:Firstname>
            <inv:Lastname>?</inv:Lastname>
            <inv:PhoneNumber>?</inv:PhoneNumber>
         </inv:DeliveryAddress>
         <inv:Gender>?</inv:Gender>
         <inv:InvoiceAddress>
            <inv:Address1>?</inv:Address1>
            <inv:Address2>?</inv:Address2>
            <inv:COAddress>?</inv:COAddress>
            <inv:City>?</inv:City>
            <inv:CountryCode>?</inv:CountryCode>
            <inv:PostalCode>?</inv:PostalCode>
            <inv:CellPhoneNumber>?</inv:CellPhoneNumber>
            <inv:CompanyName>?</inv:CompanyName>
            <inv:Email>?</inv:Email>
            <inv:Firstname>?</inv:Firstname>
            <inv:Lastname>?</inv:Lastname>
            <inv:PhoneNumber>?</inv:PhoneNumber>
         </inv:InvoiceAddress>
         <inv:InvoiceDeliveryMethod>?</inv:InvoiceDeliveryMethod>
         <inv:InvoiceRows>
            <inv:InvoiceRow>
               <inv:ArticleId>?</inv:ArticleId>
               <inv:Description>?</inv:Description>
               <inv:Quantity>?</inv:Quantity>
               <inv:UnitPrice>?</inv:UnitPrice>
               <inv:VAT>?</inv:VAT>
            </inv:InvoiceRow>
         </inv:InvoiceRows>
         <inv:InvoiceType>?</inv:InvoiceType>
         <inv:OrderDate>?</inv:OrderDate>
         <inv:OrderNo>?</inv:OrderNo>
         <inv:ProductCode>?</inv:ProductCode>
         <inv:PurchaseType>?</inv:PurchaseType>
         <inv:Reference>?</inv:Reference>
         <inv:RegNo>?</inv:RegNo>
         <inv:SalesPerson>?</inv:SalesPerson>
         <inv:StoreId>?</inv:StoreId>
		*/
		
	}
	
	public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount){
		
    }
	
	public function void(\Magento\Payment\Model\InfoInterface $payment){
		
    }
	
	public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount){
		
    }
}