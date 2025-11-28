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

namespace StreamMarket\RoyalMailShipping\Model\ResourceModel\Carrier;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Description of Rate
 */
class Rate extends AbstractDb
{
    /*
     * Define main table
     */

    protected function _construct()
    {
        $this->_init('sm_royalmail_matrixrate', 'pk');
    }

    public function getNewRate(\Magento\Framework\DataObject $request,
            $zipRangeSet = 0, $allowedMethods = array())
    {
        if (!count($allowedMethods)) {
            return array();
        }
        $connection = $this->getConnection();
        $postcode = $request->getDestPostcode();
        $table = $this->_resources->getTableName('sm_royalmail_matrixrate');
        if ($zipRangeSet && is_numeric($postcode)) {
            $zipSearchString = ' AND ' . $postcode . ' BETWEEN dest_zip AND dest_zip_to )';
        } else {
            $zipSearchString = $connection->quoteInto(" AND ? LIKE dest_zip )",
                    $postcode);
        }
        for ($j = 0; $j < 10; $j++) {
            $select = $connection->select()->from($table);
            switch ($j) {
                case 0:
                    $select->where(
                            $connection->quoteInto(" (dest_country_id=? ",
                                    $request->getDestCountryId()) .
                            $connection->quoteInto(" AND dest_region_id=? ",
                                    $request->getDestRegionId()) .
                            $connection->quoteInto(" AND STRCMP(LOWER(dest_city),LOWER(?)) = 0  ",
                                    $request->getDestCity()) .
                            $zipSearchString
                    );
                    break;
                case 1:
                    $select->where(
                            $connection->quoteInto(" (dest_country_id=? ",
                                    $request->getDestCountryId()) .
                            $connection->quoteInto(" AND dest_region_id=?  AND dest_city=''",
                                    $request->getDestRegionId()) .
                            $zipSearchString
                    );
                    break;
                case 2:
                    $select->where(
                            $connection->quoteInto(" (dest_country_id=? ",
                                    $request->getDestCountryId()) .
                            $connection->quoteInto(" AND dest_region_id=? ",
                                    $request->getDestRegionId()) .
                            $connection->quoteInto(" AND STRCMP(LOWER(dest_city),LOWER(?)) = 0  AND dest_zip='')",
                                    $request->getDestCity())
                    );
                    break;
                case 3:
                    $select->where(
                            $connection->quoteInto("  (dest_country_id=? ",
                                    $request->getDestCountryId()) .
                            $connection->quoteInto(" AND STRCMP(LOWER(dest_city),LOWER(?)) = 0  AND dest_region_id='0'",
                                    $request->getDestCity()) .
                            $zipSearchString
                    );
                    break;
                case 4:
                    $select->where(
                            $connection->quoteInto("  (dest_country_id=? ",
                                    $request->getDestCountryId()) .
                            $connection->quoteInto(" AND STRCMP(LOWER(dest_city),LOWER(?)) = 0  AND dest_region_id='0' AND dest_zip='') ",
                                    $request->getDestCity())
                    );
                    break;
                case 5:
                    $select->where(
                            $connection->quoteInto("  (dest_country_id=? AND dest_region_id='0' AND dest_city='' ",
                                    $request->getDestCountryId()) .
                            $zipSearchString
                    );
                    break;
                case 6:
                    $select->where(
                            $connection->quoteInto("  (dest_country_id=? ",
                                    $request->getDestCountryId()) .
                            $connection->quoteInto(" AND dest_region_id=? AND dest_city='' AND dest_zip='') ",
                                    $request->getDestRegionId())
                    );
                    break;

                case 7:
                    $select->where(
                            $connection->quoteInto("  (dest_country_id=? AND dest_region_id='0' AND dest_city='' AND dest_zip='') ",
                                    $request->getDestCountryId())
                    );
                    break;
                case 8:
                    $select->where(
                            "  (dest_country_id='0' AND dest_region_id='0'" .
                            $zipSearchString
                    );
                    break;

                case 9:
                    $select->where(
                            "  (dest_country_id='0' AND dest_region_id='0' AND dest_zip='')"
                    );
                    break;
            }
            if (is_array($request->getMRConditionName())) {
                $i = 0;
                foreach ($request->getMRConditionName() as $conditionName) {
                    if ($i == 0) {
                        $select->where('condition_name=?', $conditionName);
                    } else {
                        $select->orWhere('condition_name=?', $conditionName);
                    }
                    $select->where('condition_from_value<=?',
                            $request->getData($conditionName));
                    $i++;
                }
            } else {
                $select->where('condition_name=?',
                        $request->getMRConditionName());
                $select->where('condition_from_value<=?',
                        $request->getData($request->getMRConditionName()));
                $select->where('condition_to_value>=?',
                        $request->getData($request->getMRConditionName()));
            }
            if (count($allowedMethods)) {
                $select->where('delivery_type_code IN (?)',
                        array_keys($allowedMethods));
            }
            $select->where('website_id=?', $request->getWebsiteId());
            $select->where('is_active=?', 1);
            $select->order('dest_country_id DESC');
            $select->order('dest_region_id DESC');
            $select->order('dest_zip DESC');
            $select->order('condition_from_value DESC');
            /*
              pdo has an issue. we cannot use bind
             */
            $newdata = array();
            $row = $connection->fetchAll($select);
            if (!empty($row)) {
                // have found a result or found nothing and at end of list!
                foreach ($row as $data) {
                    $newdata[] = $data;
                }
                break;
            }
        }
        return $newdata;
    }

}
