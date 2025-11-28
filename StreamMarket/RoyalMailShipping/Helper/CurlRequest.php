<?php
namespace StreamMarket\RoyalMailShipping\Helper;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\App\Filesystem\DirectoryList;

class CurlRequest extends AbstractHelper
{
		protected $curl;
		
		protected $scopeConfig;
		
		protected $_encryptor;
		
		/**
		 * @var \Magento\Framework\App\Filesystem\DirectoryList
		 */
		private $directoryList;
		
		private $transactionFactory;
		
		protected $order;
		
		protected $messageManager;
		
		protected $resourceConnection;
	    
		public $_storeManager;
		
		protected $_fileSystem;
		
		private $_objectManager;
		
		private $filesystemIo;
	
   	    const XML_PATH_METHODS_RECIPIENT = 'carriers/smroyalmail/allowed_methods';
		const XML_PATH_POSTLOCATION = 'carriers/smroyalmail/postlocation';
		const XML_PATH_CLIENT_ID = 'carriers/smroyalmail/client_id';
		const XML_PATH_CLIENT_SECRET = 'carriers/smroyalmail/client_secret';
		const XML_PATH_API_USER = 'carriers/smroyalmail/api_user';
		const XML_PATH_API_PASSWORD = 'carriers/smroyalmail/api_password';
		const XML_PATH_GATEWAY_URL = 'carriers/smroyalmail/gateway_url';
		const XML_PATH_SHIPPING_LOCATION_ID = 'carriers/smroyalmail/shipping_location_id';
		const LABEL_DIR_NAME = 'sm_royalmail';
		const RM_DEBUG = 'carriers/smroyalmail/rm_debug';
		
		public function __construct(
		  Curl $curl,
		  DirectoryList $directoryList,
		  \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		  \Magento\Framework\Encryption\EncryptorInterface $encryptor,
		  \Streammarket\RoyalMailShipping\Model\TransactionFactory $transactionFactory,
		  \Magento\Sales\Api\Data\OrderInterface $order,
		  \Magento\Framework\Message\ManagerInterface $messageManager,
		  \Magento\Store\Model\StoreManagerInterface $storeManager,
		  \Magento\Framework\Filesystem\Io\File $filesystemIo,
		  \Magento\Framework\ObjectManagerInterface $objectmanager
	) {
		  $this->curl = $curl;
		  $this->scopeConfig = $scopeConfig;
		  $this->_encryptor = $encryptor;
		  $this->directoryList = $directoryList;
		  $this->transactionFactory = $transactionFactory;
		  $this->order = $order;
		  $this->messageManager = $messageManager;
		  $this->_storeManager=$storeManager;
		  $this->filesystemIo = $filesystemIo;
		  $this->_objectManager = $objectmanager;
		}
		
		public function getOrderDetails($orderId){
			
			$order = $this->order->load($orderId);
			return $order;
		}
		 
		public function makeACurlRequest($tracking_number) {
		
		  $auth_token = $this->generateToken();
		  $gateway_url = $this->getSystemConfigValue(self::XML_PATH_GATEWAY_URL);		
		  $curl = curl_init();

			curl_setopt_array($curl, [
			  CURLOPT_URL => "$gateway_url/shipments/status",
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 30,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "PUT",
			  CURLOPT_POSTFIELDS => "{\"Status\":\"Cancel\",\"ShipmentIds\":[\"$tracking_number\"],\"Reason\":\"Order Cancelled\"}",
			  CURLOPT_HTTPHEADER => [
				"accept: application/json",
				"authorization: Bearer $auth_token",
				"content-type: application/json"
			  ],
			]);

			$response = curl_exec($curl);
			curl_close($curl);
			return $response;

		}   
		
		public function HoldCurlRequest($tracking_number) {
		
		  $auth_token = $this->generateToken();
		  $gateway_url = $this->getSystemConfigValue(self::XML_PATH_GATEWAY_URL);		
		  $curl = curl_init();

			curl_setopt_array($curl, [
			  CURLOPT_URL => "$gateway_url/shipments/status",
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 30,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "PUT",
			  CURLOPT_POSTFIELDS => "{\"Status\":\"Hold\",\"ShipmentIds\":[\"$tracking_number\"],\"Reason\":\"Awaiting Payment\"}",
			  CURLOPT_HTTPHEADER => [
				"accept: application/json",
				"authorization: Bearer $auth_token",
				"content-type: application/json"
			  ],
			]);

			$response = curl_exec($curl);
			curl_close($curl);
			return $response;

		}   
		
		public function ReleaseCurlRequest($tracking_number) {
		
		  $auth_token = $this->generateToken();
		  $gateway_url = $this->getSystemConfigValue(self::XML_PATH_GATEWAY_URL);		
		  $curl = curl_init();

			curl_setopt_array($curl, [
			  CURLOPT_URL => "$gateway_url/shipments/status",
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 30,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "PUT",
			  CURLOPT_POSTFIELDS => "{\"Status\":\"Release\",\"ShipmentIds\":[\"$tracking_number\"]}",
			  CURLOPT_HTTPHEADER => [
				"accept: application/json",
				"authorization: Bearer $auth_token",
				"content-type: application/json"
			  ],
			]);

			$response = curl_exec($curl);
			curl_close($curl);
			return $response;

		} 
		
