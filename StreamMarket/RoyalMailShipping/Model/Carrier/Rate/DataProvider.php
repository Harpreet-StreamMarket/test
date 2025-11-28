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

namespace StreamMarket\RoyalMailShipping\Model\Carrier\Rate;

use Magento\Framework\App\Request\DataPersistorInterface;

/**
 * Description of DataProvider
 */
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var \StreamMarket\RoyalMailShipping\Model\ResourceModel\Carrier\Rate\Collection
     */
    protected $collection;

    /**
     * @var array
     */
    protected $loadedData;

    public function __construct($name, $primaryFieldName, $requestFieldName,
            \StreamMarket\RoyalMailShipping\Model\ResourceModel\Carrier\Rate\CollectionFactory $rateCollectionFactory,
            DataPersistorInterface $dataPersistor, array $meta = array(),
            array $data = array())
    {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta,
                $data);
        $this->collection = $rateCollectionFactory->create();
        $this->dataPersistor = $dataPersistor;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        /* @var $rate \StreamMarket\RoyalMailShipping\Model\Carrier\Rate */
        foreach ($items as $rate) {
            $this->loadedData[$rate->getId()] = $rate->getData();
        }

        $data = $this->dataPersistor->get('royalmail_rate');
        if (!empty($data)) {
            $rate = $this->collection->getNewEmptyItem();
            $rate->setData($data);
            $this->loadedData[$rate->getId()] = $rate->getData();
            $this->dataPersistor->clear('royalmail_rate');
        }

        return $this->loadedData;
    }

}
