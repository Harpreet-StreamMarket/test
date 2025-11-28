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
class EODProcess
{

    /**
     * @var \StreamMarket\RoyalMailShipping\Helper\Data
     */
    private $helper;

    /**
     * @var \StreamMarket\RoyalMailShipping\Model\CarrierFactory
     */
    private $carrierFactory;

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
            \StreamMarket\RoyalMailShipping\Model\CarrierFactory $carrierFactory,
            \StreamMarket\RoyalMailShipping\Helper\CurlRequest $helper)
    {
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
        $this->carrierFactory = $carrierFactory;
        $this->helper = $helper;
    }

    public function execute()
    {
		$this->helper->massManifestCreate();
	}

}
