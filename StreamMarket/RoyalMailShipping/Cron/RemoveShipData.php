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
class RemoveShipData
{

    private $removeshipdata;

    const RUN_MANIFEST_AUTOMATICALLY_ENABLED_CONFIG_PATH = 'carriers/smroyalmail/manifest_automatically';
    const MANIFEST_DESCRIPTION_CONFIG_PATH = 'carriers/smroyalmail/manifest_description';
    const MANIFEST_REFERENCE_CONFIG_PATH = 'carriers/smroyalmail/manifest_reference';

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
            \StreamMarket\RoyalMailShipping\Model\RemoveShipData $removeshipdata
			)
    {
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
        $this->removeshipdata = $removeshipdata;
    }

    public function execute()
    {
		$this->removeshipdata->execute();
	}

}
