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

namespace StreamMarket\RoyalMailShipping\Model\Carrier\Source;

/**
 * Description of ServiceTypes
 */
class EnabledServiceTypes extends \StreamMarket\RoyalMailShipping\Model\Carrier\Source\AbstractSource
{

    /**
     * @var \StreamMarket\RoyalMailShipping\Model\Service\MatrixFactory
     */
    private $serviceMatrixFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $_storeManager;
    private $_options = null;

    public function __construct(\StreamMarket\RoyalMailShipping\Model\Carrier\Codes $carrierCodes,
            \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
            \Magento\Store\Model\StoreManagerInterface $storeManager,
            \StreamMarket\RoyalMailShipping\Model\Service\MatrixFactory $serviceMatrixFactory)
    {
        parent::__construct($carrierCodes);
        $this->_storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->serviceMatrixFactory = $serviceMatrixFactory;
    }

    public function getOptions()
    {
        if (!is_null($this->_options)) {
            return $this->_options;
        }
        $offeringsCodes = [];
        $enabledMethodes = $this->scopeConfig->getValue(
                'carriers/' . \StreamMarket\RoyalMailShipping\Model\Carrier::CODE . '/allowed_methods',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $this->_storeManager->getStore()
        );
        if ($enabledMethodes) {
            $offeringsCodes = explode(',', $enabledMethodes);
        }
        $serviceTypeCodes = $this->serviceMatrixFactory->create()->getServiceTypes($offeringsCodes);
        $this->_options = [];
        if ($serviceTypeCodes) {
            foreach ($serviceTypeCodes as $k => $v):
                $this->_options[$v] = $this->carrierCodes->getCode('service_type',
                        $v);
            endforeach;
        }
        return $this->_options;
    }

    public function toOptionArray()
    {
        $arr = array();
        foreach ($this->getOptions() as $k => $v) {
            $arr[] = array('value' => $k, 'label' => $v);
        }
        return $arr;
    }

}
