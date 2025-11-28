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
 * Description of Container
 */
class Container extends \StreamMarket\RoyalMailShipping\Model\Carrier\Source\AbstractSource
{

    public function toOptionArray()
    {
        $arr = array();
        foreach ($this->carrierCodes->getCode('container_description') as $k => $v) {
            $arr[] = array('value' => $k, 'label' => $v);
        }
        return $arr;
    }

}
