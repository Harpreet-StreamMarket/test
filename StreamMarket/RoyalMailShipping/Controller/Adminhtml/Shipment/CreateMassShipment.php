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

namespace StreamMarket\RoyalMailShipping\Controller\Adminhtml\Shipment;

use Magento\Framework\Controller\ResultFactory;

class CreateMassShipment extends \Magento\Backend\App\Action
{
    protected \Magento\Sales\Api\Data\OrderInterface $orderFactory;
    protected \Magento\Shipping\Model\ShipmentNotifier $shipmentFactory;
    protected $orderModel;
    protected \Magento\Sales\Model\Order\Shipment\TrackFactory $trackFactory;
    protected \Psr\Log\LoggerInterface $logger;
    protected \Magento\Framework\App\ResourceConnection $resourceConnection;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    private \Magento\Framework\View\Result\PageFactory $resultPageFactory;
    protected \Magento\Sales\Api\Data\OrderInterface $orderRepository;

    private ShipWithRoyalmail $apicall;

    private \Magento\Inventory\Model\Source\Command\GetSourcesAssignedToStockOrderedByPriority $sourceCommand;

    private \Magento\InventorySales\Model\StockResolver $stockResolver;

    protected \Magento\Framework\Module\Manager $_moduleManager;

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Sales::shipment';

    public function __construct(
        \Magento\Backend\App\Action\Context                                                $context,
        \Magento\Framework\View\Result\PageFactory                                         $resultPageFactory,
        \Magento\Sales\Api\Data\OrderInterface                                             $orderFactory,
        \Magento\Sales\Model\Convert\Order                                                 $orderModel,
        \Magento\Sales\Model\Order\Shipment\TrackFactory                                   $trackFactory,
        \Magento\Shipping\Model\ShipmentNotifier                                           $shipmentFactory,
        \Psr\Log\LoggerInterface                                                           $logger,
        \Magento\Sales\Api\Data\OrderInterface                                             $orderRepository,
        \StreamMarket\RoyalMailShipping\Controller\Adminhtml\Shipment\ShipWithRoyalmail    $apicall,
        \Magento\Framework\App\ResourceConnection                                          $resourceConnection,
        \Magento\Inventory\Model\Source\Command\GetSourcesAssignedToStockOrderedByPriority $sourceCommand,
        \Magento\InventorySales\Model\StockResolver                                        $stockResolver,
        \Magento\Framework\Module\Manager                                                  $moduleManager
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->orderFactory = $orderFactory;
        $this->orderModel = $orderModel;
        $this->trackFactory = $trackFactory;
        $this->shipmentFactory = $shipmentFactory;
        $this->logger = $logger;
        $this->orderRepository = $orderRepository;
        $this->apicall = $apicall;
        $this->resourceConnection = $resourceConnection;
        $this->sourceCommand = $sourceCommand;
        $this->stockResolver = $stockResolver;
        $this->_moduleManager = $moduleManager;
    }

    /**
     * Init actions
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magento_Sales::sales')
            ->addBreadcrumb(__('Royal Mail'), __('Royal Mail'))
            ->addBreadcrumb(__('Mass Shipment'), __('Mass Shipment'));
        return $resultPage;
    }

    /**
     * Edit Rate
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $data = $this->getRequest()->getParams();
        $order_id = $data['order_number'];
        $service_offering = $data['service_offering'];
        $notify_customer = $data['notify_customer'];
        $parcel_type = $data['parcel_type'];
        $signed_for = $data['signed_for'];
        $result = $this->createShipment($order_id, $service_offering, $notify_customer, $parcel_type, $signed_for);
        return $result;
    }

    public function createShipment($increment_id, $service_offering, $notify_customer, $parcel_type, $signed_for)
    {

        $order = $this->orderRepository->loadByIncrementId($increment_id);
        $order_id = $order->getId();
        $shipmentCollection = $order->getShipmentsCollection();
        if ($order->canShip()) {

            $shipment = $this->orderModel->toShipment($order);
            $item_details = $order->getAllItems();
            $shippingAddress = $order->getShippingAddress()->getData();
            $billingAddress = $order->getBillingAddress()->getData();
            foreach ($order->getAllItems() as $orderItem) {

                if (!$orderItem->getQtyToShip() || $orderItem->getIsVirtual()) {
                    continue;
                }
                $qtyShipped = $orderItem->getQtyToShip();

                $shipmentItem = $this->orderModel->itemToShipmentItem($orderItem)->setQty($qtyShipped);

                $shipment->addItem($shipmentItem);

            }
            //$shipment->getExtensionAttributes()->setSourceCode('calw');
            if ($this->_moduleManager->isEnabled('Magento_Inventory')) {

                $shipment->getExtensionAttributes()
                    ->setSourceCode(
                        array_reduce(
                        /* @var \Magento\Inventory\Model\Source\Command\GetSourcesAssignedToStockOrderedByPriority */
                            $this->sourceCommand->execute(
                            /* @var \Magento\InventorySales\Model\StockResolver */
                                $this->stockResolver
                                    ->execute(
                                        'website',
                                        $order->getStore()
                                            ->getWebsite()
                                            ->getCode()
                                    )
                                    ->getStockId()
                            ),
                            function ($sourceCode, $source) {
                                return $sourceCode ?: $source->getSourceCode();
                            },
                            false
                        )
                    );
            }
            $shipment->register();
            $shipment->getOrder()->setIsInProcess(true);

            try {

                $shipment->save();
                $shipment->getOrder()->save();

                // Send email
                if ($notify_customer == 1) {
                    $this->shipmentFactory->notify($shipment);
                }

                return $this->callCreateShipmentApi($shippingAddress, $item_details, $order_id, $increment_id, $billingAddress, $order, $service_offering, $parcel_type, $signed_for);
            } catch (\Exception $e) {
                echo $e;
                $this->logger->info($e->getMessage());
            }
        } else {
            $msg = "Shipment already created You can not create an shipment again.";
            $response = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)->setData(['status' => "rapeat", 'message' => $msg]);
            $this->logger->info('You can not create an shipment:');
            return $response;
        }

    }

    public function callCreateShipmentApi($shippingAddress, $item_details, $orderId, $increment_id, $billingAddress, $order, $service_offering, $parcel_type, $signed_for)
    {
        $connection = $this->resourceConnection->getConnection();
        $sql = "Select entity_id FROM sales_shipment where order_id = $orderId";
        $data = $connection->fetchAll($sql);
        foreach ($data as $val) {
            $shipmentId = $val['entity_id'];
            return $this->apicall->callPostAPI($shipmentId, $shippingAddress, $billingAddress, $item_details, $orderId, $order, $service_offering, '', '', '', '', '', 'massaction', $parcel_type, $signed_for);
        }
    }

}
