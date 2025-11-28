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

namespace StreamMarket\RoyalMailShipping\Api\Data;

/**
 * @api
 */
interface RateInterface
{

    const RATE_ID = 'pk';
    const PRICE = 'price';
    const IS_ACTIVE = 'is_active';

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set ID
     *
     * @param int $id
     * @return \StreamMarket\RoyalMailShipping\Api\Data\RateInterface
     */
    public function setId($id);

    /**
     * Get identifier
     *
     * @return string
     */
    public function getPrice();

    /**
     * Is active
     *
     * @return bool|null
     */
    public function isActive();

    /**
     * Set is active
     *
     * @param int|bool $isActive
     * @return \StreamMarket\RoyalMailShipping\Api\Data\RateInterface
     */
    public function setIsActive($isActive);
}
