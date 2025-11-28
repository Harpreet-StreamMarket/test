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
use Magento\Framework\App\Filesystem\DirectoryList;
/**
 * Description of ReprintLabel
 */
class ReprintLabel extends \Magento\Backend\App\Action
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
	 
	 /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    private $directoryList;
	
    const ADMIN_RESOURCE = 'StreamMarket_RoyalMailShipping::transactions';
	const RM_DEBUG = 'carriers/smroyalmail/rm_debug';
	const LABEL_DIR_NAME = 'sm_royalmail';
	

    public function __construct(\Magento\Backend\App\Action\Context $context,
            \StreamMarket\RoyalMailShipping\Model\TransactionFactory $transactionFactory,
            \StreamMarket\RoyalMailShipping\Model\CarrierFactory $carrierFactory,
			\StreamMarket\RoyalMailShipping\Helper\CurlRequest $curlrequest,
			ShipmentRepositoryInterface $shipmentRepository,
			LoggerInterface $logger,
			\StreamMarket\RoyalMailShipping\Model\Tracking $tracking,
			\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
			DirectoryList $directoryList
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
		$this->directoryList = $directoryList;
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
			$response = $this->curlrequest->reGenerateLabel($royalmail_shipment_id);
					
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
						$file_name = "Label_".$tracking_number."_0";
						$label_img = $this->saveFile($response['Label'], 'pdf', $file_name);
						foreach($data as $val){
							$this->saveInTransaction($tracking_number,$label_img);
						}
						$resultRedirect = $this->resultRedirectFactory->create();
						$resultRedirect->setPath('smroyalmail/shipment/index');
						$this->messageManager->addSuccessMessage('The PDF generated successfully for the shipment #' . $tracking_number);
						return $resultRedirect;
					}else{
						if($debug == 1){
						$writer = new \Zend_Log_Writer_Stream(BP . '/var/log/regenrate_label_error.log');
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
	
	public function saveFile($data, $format, $name)
		{
			$mediaPath = $this->directoryList->getUrlPath(DirectoryList::MEDIA);
			$dir = $mediaPath . DIRECTORY_SEPARATOR . self::LABEL_DIR_NAME . DIRECTORY_SEPARATOR . date('Y') . DIRECTORY_SEPARATOR . date('Y-m-d') . DIRECTORY_SEPARATOR;
			
			$file_name_prepare = '/' . self::LABEL_DIR_NAME . '/' . date('Y') . '/' . date('Y-m-d') . '/' . $name . '_' . date('H-i-s').'.pdf';
			if (!file_exists($dir)) {
				@mkdir($dir, 0755, true);
			}
			$fileName = $dir . $name . '_' . date('H-i-s');
			$format = strtolower($format);
			switch ($format):
				case 'png':
					$fileName .= '.png';
					file_put_contents($fileName, base64_decode($data));
					break;
				case 'ds':
					$this->_logger->debug('Data stream not supported yet by Royal Mail Shipping module.');
					/* not implemented */
					break;
				case 'pdf'://PDF
				default ://DEFAULT
					$fileName .= '.pdf';
					file_put_contents($fileName, base64_decode($data));
			endswitch;
			
			return $file_name_prepare;
	}
	
	public function saveInTransaction($tracking_number,$label_img){
			
			$transactions = $this->transactionFactory->create()->getCollection();
                $transactions->addFieldToFilter('shipment_number',$tracking_number);
						foreach ($transactions as $_transaction):
                        $_transaction->setLabelFile($label_img)
                                ->save();
                endforeach;
	}
}
