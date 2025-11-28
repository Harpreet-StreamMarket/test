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

namespace StreamMarket\RoyalMailShipping\Model\Config\Source;

/**
 * Description of AllowedCountry
 */
class ParcelType implements \Magento\Framework\Data\OptionSourceInterface
{

    public function toOptionArray()
    {
        return [['value' => 'P', 'label' => 'Parcel'],
		['value' => 'L', 'label' => 'Letter'],
		['value' => 'F', 'label' => 'Large Letter'],
		['value' => 'S', 'label' => 'Printed Papers - International Services Only']
		];
    }

}