		public function PickedCurlRequest($tracking_number) {
		
		  $auth_token = $this->generateToken();
		  $gateway_url = $this->getSystemConfigValue(self::XML_PATH_GATEWAY_URL);		
		  $curl = curl_init();

			curl_setopt_array($curl, [
			  CURLOPT_URL => "$gateway_url/shipments/status",
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 30,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "PUT",
			  CURLOPT_POSTFIELDS => "{\"Status\":\"Picked\",\"ShipmentIds\":[\"$tracking_number\"]}",
			  CURLOPT_HTTPHEADER => [
				"accept: application/json",
				"authorization: Bearer $auth_token",
				"content-type: application/json"
			  ],
			]);

			$response = curl_exec($curl);
			curl_close($curl);
			return $response;
		} 
		
		
		public function getConfigValue(){
			$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
			return $this->scopeConfig->getValue(self::XML_PATH_METHODS_RECIPIENT, $storeScope);
		}
		
		public function getSystemConfigValue($system_code){
			$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
			return $this->scopeConfig->getValue($system_code, $storeScope);
		}
		
		/*public function generateToken() {
			$resource = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
			$connection = $resource->getConnection();
			$sql = "Select token_value FROM sm_royalmail_token where request_type = 'v4'";
			$result = $connection->fetchOne($sql);
			if($result){
				$response = $result;
			}else{
				$response = $this->generateRMToken();
			}
				return $response;				
		}*/
		
		public function generateToken() {
			
			$debug = $this->getSystemConfigValue(self::RM_DEBUG);
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
			$connection = $resource->getConnection();
			
			//Recover token if requested in the last 4 hours
			$sql = "Select *, DATE_ADD(updated_at, INTERVAL 30 minute) as tokenexpire, NOW() as currenttime FROM sm_royalmail_token WHERE DATE_ADD(updated_at, INTERVAL 30 minute) > NOW() and request_type = 'V4'";
			//$sql = "SELECT * FROM `sm_royalmail_token` WHERE request_type = 'V4'";
			$result = $connection->fetchRow($sql);
			
			if($result){
				$response = $result['token_value'];
			}else{
				$response = $this->generateRMToken();
				//print_r($response);
				if(isset($response['access_token'])){
					$access_token = $response['access_token'];
					$sql = "Insert Into sm_royalmail_token (id, token_value,cron_status,request_type) Values ('1','$access_token','0','V4') ON DUPLICATE KEY UPDATE token_value = '$access_token'";
					$connection->query($sql);
					return $response['access_token'];	
				}else{
					if($debug == 1){
						$writer = new \Zend_Log_Writer_Stream(BP . '/var/log/access_token_error.log');
						$logger = new \Zend_Log();
						$logger->addWriter($writer);
						$logger->info('generateToken');
						$logger->info(print_r($response, true));
					}
				}
			}
			return $response;				
		}
		
		public function generateRMToken($client_id = null,$client_secret = null) {
			
			
			if($client_id == ''){
				$client_id = $this->getSystemConfigValue(self::XML_PATH_CLIENT_ID);
			}
			if($client_secret == ''){
				$client_secret = $this->getSystemConfigValue(self::XML_PATH_CLIENT_SECRET);
			}
			$debug = $this->getSystemConfigValue(self::RM_DEBUG);
			try{			
			 $gateway_url = $this->getSystemConfigValue(self::XML_PATH_GATEWAY_URL);
			 $curl = curl_init();
				curl_setopt_array($curl, array(
				CURLOPT_URL => "https://authentication.proshipping.net/connect/token",
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => "",
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 30,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => "POST",
				CURLOPT_POSTFIELDS => "client_id=$client_id&client_secret=$client_secret&grant_type=client_credentials",
				CURLOPT_HTTPHEADER => array(
					"cache-control: no-cache",
					"content-type: application/x-www-form-urlencoded"
				),
			));
			 $response = curl_exec($curl);
			 $err = curl_error($curl);
			 curl_close($curl);
			 $result = null;
			 if (!$err)
			 {
				 $result = json_decode($response, true);
				  return $result;
			 }else{
				 return $response;
			 }
			}catch (\Exception $e) {
				
				if($debug == 1){
					$writer = new \Zend_Log_Writer_Stream(BP . '/var/log/access_token_error.log');
					$logger = new \Zend_Log();
					$logger->addWriter($writer);
					$logger->info($e);
				}
			
            }
		}
		
		public function decryptFormat($value){
			
			return $this->_encryptor->decrypt($value);
		}
		
