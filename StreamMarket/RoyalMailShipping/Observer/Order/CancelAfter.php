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

namespace StreamMarket\RoyalMailShipping\Observer\Order;

/**
 * Description of CancelAfter
 */
class CancelAfter implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * @var \StreamMarket\RoyalMailShipping\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Shipment\Track\CollectionFactory
     */
    private $trackCollectionFactory;

    /**
     * @var \StreamMarket\RoyalMailShipping\Model\CarrierFactory
     */
    private $carrierFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \StreamMarket\RoyalMailShipping\Model\ResourceModel\Transaction\CollectionFactory
     */
    private $transactionCollectionFactory;

    public function __construct(\StreamMarket\RoyalMailShipping\Model\ResourceModel\Transaction\CollectionFactory $transactionCollectionFactory,
            \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
            \StreamMarket\RoyalMailShipping\Model\CarrierFactory $carrierFactory,
            \Magento\Sales\Model\ResourceModel\Order\Shipment\Track\CollectionFactory $trackCollectionFactory,
            \StreamMarket\RoyalMailShipping\Helper\Data $helper,
            \Psr\Log\LoggerInterface $logger)
    {
        $this->transactionCollectionFactory = $transactionCollectionFactory;
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
        $this->carrierFactory = $carrierFactory;
        $this->trackCollectionFactory = $trackCollectionFactory;
        $this->helper = $helper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->helper->isModuleEnabled()) {
            return;
        }
        try {
            /* @var $order \Magento\Sales\Model\Order */
            $order = $observer->getEvent()->getOrder();
            if ($order->hasShipments()) {
                //get royalmail shipment transaction
                /* @var $transactions \StreamMarket\RoyalMailShipping\Model\ResourceModel\Transaction\Collection */
                $transactions = $this->transactionCollectionFactory->create();
                $transactions->addFieldToFilter('order_id', $order->getId())
                        ->addFieldToFilter('request_type',
                                \StreamMarket\RoyalMailShipping\Helper\Data::REQUEST_TYPE_CREATE_SHIPMENT)
                        ->addFieldToFilter('has_error',
                                \StreamMarket\RoyalMailShipping\Model\Transaction::HAS_ERROR_NO)
                        ->addFieldToFilter('shipment_number',
                                array('notnull' => true))
                        ->addFieldToFilter('shipment_id', array('gt' => 0))
                        ->addFieldToFilter('status',
                                array(
                            'nin' => array(
                                \StreamMarket\RoyalMailShipping\Helper\Data::STATUS_CANCELLED,
                                \StreamMarket\RoyalMailShipping\Helper\Data::STATUS_MANIFESTED)
                                )
                );
                if ($transactions->count()) {
                    foreach ($transactions as $transaction):
                        $shipment = $transaction->getOrderShipment();
                        $request = new \Magento\Framework\DataObject();
                        $request->setShipmentNumber($transaction->getShipmentNumber())
                                ->setOrderShipment($shipment);
                        $carrier = $this->carrierFactory->create();
                        $_transaction = $carrier->cancelShipment($request);
                        if (!$_transaction->hasErrors()) {
                            /* delete tracking number */
                            /* @var $trackCollection \Magento\Sales\Model\ResourceModel\Order\Shipment\Track\Collection */
                            $trackCollection = $this->trackCollectionFactory->create();
                            $trackCollection->addFieldToFilter('parent_id',
                                            $transaction->getShipmentId())
                                    ->addFieldToFilter('track_number',
                                            $transaction->getShipmentNumber());
                            foreach ($trackCollection as $track):
                                if ($track->getCarrierCode() == \StreamMarket\RoyalMailShipping\Model\Carrier::CODE) {
                                    $track->setShipment($shipment);
                                    $track->setCanceledOnline(true)//do not dispatch track delete observer
                                            ->delete();
                                }
                            endforeach;
                        } else {
                            $this->logger->log((string) $_transaction->getMessage());
                        }
                    endforeach;
                }
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

}
