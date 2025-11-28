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

namespace StreamMarket\RoyalMailShipping\Model\Manifest;

/**
 * Description of DataProvider
 */
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{

    public function __construct($name, $primaryFieldName, $requestFieldName,
            \StreamMarket\RoyalMailShipping\Model\ResourceModel\Transaction\CollectionFactory $transactionCollectionFactory,
            array $meta = array(), array $data = array())
    {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta,
                $data);
        $this->collection = $transactionCollectionFactory->create();
    }

    public function getData()
    {
        return [];
    }

}
