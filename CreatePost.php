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
use StreamMarket\RoyalMailShipping\Helper\Data as HelperData;
/**
 * CreatePost Action
 */
class CreatePost extends \Magento\Backend\App\Action
{
	
	const XML_PATH_CLIENT_ID = 'carriers/smroyalmail/client_id';
	
    protected $helperData;
	protected $scopeConfig;

    private $carrierFactory;
	
	protected $curl;

    public function __construct(
	\Magento\Backend\App\Action\Context $context,
    \StreamMarket\RoyalMailShipping\Model\CarrierFactory $carrierFactory,
	Curl $curl,
	HelperData $helperData,
	\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
			)
    {
        parent::__construct($context);
        $this->carrierFactory = $carrierFactory;
		$this->curl = $curl;
		$this->helperData = $helperData;
		$this->scopeConfig = $scopeConfig;
    }

    public function execute()
    {
		$token     = $this->helperData->generateToken();
		$clientId  = $this->getSystemConfigValue(self::XML_PATH_CLIENT_ID);

		$url = 'https://api.royalmail.net/shipping/v3/shipments/cancel';

		// Payload (use json_encode instead of manual string building)
		$payload = [
			[
				"ShipmentId"            => $tracking_number,
				"ReasonForCancellation" => "OrderCancelled"
			]
		];

		$jsonData = json_encode($payload);

		// Set headers
		$this->curl->addHeader("Content-Type", "application/json");
		$this->curl->addHeader("Accept", "application/json");
		$this->curl->addHeader("X-IBM-Client-Id", $clientId);
		$this->curl->addHeader("X-RMG-Auth-Token", $token);

		// Timeout and return behavior
		$this->curl->setOption(CURLOPT_TIMEOUT, 60);
		$this->curl->setOption(CURLOPT_RETURNTRANSFER, true);

		// POST request
		$this->curl->post($url, $jsonData);

		// Response
		$response = $this->curl->getBody();
		$data = json_decode($response, true);

		// Error handling
		if (json_last_error() !== JSON_ERROR_NONE) {
			$this->logger->error('Royal Mail API invalid JSON response: ' . $response);
			return [
				'error' => true,
				'message' => 'Invalid JSON response received',
				'raw' => $response
			];
		}
		return $data;
    }
}
