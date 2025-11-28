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

namespace StreamMarket\RoyalMailShipping\Controller\Adminhtml\Mass;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Backend\App\Action;
use Magento\Framework\App\ObjectManager;
/**
 * Description of Ship
 */
class Process extends \Magento\Backend\App\Action implements HttpGetActionInterface
{
	protected $orderFactory;
	protected $shipmentFactory;
	protected $orderModel;
	protected $trackFactory;
	protected $logger;
	protected $resourceConnection;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    private $resultPageFactory;
	protected $orderRepository;
	
	private $apicall;
	
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Sales::shipment';

    public function __construct(
    \Magento\Backend\App\Action\Context $context,
            \Magento\Framework\View\Result\PageFactory $resultPageFactory,
			\Magento\Sales\Api\Data\OrderInterface $orderFactory,
		\Magento\Sales\Model\Convert\Order $orderModel,
		\Magento\Sales\Model\Order\Shipment\TrackFactory $trackFactory,
		\Magento\Shipping\Model\ShipmentNotifier $shipmentFactory,
		\Psr\Log\LoggerInterface $logger,
		\Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
		\StreamMarket\RoyalMailShipping\Controller\Adminhtml\Shipment\ShipWithRoyalmail $apicall,
		\Magento\Framework\App\ResourceConnection $resourceConnection
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
		
	echo "hello";	
		exit;
	$data = $this->getRequest()->getParams();
		$id = $data['order_id'];
		$this->createShipment($id);
    }
	 
	 public function createShipment($id,$packages){
		
		$order = $this->orderRepository->get($id);
	    $shipmentCollection = $order->getShipmentsCollection();
	 if($order->canShip()){
	 
		$shipment = $this->orderModel->toShipment($order);
	    $item_details = $order->getAllItems();
		$shippingAddress = $order->getShippingAddress()->getData();
		$billingAddress = $order->getBillingAddress()->getData();
		
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
	 
			$shipment->save();
			$shipment->getOrder()->save();
	 
			// Send email
			$this->shipmentFactory->notify($shipment);
			$shipment->save();
			$this->_redirect('sales/order/view', ['order_id' => $id]);
			$orderId = $id;
			
			$this->callCreateShipmentApi($shippingAddress,$billingAddress,$item_details,$orderId,$order,'SD2',$packages);
			
			//$this->messageManager->addSuccessMessage('Shipment created successfully.');

		} catch (\Exception $e) {
			$this->logger->info($e->getMessage());
			$this->messageManager->addErrorMessage($e->getMessage());
		}
	  }else{
		$this->logger->info('You can not create an shipment:');
		$this->messageManager->addErrorMessage('You can not create an shipment:');
	  }
	  
	 }
	 
	 public function callCreateShipmentApi($shippingAddress,$billingAddress,$item_details,$orderId,$order,$service_offering,$packages)
	 {
		 $connection = $this->resourceConnection->getConnection();
		 $sql = "Select entity_id FROM sales_shipment where order_id = $orderId";
		 $data= $connection->fetchAll($sql);
		 foreach($data as $val){
			 $shipmentId = $val['entity_id'];
		 }
		 foreach($packages as $package_data){
			 $package_data = $package_data['params'];
		 }
		 
		//for ($x = 1; $x <= $count; $x++) {
			$this->apicall->callMultiPostAPI($shipmentId,$shippingAddress,$billingAddress,$item_details,$orderId,$order,$service_offering,$packages);
			//}
		 
	}
        
    }


