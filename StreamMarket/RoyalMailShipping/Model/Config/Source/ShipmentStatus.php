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
class ShipmentStatus implements \Magento\Framework\Data\OptionSourceInterface
{

    public function toOptionArray()
    {
        return [['value' => 'Picked', 'label' => 'Picked'],
		['value' => 'OnHold', 'label' => 'OnHold']
		];
    }

}
