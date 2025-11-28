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

namespace StreamMarket\RoyalMailShipping\Observer\Shipment;

/**
 * Description of SaveBefore
 */
class SaveBefore implements \Magento\Framework\Event\ObserverInterface
{
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $shipment = $observer->getEvent()->getShipment();
        if ($shipment->getRoyalmailShippingLabel()) {
            $shipment->setShippingLabel($shipment->getRoyalmailShippingLabel());
        }
    }

}
