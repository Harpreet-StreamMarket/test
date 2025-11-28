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

namespace StreamMarket\RoyalMailShipping\Model;

use Magento\Framework\Model\AbstractModel;

class MassAction extends AbstractModel
{
	protected $orderFactory;
	protected $shipmentFactory;
	protected $orderModel;
	protected $trackFactory;
	protected $logger;
 
	public function __construct(
		\Magento\Sales\Api\Data\OrderInterface $orderFactory,
		\Magento\Sales\Model\Convert\Order $orderModel,
		\Magento\Sales\Model\Order\Shipment\TrackFactory $trackFactory,
		\Magento\Shipping\Model\ShipmentNotifier $shipmentFactory,
		\Psr\Log\LoggerInterface $logger
	 
	)
	{
		$this->orderFactory = $orderFactory;
		$this->orderModel = $orderModel;
		$this->trackFactory = $trackFactory;
		$this->shipmentFactory = $shipmentFactory;
		$this->logger = $logger;
	}
 
	public function execute()
	{
	  $orderNumber = '38';
	  $order = $this->orderFactory->get($orderNumber);
	 
	 if($order->canShip()){
	 
		$shipment = $this->orderModel->toShipment($order);
	 
		foreach ($order->getAllItems() AS $orderItem) {
	 
			if (!$orderItem->getQtyToShip() || $orderItem->getIsVirtual()) {
				continue;
			}
			$qtyShipped = $orderItem->getQtyToShip();
	 
			$shipmentItem = $this->orderModel->itemToShipmentItem($orderItem)->setQty($qtyShipped);
	 
			$shipment->addItem($shipmentItem);
	 
		}
	 
		$shipment->register();
		$shipment->getOrder()->setIsInProcess(true);
	 
		try {
	 
			$trackingIds = array(
					'0'=>array('carrier_code'=>'fedex','title' => 'Federal Express','number'=>'3131331230')
					
			);
	 
			/*Add Multiple tracking information*/
			foreach ($trackingIds as $trackingId) {
				$data = array(
					'carrier_code' => $trackingId['carrier_code'],
					'title' => $trackingId['title'],
					'number' => $trackingId['number'],
				);
				$track = $this->trackFactory->create()->addData($data);
				$shipment->addTrack($track)->save();
			}
	 
			$shipment->save();
			$shipment->getOrder()->save();
	 
			// Send email
			$this->shipmentFactory->notify($shipment);
			$shipment->save();
	 
		} catch (\Exception $e) {
			$this->logger->info($e->getMessage());
		}
	  }else{
		$this->logger->info('You can not create an shipment:' . $orderNumber);
	  }
	 
	 }
}