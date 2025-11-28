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

namespace StreamMarket\RoyalMailShipping\Cron;

use \Psr\Log\LoggerInterface;
use \Magento\Store\Model\ScopeInterface;

/**
 * Description of EODProcess
 */
class TokenGenerate
{

    private $CurlRequest;
	private $_objectManager;

    const RM_TOKEN_GENERATE = 'carriers/smroyalmail/rm_token';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger,
            \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
            \StreamMarket\RoyalMailShipping\Helper\CurlRequest $CurlRequest,
			 \Magento\Framework\ObjectManagerInterface $objectmanager
			)
    {
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
        $this->curlrequest = $CurlRequest;
		$this->_objectManager = $objectmanager;
    }

    public function execute()
    {
		$cron_run_time = $this->getSystemConfigValue(self::RM_TOKEN_GENERATE);
		$resource = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
		$connection = $resource->getConnection();
		$current_date = date("Y-m-d h:i:s");
		$sql = "Select * FROM sm_royalmail_token";
		$result = $connection->fetchAll($sql); // gives associated array, table fields as key in array.
		$token_count = count($result);
		if($token_count == 0){
			$token_value = $this->curlrequest->generateRMToken();
			$sql = "Insert Into sm_royalmail_token (token_value, created_at, updated_at, cron_status) Values ('$token_value','$current_date','$current_date',1)";
			$connection->query($sql);
		}else{
			foreach($result as $val){
				$id = $val['id'];
				$updated_at = $val['updated_at'];
			}
			
			$future_date = date('Y-m-d H:i:s', strtotime($updated_at.'+'.$cron_run_time.'hours'));
			$future_cron_run_time = strtotime($future_date);
			$current_date_str = strtotime(date("Y-m-d h:i:s"));
			if($current_date_str >= $future_cron_run_time){
				$token_value = $this->curlrequest->generateRMToken();
				$sql = "Update sm_royalmail_token Set token_value = '$token_value', updated_at = '$current_date' where id = $id";
				$connection->query($sql);
			}
		}
	}
	
	public function getSystemConfigValue($system_code){
			$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
			return $this->scopeConfig->getValue($system_code, $storeScope);
		}

}
