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

namespace StreamMarket\RoyalMailShipping\Ui\DataProvider\Shipment;

use Magento\Ui\DataProvider\AbstractDataProvider;
use StreamMarket\RoyalMailShipping\Model\ResourceModel\Transaction\CollectionFactory;

/**
 * Description of GridDataProvider
 */
class GridDataProvider extends AbstractDataProvider
{

    /**
     * @var StreamMarket\RoyalMailShipping\Model\ResourceModel\Transaction\Collection
     */
    protected $collection;

    public function __construct($name, $primaryFieldName, $requestFieldName,
            CollectionFactory $collectionFactory, array $meta = array(),
            array $data = array())
    {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta,
                $data);
        $this->collection = $collectionFactory->create();
        $this->collection->addFieldToFilter('request_type', \StreamMarket\RoyalMailShipping\Helper\Data::REQUEST_TYPE_CREATE_SHIPMENT)
                ->addFieldToFilter('has_error', \StreamMarket\RoyalMailShipping\Model\Transaction::HAS_ERROR_NO)
                ->addFieldToFilter('main_table.order_id', array('gt' => 0));
				//$this->collection->getSelect()->group('order_id');
        }

    /**
     *
     * @return StreamMarket\RoyalMailShipping\Model\ResourceModel\Transaction\Collection
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (!$this->getCollection()->isLoaded()) {
            $this->getCollection()->load();
        }
        return $this->getCollection()->toArray();
    }

}
