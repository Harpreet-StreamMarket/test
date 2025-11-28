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

namespace StreamMarket\RoyalMailShipping\Model\Carrier\Source;

/**
 * Description of AbstractSource
 */
abstract class AbstractSource implements \Magento\Framework\Data\OptionSourceInterface
{

    /**
     * @var \StreamMarket\RoyalMailShipping\Model\Carrier\Codes
     */
    protected $carrierCodes;

    public function __construct(\StreamMarket\RoyalMailShipping\Model\Carrier\Codes $carrierCodes)
    {
        $this->carrierCodes = $carrierCodes;
    }

}
