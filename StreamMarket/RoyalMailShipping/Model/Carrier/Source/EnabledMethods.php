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
 * Description of Methods
 */
class EnabledMethods extends \StreamMarket\RoyalMailShipping\Model\Carrier\Source\AbstractSource
{

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
            \Magento\Store\Model\StoreManagerInterface $storeManager)
    {
        parent::__construct($carrierCodes);
        $this->_storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
    }

    public function getOptions()
    {
        if (!is_null($this->_options)) {
            return $this->_options;
        }
        $this->_options = [];
        $allowed = [];
        $enabledMethodes = $this->scopeConfig->getValue(
                'carriers/' . \StreamMarket\RoyalMailShipping\Model\Carrier::CODE . '/allowed_methods',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $this->_storeManager->getStore()
        );
        if ($enabledMethodes) {
            $allowed = explode(',', $enabledMethodes);
        }
        foreach ($this->carrierCodes->getCode('method') as $k => $v) {
            if (in_array($k, $allowed)) {
                $this->_options[$k] = $v;
            }
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
