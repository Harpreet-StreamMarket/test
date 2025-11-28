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
 * Description of DimentionUnitOfMeasure
 */
class DimentionUnitOfMeasure extends \StreamMarket\RoyalMailShipping\Model\Carrier\Source\AbstractSource
{

    public function toOptionArray()
    {
        return [
				['value' => 'CM', 'label' => __('CM')],
				['value' => 'MM', 'label' => __('MM')],
			];
    }
}
