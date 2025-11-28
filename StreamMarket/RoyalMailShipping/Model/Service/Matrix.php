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

namespace StreamMarket\RoyalMailShipping\Model\Service;

use Magento\Framework\Model\AbstractModel;

/**
 * Description of Matrix
 */
class Matrix extends AbstractModel
{

    /**
     * @var \StreamMarket\RoyalMailShipping\Model\Carrier\Codes
     */
    private $carrierCodes;

    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('StreamMarket\RoyalMailShipping\Model\ResourceModel\Service\Matrix');
    }

    public function __construct(\Magento\Framework\Model\Context $context,
            \Magento\Framework\Registry $registry,
            \StreamMarket\RoyalMailShipping\Model\Carrier\Codes $carrierCodes,
            \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
            \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
            array $data = array())
    {
        parent::__construct($context, $registry, $resource, $resourceCollection,
                $data);
        $this->carrierCodes = $carrierCodes;
    }

    public function getServiceTypeBy($offering, $serviceFormat = null)
    {
        $collection = $this->getCollection()->addFieldToFilter('service_offering',
                $offering);
        if ($serviceFormat != null) {
            $collection->addFieldToFilter('service_format', $serviceFormat);
        }
        $collection->getSelect()->limit(1);
        if ($collection->count()) {
            return $collection->getFirstItem()->getServiceType();
        }
        return null;
    }

    /**
     *
     * @param array $offeringCodes
     * @param string $serviceFormat filter service format
     * @return array service type codes
     */
    public function getServiceTypes($offeringCodes, $serviceFormat = null)
    {
        if (!is_array($offeringCodes) || count($offeringCodes) < 1) {
            return array();
        }
        $collection = $this->getCollection()->addFieldToFilter('service_offering',
                array('in' => $offeringCodes));
        if ($serviceFormat != null) {
            if (is_array($serviceFormat)) {
                $collection->addFieldToFilter('service_format',
                        array('in' => $serviceFormat));
            } else {
                $collection->addFieldToFilter('service_format', $serviceFormat);
            }
        }
        $collection->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS)
                ->columns('service_type');
        $arr = array();
        if ($collection->count()) {
            foreach ($collection as $item):
                $arr[] = $item->getServiceType();
            endforeach;
        }
        return array_unique($arr);
    }

    public function getServiceOfferingsByServiceTypes($serviceTypes,
            $serviceOfferings = [], $containers = [])
    {
        if (!is_array($serviceTypes) || count($serviceTypes) < 1) {
            return array();
        }
        $collection = $this->getCollection()->addFieldToFilter('service_type',
                array('in' => $serviceTypes));
        if (is_array($serviceOfferings) && $serviceOfferings) {
            $collection->addFieldToFilter('service_offering',
                    array('in' => $serviceOfferings));
        }
        if (is_array($containers) && $containers) {
            $collection->addFieldToFilter('service_format',
                    array('in' => $containers));
        }
        $collection->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS)
                ->columns(array('service_type', 'service_offering'));
        $arr = array();
        if ($collection->count()) {
            foreach ($collection as $item):
                $arr[$item->getServiceType()][$item->getServiceOffering()] = $this->carrierCodes->getCode('method',
                        $item->getServiceOffering());
            endforeach;
//            foreach ($arr as $k => $v):
//                $arr[$k] = array_unique($v);
//            endforeach;
        }
        return $arr;
    }

    /**
     * Returns eligible container types for service offering
     * @param type $offering
     * @return type
     */
    public function getServiceFormatByOffering($offering, $serviceType = false)
    {
        $collection = $this->getCollection()->addFieldToFilter('service_offering',
                $offering);
        if ($serviceType) {
            $collection->addFieldToFilter('service_type', $serviceType);
        }
        $formats = array();
        foreach ($collection as $item):
            $formats[] = $item->getServiceFormat();
        endforeach;
        return array_unique($formats);
    }

    public function getEnhancementTypesBy($offering, $serviceFormat = null,
            $serviceTye = null, $signature = null)
    {
        $collection = $this->getCollection()->addFieldToFilter('service_offering',
                $offering);
        if ($serviceFormat != null) {
            $collection->addFieldToFIlter('service_format', $serviceFormat);
        }
        if ($serviceTye != null) {
            $collection->addFieldToFIlter('service_type', $serviceTye);
        }
        if ($signature !== null) {
            $collection->addFieldToFIlter('signature', $signature);
        }
        $enhancements = array();

        foreach ($collection as $item):
            if ($item->getEnhancementType()) {
                $enhancements[] = $item->getEnhancementType();
//                foreach ($groups as $k => $enhs):
//                    if (in_array($item->getEnhancementType(), $enhs)) {//allow only one from one group
//                        unset($groups[$k]);
//                        break;
//                    }
//                endforeach;
            }
        endforeach;
        return array_unique($enhancements);
    }

    /**
     * Returns Offerings that are eligible for signature
     * @return array
     */
    public function getSignatureServiceOfferings()
    {
        $offerngCodes = array();
        $collection = $this->getCollection()->addFieldToFilter('signature', 1);
        foreach ($collection as $item):
            $offerngCodes[] = $item->getServiceOffering();
        endforeach;
        return $offerngCodes;
    }

}
