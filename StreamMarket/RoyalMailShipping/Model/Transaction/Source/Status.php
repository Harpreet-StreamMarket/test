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
 * Description of Status
 */
class Status implements \Magento\Framework\Data\OptionSourceInterface
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
            \StreamMarket\RoyalMailShipping\Helper\Data::STATUS_ALLOCATED => __('Allocated'),
            \StreamMarket\RoyalMailShipping\Helper\Data::STATUS_PRINTED => __('Printed'),
			\StreamMarket\RoyalMailShipping\Helper\Data::STATUS_HOLD => __('Hold'),
			\StreamMarket\RoyalMailShipping\Helper\Data::STATUS_PICKED => __('Picked'),
			\StreamMarket\RoyalMailShipping\Helper\Data::STATUS_RELEASE => __('Release'),
            \StreamMarket\RoyalMailShipping\Helper\Data::STATUS_CANCELLED => __('Cancelled'),
            \StreamMarket\RoyalMailShipping\Helper\Data::STATUS_MANIFESTED => __('Manifested'),
            \StreamMarket\RoyalMailShipping\Helper\Data::STATUS_MANIFESTED_PRINTED => __('Manifest Printed'),
        ];
    }

}
