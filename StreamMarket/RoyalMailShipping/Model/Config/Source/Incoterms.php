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
class Incoterms implements \Magento\Framework\Data\OptionSourceInterface
{

    public function toOptionArray()
    {
        return [['value' => 'DDU', 'label' => 'DDU'],
		['value' => 'DDP', 'label' => 'DDP'],
		['value' => 'DAP', 'label' => 'DAP'],
		['value' => 'DAT', 'label' => 'DAT']
		];
    }

}
