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
 * Description of TrackedMethods
 */
class TrackedMethods extends \StreamMarket\RoyalMailShipping\Model\Carrier\Source\AbstractSource
{

    /**
     * @var \StreamMarket\RoyalMailShipping\Model\Service\MatrixFactory
     */
    private $serviceMatrixFactory;

    public function __construct(\StreamMarket\RoyalMailShipping\Model\Carrier\Codes $carrierCodes,
            \StreamMarket\RoyalMailShipping\Model\Service\MatrixFactory $serviceMatrixFactory)
    {
        parent::__construct($carrierCodes);
        $this->serviceMatrixFactory = $serviceMatrixFactory;
    }

    public function toOptionArray()
    {
        $arr = array();
        $signatureOfferings = $this->serviceMatrixFactory->create()->getSignatureServiceOfferings();
        foreach ($this->carrierCodes->getCode('method') as $k => $v) {
            if (in_array($k, $signatureOfferings)) {
                $arr[] = array('value' => $k, 'label' => $v);
            }
        }
        return $arr;
    }

}
