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

use Magento\Framework\Model\AbstractModel;

/**
 * Description of Rate
 */
class Rate extends AbstractModel implements \StreamMarket\RoyalMailShipping\Api\Data\RateInterface
{

    const ACTIVE_YES = 1;
    const ACTIVE_NO = 0;

    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('StreamMarket\RoyalMailShipping\Model\ResourceModel\Carrier\Rate');
    }

    public function getPrice()
    {
        return $this->getData(self::PRICE);
    }

    public function isActive()
    {
        return $this->getData(self::IS_ACTIVE);
    }

    public function setIsActive($isActive)
    {
        $this->setData($isActive);
        return $this;
    }

}
