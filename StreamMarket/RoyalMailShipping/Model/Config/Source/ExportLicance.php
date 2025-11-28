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

class ExportLicance implements \Magento\Framework\Data\OptionSourceInterface
{

    public function toOptionArray()
    {
        return [
		['value' => 'false', 'label' => 'False'],
		['value' => 'true', 'label' => 'True']
		];
    }

}
