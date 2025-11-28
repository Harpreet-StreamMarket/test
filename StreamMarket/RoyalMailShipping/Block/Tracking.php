<?php
namespace StreamMarket\RoyalMailShipping\Block;

use Exception;
use Psr\Log\LoggerInterface;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Api\Data\ShipmentTrackInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;

class Tracking
{
    /**
     * @var ShipmentRepositoryInterface
     */
    private $shipmentRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        ShipmentRepositoryInterface $shipmentRepository,
        LoggerInterface $logger
    ) {
        $this->shipmentRepository = $shipmentRepository;
        $this->logger = $logger;
    }

    /**
     * Get Shipment Trackig data by Shipment Id
     *
     * @param $id
     *
     * @return ShipmentTrackInterface|null
     */
    public function getTracking($shipmentId)
    {
        $shipment = $this->getShipmentById($shipmentId);

        if ($shipment) {
            return $shipment;
        }
        return null;
    }

    /**
     * Get Shipment data by Shipment Id
     *
     * @param $id
     *
     * @return ShipmentInterface|null
     */
    public function getShipmentById($id)
    {
        try {
            $shipment = $this->shipmentRepository->get($id);
        } catch (Exception $exception)  {
            $this->logger->critical($exception->getMessage());
            $shipment = null;
        }
        return $shipment;
    }
}