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

namespace StreamMarket\RoyalMailShipping\Block\Adminhtml\Rate\Edit;

/**
 * Description of MatrixData
 */
class MatrixData extends \Magento\Backend\Block\Template
{

    /**
     * @var \StreamMarket\RoyalMailShipping\Model\Service\MatrixFactory
     */
    private $serviceMatrixFactory;

    private $_options = null;

    public function __construct(\Magento\Backend\Block\Template\Context $context,
            \StreamMarket\RoyalMailShipping\Model\Service\MatrixFactory $serviceMatrixFactory,
            array $data = array())
    {
        parent::__construct($context, $data);
        $this->serviceMatrixFactory = $serviceMatrixFactory;
    }

    public function getOptions()
    {
        if (!is_null($this->_options)) {
            return $this->_options;
        }
        $offeringsCodes = [];
        $enabledMethodes = $this->_scopeConfig->getValue(
                'carriers/' . \StreamMarket\RoyalMailShipping\Model\Carrier::CODE . '/allowed_methods',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $this->_storeManager->getStore()
        );
        if ($enabledMethodes) {
            $offeringsCodes = explode(',', $enabledMethodes);
        }
        $serviceMatrix = $this->serviceMatrixFactory->create();
        $serviceTypeCodes = $serviceMatrix->getServiceTypes($offeringsCodes);

        $this->_options = $serviceMatrix->getServiceOfferingsByServiceTypes(array_values($serviceTypeCodes),
                $offeringsCodes);

        return $this->_options;
    }

    public function _toHtml()
    {
        if ($this->getOptions()) {

            return parent::_toHtml() . "<div id='sm_service_matrix' data='".json_encode($this->getOptions())."'></div>";
        } else {
            return parent::_toHtml();
        }
    }

}
