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

namespace StreamMarket\RoyalMailShipping\Model\Config\Source\Locale;

/**
 * Description of Country
 */
class Country extends \Magento\Config\Model\Config\Source\Locale\Country
{

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return array_merge(array(array('value' => 0, 'label' => 'All Countries')),
                $this->_localeLists->getOptionCountries());
    }

}
