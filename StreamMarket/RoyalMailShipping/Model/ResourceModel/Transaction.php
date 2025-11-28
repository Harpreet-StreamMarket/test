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

namespace StreamMarket\RoyalMailShipping\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Description of Transaction
 */
class Transaction extends AbstractDb
{
    /*
     * Define main table
     */

    protected function _construct()
    {
        $this->_init('sm_royalmail_transactions', 'id');
    }

}
