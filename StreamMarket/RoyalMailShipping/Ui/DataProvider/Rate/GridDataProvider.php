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

namespace StreamMarket\RoyalMailShipping\Ui\DataProvider\Rate;

use Magento\Ui\DataProvider\AbstractDataProvider;
use StreamMarket\RoyalMailShipping\Model\ResourceModel\Carrier\Rate\CollectionFactory;

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
