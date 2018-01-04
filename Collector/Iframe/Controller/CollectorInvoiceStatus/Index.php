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

namespace Collector\Iframe\Controller\CollectorInvoiceStatus;

class Index extends \Magento\Framework\App\Action\Action
{

    protected $resultPageFactory;
    protected $helper;
	protected $cart;
	protected $filterBuilder;
	protected $filterGroupBuilder;
	protected $searchCriteriaBuilder;
	
    protected $jsonHelper;
	protected $layoutFactory;
    protected $objectManager;
	protected $resultJsonFactory;
    protected $formKey;
	protected $orderInterface;
	protected $quoteCollectionFactory;
	protected $storeManager;
	protected $customerFactory;
	protected $customerRepository;
	protected $shippingRate;
    protected $checkoutSession;
	protected $cartRepositoryInterface;
	protected $eventManager;
	protected $cartManagementInterface;
    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context  $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Collector\Iframe\Helper\Data $_helper,
		\Magento\Checkout\Model\Cart $_cart,
        \Magento\Framework\Api\FilterBuilder $_filterBuilder,
        \Magento\Framework\Api\Search\FilterGroupBuilder $_filterGroupBuilder,
        \Magento\Framework\Api\SearchCriteriaBuilder $_searchCriteriaBuilder,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
		
		\Magento\Framework\View\Result\LayoutFactory $_layoutFactory,
		\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
		\Magento\Quote\Api\CartRepositoryInterface $_cartRepositoryInterface,
		\Magento\Quote\Api\CartManagementInterface $_cartManagementInterface,
        \Magento\Framework\Data\Form\FormKey $formKey,
		\Magento\Store\Model\StoreManagerInterface $_storeManager,
		\Magento\Customer\Api\CustomerRepositoryInterface $_customerRepository,
		\Magento\Checkout\Model\Session $_checkoutSession,
		\Magento\Customer\Model\CustomerFactory $_customerFactory,
		\Magento\Sales\Api\Data\OrderInterface $_orderInterface,
		\Magento\Quote\Model\ResourceModel\Quote\CollectionFactory $_quoteCollectionFactory,
		\Magento\Quote\Model\Quote\Address\Rate $_shippingRate,
		\Magento\Framework\Event\Manager $eventManager,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->helper = $_helper;
        $this->filterBuilder = $_filterBuilder;
		$this->cart = $_cart;
		$this->filterGroupBuilder = $_filterGroupBuilder;
		$this->searchCriteriaBuilder = $_searchCriteriaBuilder;
		
