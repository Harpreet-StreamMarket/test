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
use Magento\Framework\Controller\ResultFactory;

/**
 * Description of ShipWithRoyalmail
 */
 
class ShipWithRoyalmail extends \Magento\Backend\App\Action
{

    /**
     * @var \Magento\Sales\Api\ShipmentRepositoryInterface
     */
    private $shipmentRepository;
	
	private $productRepository; 
	
	protected $curl;
	
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
			\StreamMarket\RoyalMailShipping\Model\TransactionFactory $transactionFactory,
			\StreamMarket\RoyalMailShipping\Helper\CurlRequest $helper,
			\Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
			Curl $curl
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
		$this->transactionFactory = $transactionFactory;
		$this->helper = $helper;
		$this->productRepository = $productRepository;
		$this->curl = $curl;
    }
	
	public function loadProduct($sku)
{
    return $this->productRepository->get($sku);
}

    public function execute()
    {
		  
		$resultRedirect = $this->resultRedirectFactory->create();
		$shipmentCollection = $this->objectManager->create('Magento\Sales\Model\Order\Shipment');
		
		$shipmentId = $this->getRequest()->getParam('shipment_id');
		$service_offering = $this->getRequest()->getParam('service_offering');
		$package_width = $this->getRequest()->getParam('package_width');
		$package_length = $this->getRequest()->getParam('package_length');
		$package_height = $this->getRequest()->getParam('package_height');
		$product_type = $this->getRequest()->getParam('product_type');
		$package_weight = $this->getRequest()->getParam('package_weight');
		$parcel_type = $this->getRequest()->getParam('parcel_type');
		$signed_for = $this->getRequest()->getParam('signed_for');
		$shipment = $shipmentCollection->load($shipmentId);
		$orderId = $shipment->getOrderId();
		$order = $this->order->load($orderId);
		$item_details = $shipment->getAllItems();		
		$shippingAddress = $order->getShippingAddress()->getData();
		$billingAddress = $order->getBillingAddress()->getData();
		$this->callPostAPI($shipmentId,$shippingAddress,$billingAddress,$item_details,$orderId,$order,$service_offering,$package_height,$package_length,$package_width,$product_type,$package_weight,'',$parcel_type,$signed_for);
		
    }

	public function callPostAPI($shipmentId,$shippingAddress,$billingAddress,$item_details,$orderId,$order,$service_offering,$package_height,$package_length,$package_width,$product_type,$package_weight,$massaction,$parcel_type,$signed_for) {
		
		$success_msg = null;
		$msg = null;
		try {
			
		$auth_token = $this->helper->generateToken();
		
		$client_id = $this->helper->getSystemConfigValue('carriers/smroyalmail/client_id');
		$postlocation = $this->helper->getSystemConfigValue('carriers/smroyalmail/postlocation');
		$gateway_url = $this->helper->getSystemConfigValue('carriers/smroyalmail/gateway_url');
		$labels_merge_flag = $this->helper->getSystemConfigValue('carriers/smroyalmail/smroyalmail_item/merge_label');
		
		$eori_number = $this->helper->getSystemConfigValue('carriers/smroyalmail/smroyalmail_item/EORI');
		$exportlicence = $this->helper->getSystemConfigValue('carriers/smroyalmail/smroyalmail_item/exportlicence');
		$addresseeidentificationreferencenumber = $this->helper->getSystemConfigValue('carriers/smroyalmail/smroyalmail_item/addresseeidentificationreferencenumber');
		
		$shipping_account_id = $this->helper->getSystemConfigValue('carriers/smroyalmail/shipping_account_id');
		$shipping_location_id = $this->helper->getSystemConfigValue('carriers/smroyalmail/shipping_location_id');
		
		
		$descriptionofgoods = $this->helper->getSystemConfigValue('carriers/smroyalmail/smroyalmail_item/descriptionofgoods');
		$reasonforexport = $this->helper->getSystemConfigValue('carriers/smroyalmail/smroyalmail_item/reasonforexport');
	
		$hscode_config = $this->helper->getSystemConfigValue('carriers/smroyalmail/smroyalmail_item/hscode');
		
		$country_of_manufacture = $this->helper->getSystemConfigValue('carriers/smroyalmail/smroyalmail_item/country_of_manufacture');
		
		$country_code = $this->helper->getSystemConfigValue('general/country/default');
		
		//$currency_code = $this->helper->getSystemConfigValue('currency/options/base');
		$currency_code = $order->getOrderCurrencyCode();
		$weight_of_unit = $this->helper->getSystemConfigValue('carriers/smroyalmail/unit_of_measure');
		$dimention_unit_of_measure = $this->helper->getSystemConfigValue('carriers/smroyalmail/dimention_unit_of_measure');
		$street_address = $order->getShippingAddress()->getStreet();
		$billing_address = $order->getBillingAddress()->getStreet();
		
		$increment_id = $order->getIncrementId();
		$shippingAmount = (float)$order->getShippingAmount();
		
		if($package_width == ''){
		$package_width = $this->helper->getSystemConfigValue('carriers/smroyalmail/smroyalmail_item/package_width');
		}
		if($package_height == ''){
		$package_height = $this->helper->getSystemConfigValue('carriers/smroyalmail/smroyalmail_item/package_height');
		}
		if($package_length == ''){
		$package_length = $this->helper->getSystemConfigValue('carriers/smroyalmail/smroyalmail_item/package_length');
		}
		if($product_type == ''){
		$product_type = $this->helper->getSystemConfigValue('carriers/smroyalmail/smroyalmail_item/package_type');
		}
		
		
		/*$bill_first_addr_line = '';
		$bill_second_addr_line = '';
		$bill_third_addr_line = '';
		if(isset($street_address[0])){
			$bill_first_addr_line = $billing_address[0];
		}
		if(isset($street_address[1])){
			$bill_second_addr_line = $billing_address[1];
		}
		if(isset($third_addr_line[2])){
			$bill_third_addr_line = $billing_address[2];
		}*/
		$first_addr_line = '';
		$second_addr_line = '';
		$third_addr_line = '';
		if(isset($street_address[0])){
			$first_addr_line = $street_address[0];
		}
		if(isset($street_address[1])){
			$second_addr_line = $street_address[1];
		}
		if(isset($third_addr_line[2])){
			$third_addr_line = $street_address[2];
		}
		
		$date = date("Y-m-d");
		$total_weight = 0;
		$total_price = 0;
		$hscode = '';
		$json_format_items = '[';
		foreach ($item_details as $item) {
		$data = $item->getData();
		$name = $data['name'];
		if(isset($data['qty'])){
			$qty_ordered = $data['qty'];
		}else{
			$qty_ordered = $data['qty_ordered'];
		}
		$price = $data['price'];
		$sku = $data['sku'];
		$_product = $this->loadProduct($sku);
		if($hscode_config){
		$hscode = $_product->getResource()->getAttribute($hscode_config)->getFrontend()->getValue($_product);
		}
		if($country_of_manufacture){
		$country_of_manufacture = $_product->getCountryOfManufacture();
		}else{
			$country_of_manufacture = 'GB';
		}
		$weight = $data['weight'];
		$total_weight += ($weight * $qty_ordered);
		$total_price += ($price * $qty_ordered);
		
		$name = str_replace(['"',"'"], "", $name);
		$json_format_items .= "{\"Quantity\":$qty_ordered,\"Description\":\"$name\",\"Value\":$price,\"Weight\":$weight,\"PackageOccurrence\":1,\"HsCode\":\"$hscode\",\"SkuCode\":\"$sku\",\"CountryOfOrigin\":\"$country_of_manufacture\"},";
		}
		$json_format_items = rtrim($json_format_items,',');
		$json_format_items .= ']';
		$json_format_items_format = $json_format_items;
		
	  $postcode = $shippingAddress['postcode'];
	  $city = $shippingAddress['city'];
	  $email = $shippingAddress['email'];
	  $telephone = $shippingAddress['telephone'];
	  
	  if($package_weight == ''){
		  $package_weight = $total_weight;
	  }
	  
	  $country_id = $shippingAddress['country_id'];
	  $firstname = $shippingAddress['firstname'].' '.$shippingAddress['lastname'];
	  $lastname = $shippingAddress['lastname'];
	  $street = $shippingAddress['street'];
	  $region = $shippingAddress['region'];
	  $company = $shippingAddress['company'];
	  $VatNumber = $shippingAddress['vat_id'];
	  
	  $shipper_company = $this->helper->getSystemConfigValue('carriers/smroyalmail/smroyalmail_shipper_details/shipper_company');
	  $shipper_name = $this->helper->getSystemConfigValue('carriers/smroyalmail/smroyalmail_shipper_details/shipper_name');
	  $shipper_addr1 = $this->helper->getSystemConfigValue('carriers/smroyalmail/smroyalmail_shipper_details/shipper_addr1');
	  $shipper_addr2 = $this->helper->getSystemConfigValue('carriers/smroyalmail/smroyalmail_shipper_details/shipper_addr2');
	  $shipper_addr3 = $this->helper->getSystemConfigValue('carriers/smroyalmail/smroyalmail_shipper_details/shipper_addr3');
	  $shipper_city = $this->helper->getSystemConfigValue('carriers/smroyalmail/smroyalmail_shipper_details/shipper_city');
	  $shipper_region = $this->helper->getSystemConfigValue('carriers/smroyalmail/smroyalmail_shipper_details/shipper_region');
	  $shipper_postcode = $this->helper->getSystemConfigValue('carriers/smroyalmail/smroyalmail_shipper_details/shipper_postcode');
	  $shipper_phone = $this->helper->getSystemConfigValue('carriers/smroyalmail/smroyalmail_shipper_details/shipper_phone');
	  $shipper_email = $this->helper->getSystemConfigValue('carriers/smroyalmail/smroyalmail_shipper_details/shipper_email');
	  $shipper_country = $this->helper->getSystemConfigValue('carriers/smroyalmail/smroyalmail_shipper_details/shipper_country');
	  
	  $shipper_reference = $this->helper->getSystemConfigValue('carriers/smroyalmail/smroyalmail_shipper_details/shipper_reference');
	  $shipper_department = $this->helper->getSystemConfigValue('carriers/smroyalmail/smroyalmail_shipper_details/shipper_department');
	  $shipper_vatnumber = $this->helper->getSystemConfigValue('carriers/smroyalmail/smroyalmail_shipper_details/shipper_vatnumber');
	  $incoterms = $this->helper->getSystemConfigValue('carriers/smroyalmail/smroyalmail_shipper_details/incoterms');
	  $debug = $this->helper->getSystemConfigValue('carriers/smroyalmail/rm_debug');
	  
	   $signed = '';
	  if($signed_for == 1){
		 $label = "Signed"; 
		 $signed = "{\"Code\":\"$label\"}";
		 
	  }
	  
	    if($price == 0){
		 $item_format = "";
	  }else{
		  $item_format = "\"Items\":$json_format_items_format";
	  }
	  
	  if($country_id != 'GB'){
	
	$item_format = "\"Items\":$json_format_items_format";
	  /* For international shipment only */
	  $data = "{\"ShipmentInformation\":{\"ContentType\":\"$product_type\",\"Action\":\"Process\",\"LabelFormat\":\"PDF\",\"ServiceCode\":\"$service_offering\",
	  \"DescriptionOfGoods\":\"$descriptionofgoods\",\"ShipmentDate\":\"$date\",\"CurrencyCode\":\"$currency_code\",\"WeightUnitOfMeasure\":\"$weight_of_unit\",
	  \"DimensionsUnitOfMeasure\":\"$dimention_unit_of_measure\"},\"Shipper\":{\"Address\":{\"ContactName\":\"$shipper_name\",\"CompanyName\":\"$shipper_company\",\"ContactEmail\":\"$shipper_email\",
	  \"ContactPhone\":\"$shipper_phone\",\"Line1\":\"$shipper_addr1\",\"Line2\":\"$shipper_addr2\",\"Line3\":\"$shipper_addr3\",\"Town\":\"$shipper_city\",
	  \"Postcode\":\"$shipper_postcode\",\"County\":\"$shipper_region\",\"CountryCode\":\"$shipper_country\"},\"ShippingAccountId\":\"$shipping_account_id\",
	  \"ShippingLocationId\":\"$shipping_location_id\",\"Reference1\":\"$increment_id\",\"DepartmentNumber\":\"$shipper_department\",\"EoriNumber\":\"$eori_number\",
	  \"VatNumber\":\"$shipper_vatnumber\"},\"Destination\":{\"Address\":{\"ContactName\":\"$firstname\",\"ContactEmail\":\"$email\",\"ContactPhone\":\"$telephone\",
	  \"Line1\":\"$first_addr_line\",\"Town\":\"$city\",\"Postcode\":\"$postcode\",\"County\":\"$region\",\"CountryCode\":\"$country_id\"},\"EoriNumber\":\"$eori_number\",
	  \"VatNumber\":\"$VatNumber\"},\"CarrierSpecifics\":{\"ServiceEnhancements\":[{\"Code\":\"CustomsEmail\"},{\"Code\":\"CustomsPhone\"}],\"ServiceLevel\":\"\",\"EbayVtn\":\"\"},
	  \"Packages\":[{\"Dimensions\":{\"Length\":$package_length,\"Width\":$package_width,\"Height\":$package_height},\"PackageType\":\"$parcel_type\",\"PackageOccurrence\":1,
	  \"DeclaredWeight\":$package_weight}],$item_format,\"Customs\":{\"ReasonForExport\":\"$reasonforexport\",\"Incoterms\":\"$incoterms\",\"ShippingCharges\":$shippingAmount,
	  \"ExportLicenceRequired\":$exportlicence,\"Airn\":\"$addresseeidentificationreferencenumber\"},\"ReturnToSender\":{\"Address\":{\"ContactName\":\"$shipper_name\",
	  \"CompanyName\":\"$shipper_company\",\"ContactEmail\":\"$shipper_email\",\"ContactPhone\":\"$shipper_phone\",\"Line1\":\"$shipper_addr1\",\"Line2\":\"$shipper_addr2\",
	  \"Line3\":\"$shipper_addr3\",\"Town\":\"$shipper_city\",\"Postcode\":\"$shipper_postcode\",\"County\":\"$shipper_region\",\"CountryCode\":\"$shipper_country\"}}}";	   
	  
		}else{
			/* For domestic shipment only */
	  $data = "{\"ShipmentInformation\":{\"ContentType\":\"$product_type\",\"Action\":\"Process\",\"LabelFormat\":\"PDF\",\"ServiceCode\":\"$service_offering\",\"DescriptionOfGoods\":\"$descriptionofgoods\",\"ShipmentDate\":\"$date\",\"CurrencyCode\":\"$currency_code\",\"WeightUnitOfMeasure\":\"$weight_of_unit\",
	  \"DimensionsUnitOfMeasure\":\"$dimention_unit_of_measure\"},\"Shipper\":{\"Address\":{\"ContactName\":\"$shipper_name\",\"CompanyName\":\"$shipper_company\",\"ContactEmail\":\"$shipper_email\",\"ContactPhone\":\"$shipper_phone\",\"Line1\":\"$shipper_addr1\",\"Line2\":\"$shipper_addr2\",\"Line3\":\"$shipper_addr3\",\"Town\":\"$shipper_city\",
	  \"Postcode\":\"$shipper_postcode\",\"County\":\"$shipper_region\",\"CountryCode\":\"$shipper_country\"},\"ShippingAccountId\":\"$shipping_account_id\",\"ShippingLocationId\":\"$shipping_location_id\",\"Reference1\":\"$increment_id\",\"DepartmentNumber\":\"$shipper_department\",\"EoriNumber\":\"$eori_number\",\"VatNumber\":\"$shipper_vatnumber\"},
	  \"Destination\":{\"Address\":{\"ContactName\":\"$firstname\",\"ContactEmail\":\"$email\",\"ContactPhone\":\"$telephone\",\"Line1\":\"$first_addr_line\",\"Line2\":\"$second_addr_line\",\"Line3\":\"$third_addr_line\",
	  \"Town\":\"$city\",\"Postcode\":\"$postcode\",\"CountryCode\":\"$country_id\"},\"EoriNumber\":\"$eori_number\",\"VatNumber\":\"$VatNumber\"},\"CarrierSpecifics\":{\"ServiceEnhancements\":[$signed],\"ServiceLevel\":\"\",\"EbayVtn\":\"\"},
	  \"Packages\":[{\"Dimensions\":{\"Length\":$package_length,\"Width\":$package_width,\"Height\":$package_height},\"PackageType\":\"$parcel_type\",\"PackageOccurrence\":1,\"DeclaredWeight\":$package_weight}],$item_format},
	  \"ReturnToSender\":{\"Address\":{\"ContactName\":\"$shipper_name\",\"CompanyName\":\"$shipper_company\",\"ContactEmail\":\"$shipper_email\",\"ContactPhone\":\"$shipper_phone\",\"Line1\":\"$shipper_addr1\",\"Line2\":\"$shipper_addr2\",\"Line3\":\"$shipper_addr3\",\"Town\":\"$shipper_city\",\"Postcode\":\"$shipper_postcode\",\"County\":\"$shipper_region\",\"CountryCode\":\"$shipper_country\"}}}";
		}
		
		if($debug == 1){
		$writer = new \Zend_Log_Writer_Stream(BP . '/var/log/RoyalmailRequest.log');
		$logger = new \Zend_Log();
		$logger->addWriter($writer);
		$logger->info('text message');
		$logger->info(print_r($data, true));
		}		
		
	$URL = "$gateway_url/shipments/rm";
	 
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
	  $this->curl->post($URL, $data);
	  //read response
	  $response = $this->curl->getBody();
	  $result = json_decode($response, true);
	  //$data = json_decode($response, TRUE);
	  
	if($debug == 1){
	$this->SaveRoyalmailLog($increment_id,$shipmentId,$response);
	}
	if(isset($result['Packages'])){
		$pdf_data = $result['Labels'];
	foreach($result['Packages'] as $track_data){
		if(isset($track_data['TrackingNumber'])){
		$tracking_number = $track_data['TrackingNumber'];
		$rm_shipment_id = $track_data['ShipmentId'];
		}else{
			$tracking_number = $track_data['CarrierDetails']['UniqueId'];
		}
		$this->tracking->addCustomTrack($shipmentId,$tracking_number);
	}
	
	if($tracking_number && $massaction == ''){
	$this->messageManager->addSuccessMessage('#' . $shipmentId . '-' . __('Shipping created successfully.'));
	$this->messageManager->addSuccessMessage('Tracking #' . $tracking_number . '-' . __('successfully generated.'));
	$success_msg = "Shipping created successfully";
	}
	
	$file_name = "Label_".$tracking_number."_0";
	$label_img = $this->saveFile($pdf_data, 'pdf', $file_name,$shipmentId,$orderId,$tracking_number,0,$rm_shipment_id);
	if(isset($result['Documents'])){
		
		$pdf_data = $result['Documents'];
		$file_name = "CustomsDocuments_Label_".$tracking_number."_0";
		$CustomsDocuments_Label = $this->saveFile($pdf_data, 'pdf', $file_name,$shipmentId,$orderId,$tracking_number,1,$rm_shipment_id);
		if($labels_merge_flag == 1){
		$this->mergePdf($label_img,$CustomsDocuments_Label,$shipmentId,$orderId,$tracking_number,2);
		}
	}
	
	}
	if(isset($result['Errors'][0]['Message'])){
		$msg = $result['Errors'][0]['Message'];
		$this->messageManager->addErrorMessage($msg);
	}
		}
		catch (\Exception $e) {
			echo $e->getMessage();
        $this->messageManager->addErrorMessage($e->getMessage());
    }
	if($msg){
		$response = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)->setData(['status'  => "error",'message' => $msg]);
		$orderId = $order->getId();
		$status = 'shipping_label_generate_error';
		$this->updateOrderStatus($orderId,$status);
	}else{
		$response = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)->setData(['status'  => "ok",'message' => "Shipping created successfully"]);
		if($order->getStatus() == 'shipping_label_generate_error'){
			$orderId = $order->getId();
			$status = $order->getState();
			$this->updateOrderStatus($orderId,$status);
		}
	}	
	return $response;
	
}

	public function updateOrderStatus($orderId,$status){
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$orderRepository = $objectManager->create('\Magento\Sales\Api\OrderRepositoryInterface');
		$order = $orderRepository->get($orderId);
		$order->setStatus($status);
		$orderRepository->save($order);
	}

	public function SaveRoyalmailLog($increment_id,$shipmentId,$response){
		$result = json_decode($response, true);
		$writer = new \Zend_Log_Writer_Stream(BP . '/var/log/RoyalmailResponse.log');
		$logger = new \Zend_Log();
		$logger->addWriter($writer);
		$logger->info('Shipment ID #'.$shipmentId);
		$logger->info('Order Increment ID #'.$increment_id);
		$logger->info(print_r($result, true));
	}
	

	public function saveFile($data, $format, $name,$shipmentId,$orderId,$tracking_number,$international_flag,$rm_shipment_id)
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
			if($fileName){
				$prepare_array = array("createShipment","printLabel");
				foreach($prepare_array as $request_type){
				$this->saveInTransaction($file_name_prepare,$orderId,$shipmentId,$tracking_number,$request_type,$international_flag,$rm_shipment_id);
				$this->saveInSalesShipment($data,$shipmentId,$rm_shipment_id);
			}
		}
		//echo BP. DIRECTORY_SEPARATOR .$fileName;
		//exit;
		
			return str_replace($mediaPath, '', $fileName);
		}
		
		public function mergePdf($label_img,$CustomsDocuments_Label,$shipmentId,$orderId,$tracking_number,$international_flag){
			$label_img = BP. DIRECTORY_SEPARATOR .'\media'.$label_img;
			$CustomsDocuments_Label = BP. DIRECTORY_SEPARATOR .'media'.$CustomsDocuments_Label;
			$mediaPath = $this->directoryList->getUrlPath(DirectoryList::MEDIA);
			$name = "CN23";
			$file_path_prepare = BP . DIRECTORY_SEPARATOR . $mediaPath . DIRECTORY_SEPARATOR . self::LABEL_DIR_NAME . DIRECTORY_SEPARATOR . date('Y') . DIRECTORY_SEPARATOR . date('Y-m-d') . DIRECTORY_SEPARATOR . $name . '_' . date('H-i-s') . '.pdf';
			$file_name_prepare = '/' . self::LABEL_DIR_NAME . '/' . date('Y') . '/' . date('Y-m-d') . '/' . $name . '_' . date('H-i-s').'.pdf';
			/*$pdf = new PDFMerger();
			$pdf->addPDF($label_img, 'all')
			->addPDF($CustomsDocuments_Label, 'all')
			->merge('file', $file_path_prepare);*/
			
			if($file_path_prepare){
			$prepare_array = array("createShipment","printLabel");
				foreach($prepare_array as $request_type){
				$this->saveInTransaction($file_name_prepare,$orderId,$shipmentId,$tracking_number,$request_type,$international_flag);
			}
		}
			
	}
	
	public function saveInSalesShipment($data,$shipmentId,$rm_shipment_id){
			$shipmentCollection = $this->objectManager->create('Magento\Sales\Model\Order\Shipment');
			$shipment = $shipmentCollection->load($shipmentId);
			$shipment->setShippingLabel($data);
			$shipment->save();
				
		}
		
		public function saveInTransaction($file_name_prepare,$orderId,$shipmentId,$tracking_number,$request_type,$international_flag,$rm_shipment_id){
			$shipmentCollection = $this->objectManager->create('Magento\Sales\Model\Order\Shipment');
			$order = $this->order->load($orderId);
			$shipment = $shipmentCollection->load($shipmentId);
			$order_increment_id = $order->getIncrementId();
			$shipment_increment_id = $shipment->getIncrementId();
			$current_time = date("Y-m-d h:i:s");
			$transaction = $this->transactionFactory->create();
				$transaction->setShipmentId($shipmentId);
				$transaction->setOrderId($orderId);
				$transaction->setRequestType($request_type);
				$transaction->setStatus('Printed');
				$transaction->setShipmentNumber($tracking_number);
				$transaction->setLabelFile($file_name_prepare);
				$transaction->setCreatedAt($current_time);
				$transaction->setUpdatedAt($current_time);
				$transaction->setCustomForm($international_flag);
				$transaction->setOrderIncrementId($order_increment_id);
				$transaction->setShipmentIncrementId($shipment_increment_id);
				$transaction->setRoyalmailShipmentId($rm_shipment_id);
				$transaction->save();
				
		}
	}
