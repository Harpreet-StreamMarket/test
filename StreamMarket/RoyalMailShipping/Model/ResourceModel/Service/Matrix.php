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

namespace StreamMarket\RoyalMailShipping\Model\ResourceModel\Service;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Description of Matrix
 */
class Matrix extends AbstractDb
{
    /*
     * Define main table
     */

    protected function _construct()
    {
        $this->_init('sm_royalmail_service_matrix', 'id');
    }

}