		$this->formKey = $formKey;
		$this->layoutFactory = $_layoutFactory;
		$this->eventManager = $eventManager;
        $this->jsonHelper = $jsonHelper;
        $this->checkoutSession = $_checkoutSession;
		$this->orderInterface = $_orderInterface;
		$this->resultJsonFactory = $resultJsonFactory;
		$this->objectManager = $context->getObjectManager();
        $this->quoteCollectionFactory = $_quoteCollectionFactory;
		$this->storeManager = $_storeManager;
		$this->customerRepository = $_customerRepository;
		$this->customerFactory = $_customerFactory;
		$this->shippingRate = $_shippingRate;
		$this->cartRepositoryInterface = $_cartRepositoryInterface;
		$this->cartManagementInterface = $_cartManagementInterface;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if (isset($_GET['OrderNo']) && isset($_GET['InvoiceStatus'])){
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $order = $objectManager->get('\Magento\Sales\Api\Data\OrderInterface')->loadByIncrementId($_GET['OrderNo']);
            if ($order->getId()){
                if ($_GET['InvoiceStatus'] == "0"){
                    $status = $this->helper->getHoldStatus();
                    $order->setState($status)->setStatus($status);
                    $order->save();
                }
                else if ($_GET['InvoiceStatus'] == "1"){
                    $status = $this->helper->getAcceptStatus();
                    $order->setState($status)->setStatus($status);
                    $order->save();
                }
                else {
                    $status = $this->helper->getDeniedStatus();
                    $order->setState($status)->setStatus($status);
                    $order->save();
                }
            }
			else {
				$quote = $objectManager->get(\Magento\Quote\Model\ResourceModel\Quote\CollectionFactory::class)->create()->getItemByColumnValue('reserved_order_id', $_GET['OrderNo']);
				$this->createOrder($quote, $_GET['OrderNo']);
				//create orders
			}
        }
        return $this->resultPageFactory->create();
    }
	
	public function createOrder($quote, $incrementID){
		$privId = $quote->getData('collector_private_id');
		$btype = $quote->getData('collector_btype');
		$_SESSION['curr_shipping_code'] = $quote->getData('curr_shipping_code');
		$response = $this->getResp($privId, $btype);
		$resultPage = $this->resultPageFactory->create();
		try {
			$paymentMethod = '';
			switch ($response['data']['purchase']['paymentName']){
				case 'DirectInvoice':
					$paymentMethod = 'collector_invoice';
				break;
				case 'PartPayment':
					$paymentMethod = 'collector_partpay';
				break;
				case 'Account':
					$paymentMethod = 'collector_account';
				break;
				case 'Card':
					$paymentMethod = 'collector_card';
				break;
				case 'Bank':
					$paymentMethod = 'collector_bank';
				break;
				default:
					$paymentMethod = 'collector_invoice';
				break;
			}
			$_SESSION['col_paymentmethod'] = $paymentMethod;
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$exOrder = $this->orderInterface->loadByIncrementId($response['data']['reference']);
			if ($exOrder->getIncrementId()){
				return $resultPage;
			}
			if ($response['data']['customer']['deliveryAddress']['country'] == 'Sverige'){
				$shippingCountryId = "SE";
			}
			else if ($response['data']['customer']['deliveryAddress']['country'] == 'Norge'){
				$shippingCountryId = "NO";
			}
			else {
				return $resultPage;
			}
			if ($response['data']['customer']['billingAddress']['country'] == 'Sverige'){
				$billingCountryId = "SE";
			}
			else if ($response['data']['customer']['billingAddress']['country'] == 'Norge'){
				$billingCountryId = "NO";
			}
			else {
				return $resultPage;
			}
			if (isset($_SESSION['collector_applied_discount_code'])){
				$discountCode = $_SESSION['collector_applied_discount_code'];
			}
			$shippingCode = $_SESSION['curr_shipping_code'];
			
			$actual_quote = $this->quoteCollectionFactory->create()->addFieldToFilter("reserved_order_id", $response['data']['reference'])->getFirstItem();
			
			$actual_quote_id = $actual_quote->getId();
			
			//init the store id and website id @todo pass from array
			$store = $this->storeManager->getStore();
			$websiteId = $this->storeManager->getStore()->getWebsiteId();
			//init the customer
			$customer = $this->customerFactory->create();
			$customer->setWebsiteId($websiteId);
			$customer->loadByEmail($response['data']['customer']['email']); // load customer by email address
			//check the customer
			if (!$customer->getEntityId()){
				//If not avilable then create this customer
				$customer->setWebsiteId($websiteId)
						->setStore($store)
						->setFirstname($response['data']['customer']['billingAddress']['firstName'])
						->setLastname($response['data']['customer']['billingAddress']['lastName'])
						->setEmail($response['data']['customer']['email'])
						->setPassword($response['data']['customer']['email']);
				$customer->save();
			}
			$customer->setEmail($response['data']['customer']['email']);
			$customer->save();
			$actual_quote->setCustomerEmail($response['data']['customer']['email']);
			$actual_quote->setStore($store);
			$customer = $this->customerRepository->getById($customer->getEntityId());
			$actual_quote->setCurrency();
			$actual_quote->assignCustomer($customer);
			if (isset($_SESSION['collector_applied_discount_code'])){
				$actual_quote->setCouponCode($discountCode);
			}
			//Set Address to quote @todo add section in order data for seperate billing and handle it
			
			$billingAddress = array(
				'firstname' => $response['data']['customer']['billingAddress']['firstName'],
				'lastname' => $response['data']['customer']['billingAddress']['lastName'],
				'street' => $response['data']['customer']['billingAddress']['address'],
				'city' => $response['data']['customer']['billingAddress']['city'],
				'country_id' => $billingCountryId,
				'postcode' => $response['data']['customer']['billingAddress']['postalCode'],
				'telephone' => $response['data']['customer']['mobilePhoneNumber']
			);
			$shippingAddressArr = array(
				'firstname' => $response['data']['customer']['deliveryAddress']['firstName'],
				'lastname' => $response['data']['customer']['deliveryAddress']['lastName'],
				'street' => $response['data']['customer']['deliveryAddress']['address'],
				'city' => $response['data']['customer']['deliveryAddress']['city'],
				'country_id' => $shippingCountryId,
				'postcode' => $response['data']['customer']['deliveryAddress']['postalCode'],
				'telephone' => $response['data']['customer']['mobilePhoneNumber']
			);
			$actual_quote->getBillingAddress()->addData($billingAddress);
			$actual_quote->getShippingAddress()->addData($shippingAddressArr);
			
			// Collect Rates and Set Shipping & Payment Method
			$this->shippingRate->setCode($shippingCode)->getPrice();
			$shippingAddress = $actual_quote->getShippingAddress();
			//@todo set in order data
			$shippingAddress->setCollectShippingRates(true)
					->collectShippingRates()
					->setShippingMethod($shippingCode); //shipping method
			$actual_quote->getShippingAddress()->addShippingRate($this->shippingRate);
			$actual_quote->setPaymentMethod($paymentMethod); //payment method
			$actual_quote->getPayment()->importData(['method' => $paymentMethod]);
			$actual_quote->setReservedOrderId($response['data']['reference']);
			// Collect total and save
			$actual_quote->collectTotals();
			// Submit the quote and create the order
			$actual_quote->save();
			$cart = $this->cartRepositoryInterface->get($actual_quote->getId());
			$cart->setCustomerEmail($response['data']['customer']['email']);
			$cart->setCustomerId($customer->getId());
			$cart->getBillingAddress()->addData($billingAddress);
			$cart->getShippingAddress()->addData($shippingAddressArr);
			$cart->getBillingAddress()->setEmail($response['data']['customer']['email']);
			$cart->getShippingAddress()->setEmail($response['data']['customer']['email']);
			$cart->setCustomerEmail($response['data']['customer']['email']);
			$cart->save();
			$cart->setCustomerId($customer->getId());
			$cart->setCustomerEmail($response['data']['customer']['email']);
			$cart->getBillingAddress()->setEmail($response['data']['customer']['email']);
			$cart->getShippingAddress()->setEmail($response['data']['customer']['email']);
			$cart->getBillingAddress()->setCustomerId($customer->getId());
			$cart->getShippingAddress()->setCustomerId($customer->getId());
			$cart->assignCustomer($customer);
			$cart->save();
			$_SESSION['is_iframe'] = 1;
			$order_id = $this->cartManagementInterface->placeOrder($cart->getId());
			$order = $objectManager->create('\Magento\Sales\Model\Order')->load($order_id);
			$emailSender = $objectManager->create('\Magento\Sales\Model\Order\Email\Sender\OrderSender');
			$emailSender->send($order);
			$order->setData('collector_invoice_id', $response['data']['purchase']['purchaseIdentifier']);
			$fee = 0;
			foreach ($response['data']['order']['items'] as $item){
				if ($item['id'] == 'invoice_fee'){
					$fee = $item['unitPrice'];
				}
			}
			$order->setData('fee_amount', $fee);
			$order->setData('base_fee_amount', $fee);
			
			$order->setGrandTotal($order->getGrandTotal() + $fee);
			$order->setBaseGrandTotal($order->getBaseGrandTotal() + $fee);
			
			
			if ($response["data"]["purchase"]["result"] == "OnHold"){
                $status = $this->helper->getHoldStatus();
				$order->setState($status)->setStatus($status);
				$order->save();
			}
			else if ($response["data"]["purchase"]["result"] == "Preliminary"){
                $status = $this->helper->getAcceptStatus();
				$order->setState($status)->setStatus($status);
				$order->save();
			}
			else {
                $status = $this->helper->getDeniedStatus();
				$order->setState($status)->setStatus($status);
				$order->save();
			}
			
			$this->eventManager->dispatch(
					'checkout_onepage_controller_success_action',
					['order_ids' => [$order->getId()]]
			);
			
			$this->checkoutSession->clearStorage();
			$this->checkoutSession->clearQuote();
			return $resultPage;
		}
		catch (\Exception $e){
			file_put_contents("var/log/collector.log", "checkout error: " . $e->getMessage() . "\n", FILE_APPEND);
			return $resultPage;
		}
	}
	
	public function getResp($privId, $btype){
		$init = $this->helper->getWSDL();
		if($privId){
			if(isset($btype)){
				if($btype == 'b2b'){
					$pusername = $this->helper->getUsername();
					$psharedSecret = $this->helper->getPassword();
					$pstoreId = $this->helper->getB2BStoreID();
					$array['storeId'] = $pstoreId;
				} else {
					$pusername = $this->helper->getUsername();
					$psharedSecret = $this->helper->getPassword();
					$pstoreId = $this->helper->getB2CStoreID();
					$array['storeId'] = $pstoreId;
				}
				
			} else {
				$pusername = $this->helper->getUsername();
				$psharedSecret = $this->helper->getPassword();
				$pstoreId = $this->helper->getB2CStoreID();
				$array['storeId'] = $pstoreId;
			}
					
			$path = '/merchants/'.$pstoreId.'/checkouts/'.$privId;
			$hash = $pusername.":".hash("sha256",$path.$psharedSecret);
			$hashstr = 'SharedKey '.base64_encode($hash);

			$ch = curl_init($init.$path);
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
			}
			else {
				$result['code'] = 0;
				$result['error'] = $data["error"];
			}
			return $result;
		}
	}
}
