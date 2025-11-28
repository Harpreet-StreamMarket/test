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

namespace StreamMarket\RoyalMailShipping\Controller\Adminhtml\Order;

use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Description of Ship
 */
class Ship extends \Magento\Backend\App\Action
{

    /**
     * @var \StreamMarket\RoyalMailShipping\Model\CarrierFactory
     */
    private $carrierFactory;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader
     */
    private $shipmentLoader;

    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\ShipmentSender
     */
    private $shipmentSender;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $resultJsonFactory;

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Sales::shipment';

    public function __construct(\Magento\Backend\App\Action\Context $context,
            \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
            \Magento\Sales\Model\Order\Email\Sender\ShipmentSender $shipmentSender,
            \StreamMarket\RoyalMailShipping\Model\CarrierFactory $carrierFactory,
            \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader $shipmentLoader,
            OrderRepositoryInterface $orderRepository
    )
    {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->shipmentSender = $shipmentSender;
        $this->shipmentLoader = $shipmentLoader;
        $this->orderRepository = $orderRepository;
        $this->carrierFactory = $carrierFactory;
    }

    /**
     * Save shipment and order in one transaction
     *
     * @param \Magento\Sales\Model\Order\Shipment $shipment
     * @return $this
     */
    protected function _saveShipment($shipment)
    {
        $shipment->getOrder()->setIsInProcess(true);
        $transaction = $this->_objectManager->create(
                \Magento\Framework\DB\Transaction::class
        );
        $transaction->addObject(
                $shipment
        )->addObject(
                $shipment->getOrder()
        )->save();

        return $this;
    }

    public function execute()
    {
        $serviceOffering = $this->getRequest()->getParam('service_offering');
        $serviceType = $this->getRequest()->getParam('service_type');
        $containerType = $this->getRequest()->getParam('container_type');
        $orderId = $this->getRequest()->getParam('order_id');
        $notifyCustomer = $this->getRequest()->getParam('notify_customer');
        $result = array('success' => false, 'message' => '');
        try {
            $this->shipmentLoader->setOrderId($orderId);
            $this->shipmentLoader->setShipmentId(null);
            $shipment = $this->shipmentLoader->load();
            $order = $this->orderRepository->get($orderId);
            if ($shipment) {
                /* @var $carrier \StreamMarket\RoyalMailShipping\Model\Carrier */
                $carrier = $this->carrierFactory->create();
                $shipment = $carrier->shipOrderWith($order,
                        $serviceType . '_' . $serviceOffering, $containerType);
                $shipment->getOrder()->setCustomerNoteNotify($notifyCustomer);
                $this->_saveShipment($shipment);
                $result['success'] = true;
                $result['message'] = '<a href="' . $this->getUrl('sales/order/view',
                                array('order_id' => $order->getId())) . '" target="_blank">#' . $order->getIncrementId() . '</a> - ' . __('The shipment has been created.');
                try {
                    if ($notifyCustomer) {
                        $this->shipmentSender->send($shipment);
                    }
                } catch (\Exception $ex) {

                }
            } else {
                /* @var $messages \Magento\Framework\Message\Collection */
                $messgaes = $this->messageManager->getMessages(true);
                $msgs = [];
                foreach ($messgaes->getErrors() as $errorMessage):
                    $msgs[] = $errorMessage->getText();
                endforeach;
                if (!empty($msgs)) {
                    $result['message'] = 'ERROR- <a href="' . $this->getUrl('sales/order/view',
                            array('order_id' => $orderId)) . '" target="_blank">View order #' . $orderId . '</a> - '.implode(', ', $msgs);
                } else {
                    $result['message'] = __('Unable to load shipment.');
                }
            }
        } catch (\Exception $e) {
            $result['success'] = false;
            $result['message'] = 'ERROR- <a href="' . $this->getUrl('sales/order/view',
                            array('order_id' => $orderId)) . '" target="_blank">View order #' . $orderId . '</a> - ' . $e->getMessage();
        }
        /** @var \Magento\Framework\Controller\Result\Json $result */
        $response = $this->resultJsonFactory->create();
        $response->setData($result);
        return $response;
    }

}
