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

namespace StreamMarket\RoyalMailShipping\Model\ResourceModel\Carrier\Rate;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Description of Collection
 */
class Collection extends AbstractCollection
{

    protected function _construct()
    {
        $this->_init('StreamMarket\RoyalMailShipping\Model\Carrier\Rate',
                'StreamMarket\RoyalMailShipping\Model\ResourceModel\Carrier\Rate');
    }

    /**
     * {@inheritdoc}
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->_select->joinLeft(array("c" => $this->_resource->getTable('directory_country')),
                        'c.country_id = main_table.dest_country_id',
                        'iso3_code AS dest_country')
                ->joinLeft(array("r" => $this->_resource->getTable('directory_country_region')),
                        'r.region_id = main_table.dest_region_id',
                        'code AS dest_region')
                ->order(array("dest_country", "dest_region", "dest_zip"));
        return $this;
    }

    public function setWebsiteFilter($websiteId)
    {
        $this->_select->where("website_id = ?", $websiteId);
        return $this;
    }

    public function setConditionFilter($conditionName)
    {
        $this->_select->where("condition_name = ?", $conditionName);
        return $this;
    }

    public function setCountryFilter($countryId)
    {
        $this->_select->where("dest_country_id = ?", $countryId);
        return $this;
    }

}