		public function reGenerateLabel($royalmail_shipment_id) {
		
		  $auth_token = $this->generateToken();
		  $gateway_url = $this->getSystemConfigValue(self::XML_PATH_GATEWAY_URL);
		  $URL = "$gateway_url/shipments/printLabel/rm/$royalmail_shipment_id?labelFormat=PDF";
		 
		  //set curl options
		  $this->curl->setOption(CURLOPT_HEADER, 0);
		  $this->curl->setOption(CURLOPT_TIMEOUT, 60);
		  $this->curl->setOption(CURLOPT_RETURNTRANSFER, true);
		  
		  //set curl header
		  $this->curl->addHeader("Content-Type", "application/json");
		  $this->curl->addHeader("accept", "application/json");
		  $this->curl->addHeader("authorization", "Bearer $auth_token");
		  $this->curl->get($URL);
		  //read response
		  $response = $this->curl->getBody();
		  $data = json_decode($response, TRUE);
		  if($data){
			  return $data;
		  }else{
			return array("error" => "Unable to generate compatible pdf");
		  }
		}
		
		
		public function massManifestCreate() {
			
		  $auth_token = $this->generateToken();
		  $gateway_url = $this->getSystemConfigValue(self::XML_PATH_GATEWAY_URL);
		  $shipping_location_id = $this->getSystemConfigValue(self::XML_PATH_SHIPPING_LOCATION_ID);
		  $debug = $this->getSystemConfigValue(self::RM_DEBUG);
		  $manifest_automatically_status = $this->getSystemConfigValue('carriers/smroyalmail/manifest_automatically');
		  
		  if($manifest_automatically_status == 1){
		  $URL = "$gateway_url/manifests/RM";
		  $jsonData = "{\"ShippingLocationId\":\"$shipping_location_id\"}";
		 
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
		  
		  if($debug == 1){
			$writer = new \Zend_Log_Writer_Stream(BP . '/var/log/rm_massaction_cron.log');
			$logger = new \Zend_Log();
			$logger->addWriter($writer);
			$logger->info($data);
		  }
		  try {
		  if(!isset($data['Errors'])){  
			  foreach($data as $manifest){
					$ManifestImage = $manifest['ManifestImage'];
					$ManifestNumber = $manifest['ManifestNumber'];
					$name = 'manifest_'.$ManifestNumber;
					$this->saveFile($ManifestImage,'pdf',$name,$ManifestNumber,'mass manifest generate');
				}  
		  }
		if(isset($data['Errors'])){
		  $Errors = $data['Errors'];
		  foreach($Errors as $data){
			  $Message = $data['Message'];
			}
		  }
		}catch (\Exception $e) {
			echo $e;
			//echo $data;
			
        }
		  }else{
				if($debug == 1){
				$writer = new \Zend_Log_Writer_Stream(BP . '/var/log/rm_massaction_cron.log');
				$logger = new \Zend_Log();
				$logger->addWriter($writer);
				$logger->info("Please Enable Run EOD Process(Manifest) Automatically option in RoyalMail Shipping Section");
			  }
		  }
	}
	
	
	public function getTrackingInfo($tracking_number,$shipmentId,$flag)
    {
        $transactions = $this->transactionFactory->create()->getCollection();
		$transactions->addFieldToFilter('request_type','printLabel')->addFieldToFilter('shipment_number',$tracking_number)->addFieldToFilter('shipment_id',$shipmentId)->addFieldToFilter('custom_form',$flag);
		$data = $transactions->getData();
		foreach($data as $val){
			return $file_name_prepare = $val['label_file'];
		}
    }
	
	public function getBaseUrl(){
		return $this->_storeManager->getStore()->getBaseUrl();
	}
	
	public function getAvailbiltyRequest(){
		  $auth_token = $this->generateToken();
		  $gateway_url = $this->getSystemConfigValue(self::XML_PATH_GATEWAY_URL);
		  $URL = "$gateway_url/carriers/RM/services";
		  
		  //set curl options
		  $this->curl->setOption(CURLOPT_HEADER, 0);
		  $this->curl->setOption(CURLOPT_TIMEOUT, 60);
		  $this->curl->setOption(CURLOPT_RETURNTRANSFER, true);
		  
		  //set curl header
		  $this->curl->addHeader("Content-Type", "application/json");
		  $this->curl->addHeader("accept", "application/json");
		  $this->curl->addHeader("authorization", "Bearer $auth_token");
		  $this->curl->get($URL);
		  //read response
		  $response = $this->curl->getBody();
		  $data = json_decode($response, TRUE);
		  if($data){
			  return $data;
		  }else{
			return array("error" => "No Service Code Available");
		  }
	}
	
	public function getErrorCode(){
		
		$codes =  array(
                '400' => __('The request was invalid. Details are provided in the error messages.'),
                '401' => __('Service Unauthorized. Please contact to the developer if occurred again.'),
                '500' => __('Internal Server Error.'),
                '503' => __('Service Unavailable Please contact to the developer if occurred again')
                );
				return $codes;
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