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
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\HTTP\Client\Curl;

class GetContainerType extends \Magento\Backend\App\Action
{
	protected $shipmentFactory;
	private $helper;
	protected $curl;
	protected $resultPageFactory;
	
	const XML_PATH_GATEWAY_URL = 'carriers/smroyalmail/gateway_url';
	
    public function __construct(
    \Magento\Backend\App\Action\Context $context,
    \Magento\Framework\View\Result\PageFactory $resultPageFactory,
	\StreamMarket\RoyalMailShipping\Helper\CurlRequest $helper,
	Curl $curl
	)
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
		$this->helper = $helper;
		$this->curl = $curl;
    }

    public function execute()
    {
		
		$auth_token = $this->helper->generateToken();
        $data = $this->getRequest()->getParams();
		$service_offering = $data['code'];
		$html = "";
		if($service_offering){
		$gateway_url = $this->helper->getSystemConfigValue(self::XML_PATH_GATEWAY_URL);
		$URL = "$gateway_url/carriers/RM/services/$service_offering/packageTypes";
		 
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
		  $array_reverse = array_reverse($data);
		  $html = '';
		  foreach($array_reverse as $val){
			$code = $val['PackageTypeCode'];
			if($code == 'NotApplicable'){
				$html = $this->getParcelTypes();
			}else{
				$label = $val['Description'];
				$html .= "<option value='$code'>$label</option>";
			}
		  }
		}  
		$response = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)->setData(['status'  => "success",'html' => $html]);
		return $response;
	 }
	 
	 public function getSystemConfigValue($system_code){
			$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
			return $this->scopeConfig->getValue($system_code, $storeScope);
	 }
	 
	 public function getParcelTypes(){
		$html = "<option value='Parcel'>Parcel</option><option value='Letter'>Letter</option><option value='LargeLetter'>Large Letter</option><option value='PrintedPapers'>Printed Papers</option>";
		return $html;
	 }
} 
