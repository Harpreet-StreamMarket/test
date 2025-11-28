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

namespace StreamMarket\RoyalMailShipping\Model\Carrier;

use StreamMarket\RoyalMailShipping\Api\RateRepositoryInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;

/**
 * Description of RateRepository
 */
class RateRepository implements RateRepositoryInterface
{

    /**
     * @var \StreamMarket\RoyalMailShipping\Model\Carrier\RateFactory
     */
    private $rateFactory;

    /**
     * @var \Magento\Framework\Api\SearchResultsInterfaceFactory
     */
    private $searchResultFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var \StreamMarket\RoyalMailShipping\Model\ResourceModel\Carrier\Rate\CollectionFactory
     */
    private $rateCollectionFactory;

    /**
     * @var \StreamMarket\RoyalMailShipping\Model\ResourceModel\Carrier\Rate
     */
    private $rateResource;
    private $_rates = [];

    /**
     *
     * @param \StreamMarket\RoyalMailShipping\Model\Carrier\RateFactory $rateFactory
     * @param \StreamMarket\RoyalMailShipping\Model\ResourceModel\Carrier\Rate $rateResource
     * @param \StreamMarket\RoyalMailShipping\Model\ResourceModel\Carrier\Rate\CollectionFactory $rateCollectionFactory
     * @param \Magento\Framework\Api\SearchResultsInterfaceFactory $searchResultFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
    \StreamMarket\RoyalMailShipping\Model\Carrier\RateFactory $rateFactory,
            \StreamMarket\RoyalMailShipping\Model\ResourceModel\Carrier\Rate $rateResource,
            \StreamMarket\RoyalMailShipping\Model\ResourceModel\Carrier\Rate\CollectionFactory $rateCollectionFactory,
            \Magento\Framework\Api\SearchResultsInterfaceFactory $searchResultFactory,
            CollectionProcessorInterface $collectionProcessor)
    {

        $this->rateResource = $rateResource;
        $this->rateCollectionFactory = $rateCollectionFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchResultFactory = $searchResultFactory;
        $this->rateFactory = $rateFactory;
    }

    /**
     *
     * @param \StreamMarket\RoyalMailShipping\Api\Data\RateInterface $rate
     * @return boolean
     * @throws CouldNotDeleteException
     */
    public function delete(\StreamMarket\RoyalMailShipping\Api\Data\RateInterface $rate)
    {
        try {
            $this->rateResource->delete($rate);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                    'Could not delete the rate: %1', $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     *
     * @param type $rateId
     * @return type
     */
    public function deleteById($rateId)
    {
        return $this->delete($this->getById($rateId));
    }

    /**
     *
     * @param itn $rateId
     * @return \StreamMarket\RoyalMailShipping\Model\Carrier\Rate | null
     * @throws NoSuchEntityException
     */
    public function getById($rateId)
    {
        if (!isset($this->_rates[$rateId])) {
            /* @var $rate \StreamMarket\RoyalMailShipping\Model\Carrier\Rate */
            $rate = $this->rateFactory->create();
            $rate->load($rateId);
            if (!$rate->getId()) {
                throw new NoSuchEntityException(__('Rate with id "%1" does not exist.',
                        $rateId));
            }
            $this->_rates[$rate->getId()] = $rate;
        }
        return $this->_rates[$rateId];
    }

    /**
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return type
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        /* @var $collection \StreamMarket\RoyalMailShipping\Model\ResourceModel\Carrier\Rate\Collection */
        $collection = $this->rateCollectionFactory->create();

        $this->collectionProcessor->process($searchCriteria, $collection);

        $searchResults = $this->searchResultFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     *
     * @param \StreamMarket\RoyalMailShipping\Api\Data\RateInterface $rate
     * @return \StreamMarket\RoyalMailShipping\Api\Data\RateInterface
     * @throws CouldNotSaveException
     */
    public function save(\StreamMarket\RoyalMailShipping\Api\Data\RateInterface $rate)
    {
        try {
            $this->rateResource->save($rate);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(
            __('Could not save the rate: %1', $exception->getMessage()),
            $exception
            );
        }
        return $rate;
    }

}
