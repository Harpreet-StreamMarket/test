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

namespace StreamMarket\RoyalMailShipping\Controller\Adminhtml\Shipment;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\HTTP\Client\Curl;

/**
 * Description of ShipWithRoyalmail
 */
 
class CreateManifest extends \Magento\Backend\App\Action
{
	protected $curl;

    /**
     * @var \Magento\Sales\Api\ShipmentRepositoryInterface
     */
    private $shipmentRepository;
	
	
    /**
     * @var \StreamMarket\RoyalMailShipping\Model\Carrier
     */
    private $carrier;
	
	private $tracking;
	
	/**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    private $directoryList;
	
	private $transactionFactory;

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
	 
	protected $order;
	
    private $objectManager;
	
	private $transaction;
	
	private $sqldata;
 
	private $helper;
    
	const ADMIN_RESOURCE = 'Magento_Sales::shipment';
	
	const LABEL_DIR_NAME = 'sm_royalmail';
	
	public function __construct(\Magento\Backend\App\Action\Context $context,
            \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepository,
            \StreamMarket\RoyalMailShipping\Model\Carrier $carrier,
			\StreamMarket\RoyalMailShipping\Model\Tracking $tracking,
			\Magento\Framework\ObjectManagerInterface $objectmanager,
			\Magento\Sales\Api\Data\OrderInterface $order,
			DirectoryList $directoryList,
			\StreamMarket\RoyalMailShipping\Model\Transaction $transaction,
			\StreamMarket\RoyalMailShipping\Helper\CurlRequest $sqldata,
			\StreamMarket\RoyalMailShipping\Model\TransactionFactory $transactionFactory,
			Curl $curl,
			\StreamMarket\RoyalMailShipping\Helper\CurlRequest $helper
			
			)
    {
        parent::__construct($context);
        $this->carrier = $carrier;
		$this->tracking = $tracking;
        $this->shipmentRepository = $shipmentRepository;
		$this->objectManager = $objectmanager;
		$this->order = $order;
		$this->directoryList = $directoryList;
		$this->transaction = $transaction;
		$this->sqldata = $sqldata;
		$this->transactionFactory = $transactionFactory;
		$this->curl = $curl;
		$this->helper = $helper;
		
    }

    public function execute()
    {
		$auth_token = $this->helper->generateToken();
		$gateway_url = $this->helper->getSystemConfigValue('carriers/smroyalmail/gateway_url');
		$ShippingAccountId = $this->helper->getSystemConfigValue('carriers/smroyalmail/shipping_account_id');
		$shipping_location_id = $this->helper->getSystemConfigValue('carriers/smroyalmail/shipping_location_id');
		$auth_token = $this->helper->generateToken();
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$debug = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('carriers/smroyalmail/rm_debug');
		
		$serviceOffering = $this->getRequest()->getParam('service_offering');
		
		$menifest_offering = $this->getRequest()->getParam('menifest_offering');
		$shipping_status = $this->getRequest()->getParam('shipping_status');
		
		$reference = $this->getRequest()->getParam('reference');
		$URL = "$gateway_url/manifests/RM";
		$prepare_url = '';
		if($menifest_offering == 'shipping_account'){
			$jsonData = "{\"ShippingAccountId\":\"$ShippingAccountId\"}";
			$prepare_url = $URL.'/ShippingAccountId/shipping_account';
		}
		if($menifest_offering == 'shipping_location'){
			$jsonData = "{\"ShippingLocationId\":\"$shipping_location_id\"}";
			$prepare_url = $URL.'/ShippingLocationId/shipping_location';
		}
		if($menifest_offering == 'shipping_status'){
			$jsonData = "{\"Status\":\"$shipping_status\"}";
			$prepare_url = $URL.'/Status/'.$shipping_status;
		}
		else{
			$jsonData = "{\"ServiceCode\":\"$serviceOffering\"}";
			$prepare_url = $URL.'/ServiceCode/'.$serviceOffering;
		}
		
		
		if($debug == 1){
				$writer = new \Zend_Log_Writer_Stream(BP . '/var/log/RmManifestRequest.log');
				$logger = new \Zend_Log();
				$logger->addWriter($writer);
				$logger->info(print_r($prepare_url, true));
				}		
		
		//set curl options
		  $this->curl->setOption(CURLOPT_HEADER, 0);
		  $this->curl->setOption(CURLOPT_TIMEOUT, 60);
		  $this->curl->setOption(CURLOPT_RETURNTRANSFER, true);
		  $this->curl->setOption(CURLOPT_CUSTOMREQUEST, "POST");
		  
		  //set curl header
		  $this->curl->addHeader("Content-Type", "application/json");
		  $this->curl->addHeader("accept", "application/json");
		  $this->curl->addHeader("authorization", "Bearer $auth_token");
		  //post request with url and data
		  $this->curl->post($URL, $jsonData);
		  //read response
		  $response = $this->curl->getBody();
		  $data = json_decode($response, TRUE);
		  
		  try {
			if(!isset($data['Errors'])){  
			foreach($data as $manifest){
				$ManifestImage = $manifest['ManifestImage'];
			    $ManifestNumber = $manifest['ManifestNumber'];
				$name = 'manifest_'.$ManifestNumber;
			    $this->saveFile($ManifestImage,'pdf',$name,$ManifestNumber,$reference);
		        $this->messageManager->addSuccessMessage('Manifests generate successfully.');
			}}
			if($debug == 1){
				$writer = new \Zend_Log_Writer_Stream(BP . '/var/log/RmManifestResponse.log');
				$logger = new \Zend_Log();
				$logger->addWriter($writer);
				$logger->info(print_r($data, true));
				}			
		if(isset($data['Message'])){
		  $Message = $data['Message'];
		  $this->messageManager->addErrorMessage($Message);
		}
		}catch (\Exception $e) {
			echo $data;
			$this->messageManager->addErrorMessage($e->getMessage());
        }
		
    }
	
	public function saveFile($data,$format,$name,$ManifestNumber,$reference)
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
			$this->saveInTransaction($file_name_prepare,'createManifest',$ManifestNumber,$reference);
			return str_replace($mediaPath, '', $fileName);
		}
		
		public function saveInTransaction($file_name_prepare,$request_type,$ManifestNumber,$reference){
			
			$current_time = date("Y-m-d h:i:s");
			$transaction = $this->transactionFactory->create();
				$transaction->setShipmentId();
				$transaction->setOrderId();
				$transaction->setRequestType($request_type);
				$transaction->setStatus('Printed');
				$transaction->setShipmentNumber($ManifestNumber);
				$transaction->setLabelFile($file_name_prepare);
				$transaction->setReference($reference);
				$transaction->setCreatedAt($current_time);
				$transaction->setUpdatedAt($current_time);
				$transaction->save();
				
		}
	}
