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
 * Description of TrackAddAfter
 */
class TrackAddAfter implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * @var \StreamMarket\RoyalMailShipping\Helper\Data
     */
    private $helper;

    /**
     * @var \StreamMarket\RoyalMailShipping\Model\ResourceModel\Transaction\CollectionFactory
     */
    private $transactionCollectionFactory;

    public function __construct(\StreamMarket\RoyalMailShipping\Model\ResourceModel\Transaction\CollectionFactory $transactionCollectionFactory,
            \StreamMarket\RoyalMailShipping\Helper\Data $helper)
    {
        $this->transactionCollectionFactory = $transactionCollectionFactory;
        $this->helper = $helper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->helper->isModuleEnabled()) {
            return;
        }
        $track = $observer->getEvent()->getTrack();
        if ($track->getId() && $track->getCarrierCode() == \StreamMarket\RoyalMailShipping\Model\Carrier::CODE) {
            try {
                /* @var $transactions \StreamMarket\RoyalMailShipping\Model\ResourceModel\Transaction\Collection */
                $transactions = $this->transactionCollectionFactory->create();
                $transactions->addFieldToFilter('shipment_number',
                        $track->getTrackNumber());
                $transactions->addFieldToFilter('order_id',
                        $track->getShipment()->getOrderId());
                $transactions->addFieldToFilter('shipment_id', 0);
                if ($transactions->count()) {
                    foreach ($transactions as $transaction):
                        $transaction->setShipmentId($track->getShipment()->getId())->save();
                    endforeach;
                }
            } catch (\Exception $e) {

            }
        }
    }

}
