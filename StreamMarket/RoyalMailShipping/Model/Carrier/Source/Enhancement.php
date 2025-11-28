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
 * Description of Enhancement
 */
class Enhancement extends \StreamMarket\RoyalMailShipping\Model\Carrier\Source\AbstractSource
{
    public function toOptionArray()
    {
        $arr = array();
        foreach ($this->carrierCodes->getCode('enhancement_type_group') as $groupCode => $enTypes) {
            $values = array();
            foreach ($enTypes as $enType):
                $values[] = array('value' => $enType, 'label' => $this->carrierCodes->getCode('enhancement_type', $enType));
            endforeach;
            $arr[] = array('value' => $values, 'label' => $this->carrierCodes->getCode('enhancement_group_description', $groupCode));
        }
        return $arr;
    }
}
