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

namespace StreamMarket\RoyalMailShipping\Api;

/**
 * @api
 */
interface RateRepositoryInterface
{

    /**
     * Save rate.
     *
     * @param \StreamMarket\RoyalMailShipping\Api\Data\RateInterface $rate
     * @return \StreamMarket\RoyalMailShipping\Api\Data\RateInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\StreamMarket\RoyalMailShipping\Api\Data\RateInterface $rate);

    /**
     * Retrieve rate.
     *
     * @param int $rateId
     * @return \StreamMarket\RoyalMailShipping\Api\Data\RateInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($rateId);

    /**
     * Retrieve rates matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \StreamMarket\RoyalMailShipping\Api\Data\PageSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete rate.
     *
     * @param \StreamMarket\RoyalMailShipping\Api\Data\RateInterface $rate
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\StreamMarket\RoyalMailShipping\Api\Data\RateInterface $rate);

    /**
     * Delete rate by ID.
     *
     * @param int $rateId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($rateId);
}
