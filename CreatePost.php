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

namespace StreamMarket\RoyalMailShipping\Controller\Adminhtml\Manifest;
use Magento\Framework\HTTP\Client\Curl;
/**
 * CreatePost Action
 */
class CreatePost extends \Magento\Backend\App\Action
{

    /**
     * @var \StreamMarket\RoyalMailShipping\Model\CarrierFactory
     */
    private $carrierFactory;
	
	protected $curl;

    public function __construct(
	\Magento\Backend\App\Action\Context $context,
    \StreamMarket\RoyalMailShipping\Model\CarrierFactory $carrierFactory,
	Curl $curl
			)
    {
        parent::__construct($context);
        $this->carrierFactory = $carrierFactory;
		$this->curl = $curl;
    }

    public function execute()
    {
		
		
        $URL = 'https://api.royalmail.net/shipping/v3/manifests/byservice';
		  $jsonData = "[{\"ShipmentId\":\"$tracking_number\",\"ReasonForCancellation\":\"OrderCancelled\"}]";
		 
		  //set curl options
		  $this->curl->setOption(CURLOPT_HEADER, 0);
		  $this->curl->setOption(CURLOPT_TIMEOUT, 60);
		  $this->curl->setOption(CURLOPT_RETURNTRANSFER, true);
		  $this->curl->setOption(CURLOPT_CUSTOMREQUEST, "POST");
		  
		  //set curl header
		  $this->curl->addHeader("Content-Type", "application/json");
		  $this->curl->addHeader("accept", "application/json");
		  $this->curl->addHeader("X-IBM-Client-Id", "d62ae68b-1797-4c84-a6d4-e80d87cf9993");
		  $this->curl->addHeader("X-RMG-Auth-Token", "eyJraWQiOiJoczI1Ni1rZXkiLCJhbGciOiJIUzI1NiJ9.eyJleHAiOjE2MzUxNzE2NzIsImlhdCI6MTYzNTE1NzI3MiwidXNlcklkIjoiMDQxODI5MTAwMEFQSSIsInBhc3N3b3JkIjoiamxsMTBSeGE2UmlOSGdBUml1RWc3dE5OSHBNPSJ9.0aL7j8xUREtnBgCSZMS4ZnxlcKi6gs7VSoQSPeq9na0");
		  //post request with url and data
		  $this->curl->post($URL, $jsonData);
		  //read response
		  $response = $this->curl->getBody();
		  $data = json_decode($response, TRUE);
		  return $data;
		
    }

}
