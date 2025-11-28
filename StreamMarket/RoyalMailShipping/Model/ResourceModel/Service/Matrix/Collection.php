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

namespace StreamMarket\RoyalMailShipping\Model\ResourceModel\Service\Matrix;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Description of Collection
 */
class Collection extends AbstractCollection
{

    protected function _construct()
    {
        $this->_init('StreamMarket\RoyalMailShipping\Model\Service\Matrix',
                'StreamMarket\RoyalMailShipping\Model\ResourceModel\Service\Matrix');
    }

}
