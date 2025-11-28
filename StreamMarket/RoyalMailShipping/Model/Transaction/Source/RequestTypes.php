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

namespace StreamMarket\RoyalMailShipping\Model\Transaction\Source;

/**
 * Description of RequestTypes
 */
class RequestTypes implements \Magento\Framework\Data\OptionSourceInterface
{

    public function toOptionArray()
    {
        $options[] = ['label' => '', 'value' => ''];
        $availableOptions = $this->getOptionArray();
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }

    public static function getOptionArray()
    {
        return [
            \StreamMarket\RoyalMailShipping\Helper\Data::REQUEST_TYPE_CREATE_SHIPMENT => __('Create Shipment'),
            \StreamMarket\RoyalMailShipping\Helper\Data::REQUEST_TYPE_PRINT_LABEL => __('Print Label'),
            \StreamMarket\RoyalMailShipping\Helper\Data::REQUEST_TYPE_CANCEL_SHIPMENT => __('Cancel Shipment'),
            \StreamMarket\RoyalMailShipping\Helper\Data::REQUEST_TYPE_CREATE_MANIFEST => __('Create Manifest'),
            \StreamMarket\RoyalMailShipping\Helper\Data::REQUEST_TYPE_PRINT_MANIFEST => __('Print Manifest'),
        ];
    }

}