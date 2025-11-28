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
class MenifestAvailable implements \Magento\Framework\Data\OptionSourceInterface
{

    public function toOptionArray()
    {
        return [['value' => 'shipping_account', 'label' => 'By Shipping Account'],
		['value' => 'shipping_location', 'label' => 'By Shipping Location'],
		['value' => 'shipping_status', 'label' => 'By Shipment Status'],
		['value' => 'service_code', 'label' => 'By Service Code'],
		['value' => 'container', 'label' => 'By Container'],
		];
    }

}
