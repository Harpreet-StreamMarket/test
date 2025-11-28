<?php

/**
 * RoyalMailShipping by StreamMarket
 *
 * @category    StreamMarket
 * @package StreamMarket_RoyalMailShipping
 * @author  Product Development Team <support@StreamMarket.co.uk>
 * @license http://extensions.StreamMarket.co.uk/license
 *
 */

namespace StreamMarket\RoyalMailShipping\Controller\Adminhtml\Transaction;

use StreamMarket\RoyalMailShipping\Helper\Data;
use Exception;
use Psr\Log\LoggerInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;
/**
 * Description of CancelShipment
 */
class CancelShipment extends \Magento\Backend\App\Action
{
	/**
     * @var ShipmentRepositoryInterface
     */
    private $shipmentRepository;
 
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var \StreamMarket\RoyalMailShipping\Model\CarrierFactory
     */
    private $carrierFactory;

    /**
     * @var \StreamMarket\RoyalMailShipping\Model\TransactionFactory
     */
    private $transactionFactory;
	
	private $curlrequest;

    private $tracking;
	
	private $scopeConfig;
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'StreamMarket_RoyalMailShipping::transactions';
	const RM_DEBUG = 'carriers/smroyalmail/rm_debug';
	

    public function __construct(\Magento\Backend\App\Action\Context $context,
            \StreamMarket\RoyalMailShipping\Model\TransactionFactory $transactionFactory,
            \StreamMarket\RoyalMailShipping\Model\CarrierFactory $carrierFactory,
			\StreamMarket\RoyalMailShipping\Helper\CurlRequest $curlrequest,
			ShipmentRepositoryInterface $shipmentRepository,
			LoggerInterface $logger,
			\StreamMarket\RoyalMailShipping\Model\Tracking $tracking,
			\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
			)
    {
        parent::__construct($context);
        $this->transactionFactory = $transactionFactory;
        $this->carrierFactory = $carrierFactory;
		$this->curlrequest = $curlrequest;
		$this->shipmentRepository = $shipmentRepository;
        $this->logger = $logger;
		$this->tracking = $tracking;
		$this->scopeConfig = $scopeConfig;
    }


	public function getSystemConfigValue($system_code){
			$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
			return $this->scopeConfig->getValue($system_code, $storeScope);
	}
	
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $transactionId = $this->getRequest()->getParam('transaction_id');
        
            /* @var $transaction \StreamMarket\RoyalMailShipping\Model\Transaction */
            $transaction = $this->transactionFactory->create();
            $transaction->load($transactionId);
			$shipment_id = $transaction->getShipmentId();
			$tracking_number = $transaction->getShipmentNumber();
			$royalmail_shipment_id = $transaction->getRoyalmailShipmentId();
			$send_request = $this->curlrequest->makeACurlRequest($royalmail_shipment_id);
			$response = json_decode($send_request, true);
			$debug = $this->getSystemConfigValue(self::RM_DEBUG);
			
			if(isset($response['Errors'][0])){
				echo $response['Errors'] = $response['Errors'][0]['Message'];
			}else{
				echo $response['HttpStatusDescription'] = 'OK';
			}
			try {	
					if(isset($response['HttpStatusDescription']) == 'OK'){
						$transactions = $this->transactionFactory->create()->getCollection();
						$transactions->addFieldToFilter('request_type','createShipment')->addFieldToFilter('shipment_number',$tracking_number);
						$data = $transactions->getData();
						foreach($data as $val){
							$shipment_id = $val['shipment_id'];
							$orderId = $val['order_id'];
							$file_name_prepare = $val['label_file'];
							$request_type = 'cancelShipment';
							$this->saveInTransaction($file_name_prepare,$orderId,$shipment_id,$tracking_number,$request_type);
						}
						$resultRedirect = $this->resultRedirectFactory->create();
						$resultRedirect->setPath('smroyalmail/shipment/index');
						$this->messageManager->addSuccessMessage('The Shipment number #' . $tracking_number . '-' . __('canceled and tracking deleted from shipment.'));
						return $resultRedirect;
					}else{
						if($debug == 1){
						$writer = new \Zend_Log_Writer_Stream(BP . '/var/log/cancel_shipment_error.log');
						$logger = new \Zend_Log();
						$logger->addWriter($writer);
						$logger->info(print_r($response, true));
						}
						$msg = $response['Errors'];
						$resultRedirect->setPath('smroyalmail/shipment/index');
						$this->messageManager->addErrorMessage($msg);
						return $resultRedirect;
					}
			}
				catch (\Exception $e) {
						echo $e;
			$this->logger->critical($e->getMessage());
    }
    }
	
	public function saveInTransaction($file_name_prepare,$orderId,$shipmentId,$tracking_number,$request_type){
			
			$current_time = date("Y-m-d h:i:s");
			$transaction = $this->transactionFactory->create();
				$transaction->setShipmentId($shipmentId);
				$transaction->setOrderId($orderId);
				$transaction->setRequestType($request_type);
				$transaction->setStatus('Cancelled');
				$transaction->setShipmentNumber($tracking_number);
				$transaction->setLabelFile($file_name_prepare);
				$transaction->setCreatedAt($current_time);
				$transaction->setUpdatedAt($current_time);
				$transaction->save();
				
				$transactions = $this->transactionFactory->create()->getCollection();
                $transactions->addFieldToFilter('request_type',
                                \StreamMarket\RoyalMailShipping\Helper\Data::REQUEST_TYPE_CREATE_SHIPMENT)
                        ->addFieldToFilter('shipment_number',$tracking_number);
						foreach ($transactions as $_transaction):
                        $_transaction->setStatus(\StreamMarket\RoyalMailShipping\Helper\Data::STATUS_CANCELLED)
                                ->save();
                endforeach;
				$this->tracking->getTrackInfo($tracking_number);
		}
		
		

}
