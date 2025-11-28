<?php

namespace StreamMarket\RoyalMailShipping\Model;

use Magento\Sales\Api\Data\ShipmentTrackInterfaceFactory;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class Tracking
{
    /**
     * @var ShipmentRepositoryInterface
     */
    private $shipmentRepository;

    /**
     * @var ShipmentTrackInterfaceFactory
     */
    private $trackFactory;

    /**
     * @param ShipmentRepositoryInterface $shipmentRepository
     * @param ShipmentTrackInterfaceFactory $trackFactory
     */
    public function __construct(
        ShipmentRepositoryInterface $shipmentRepository,
        ShipmentTrackInterfaceFactory $trackFactory
    ) {
        $this->shipmentRepository = $shipmentRepository;
        $this->trackFactory = $trackFactory;
    }

     
    public function addCustomTrack($shipmentId,$tracking_number)
    {
        $number = $tracking_number;
        $carrier = 'smroyalmail';
        $title = 'RoyalMail Shipping';

        try {
            $shipment = $this->shipmentRepository->get($shipmentId);
            $track = $this->trackFactory->create()->setNumber(
                $number
            )->setCarrierCode(
                $carrier
            )->setTitle(
                $title
            );
            $shipment->addTrack($track);
            $this->shipmentRepository->save($shipment);

        } catch (NoSuchEntityException $e) {
            //Shipment does not exist
        }
    }
	
	public function getTrackInfo($tracking_number)
    {
        try {
			$transactions = $this->trackFactory->create()->getCollection();
			$transactions->addFieldToFilter('track_number',$tracking_number);
			$transactions->addFieldToSelect('entity_id');
			$data = $transactions->getData();
			foreach($data as $track_data){
				$entity_id = $track_data['entity_id'];
				$transactions = $this->trackFactory->create()->load($entity_id);
				$transactions->delete();
			}
            
        } catch (NoSuchEntityException $e) {
			echo $e;
            //Shipment does not exist
        }
    }
}