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

namespace StreamMarket\RoyalMailShipping\Observer\Order\Shipment\View;

/**
 * Description of AddPrintLabelButton
 */
class AddPrintLabelButton implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * @var \StreamMarket\RoyalMailShipping\Helper\Data
     */
    private $helper;

    /**
     * @var \StreamMarket\RoyalMailShipping\Model\CarrierFactory
     */
    private $carrierFactory;

    /**
     * @var \StreamMarket\RoyalMailShipping\Model\ResourceModel\Transaction\CollectionFactory
     */
    private $transactionCollectionFactory;

    const BTN_ADDED_REG_KEY = 'sm_lbl_btn_added';

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
    \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
            \Magento\Framework\Registry $registry,
            \StreamMarket\RoyalMailShipping\Model\ResourceModel\Transaction\CollectionFactory $transactionCollectionFactory,
            \StreamMarket\RoyalMailShipping\Model\CarrierFactory $carrierFactory,
            \StreamMarket\RoyalMailShipping\Helper\Data $helper
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->registry = $registry;
        $this->transactionCollectionFactory = $transactionCollectionFactory;
        $this->carrierFactory = $carrierFactory;
        $this->helper = $helper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->helper->isModuleEnabled()) {
            return;
        }
        $block = $observer->getEvent()->getBlock();
        if ($block->getNameInLayout() == 'shipment_tracking' && $block instanceof \Magento\Shipping\Block\Adminhtml\Order\Tracking\View) {
            /* @var $shipment \Magento\Sales\Model\Order\Shipment */
            $shipment = $block->getShipment();
            $shippingCarrier = $shipment->getOrder()->getShippingCarrier();
            if ($shipment->getId() && !($shippingCarrier && $shippingCarrier->isShippingLabelsAvailable() && $shippingCarrier->getCarrierCode() != 'smroyalmail')) {
                /* shipment has label but order shipment carrier do not have shipping label */
                $labelButtonHtml = null;
                if ($shipment->getShippingLabel() && !($shippingCarrier && $shippingCarrier->isShippingLabelsAvailable()) && !$this->registry->registry(self::BTN_ADDED_REG_KEY)) {
                    $url = $block->getUrl('adminhtml/order_shipment/printLabel',
                            array('shipment_id' => $shipment->getId()));

                    $labelButtonHtml = $block->getLayout()->createBlock(
                                    \Magento\Backend\Block\Widget\Button::class
                            )->setData(
                                    ['label' => __('Print Shipping Label'), 'onclick' => 'setLocation(\'' . $url . '\')']
                            )->toHtml();
                    $this->registry->register(self::BTN_ADDED_REG_KEY, 1);
                }
                $_child = $block->getLayout()->createBlock('\Magento\Shipping\Block\Adminhtml\Order\Tracking\View')
                        ->setTemplate($block->getTemplate());
                $_child->setNameInLayout('sm_calling_block');
                $block->setChild('sm_calling_block', $_child);
                $block->setData('button_html', $labelButtonHtml)
                        ->setTransaction($this->getAllocatedTransaction($shipment))
                        ->setRoyalMailCarrier($this->carrierFactory->create())
                        ->setTemplate('StreamMarket_RoyalMailShipping::order/shipment/view/shipping_label_btn.phtml');
            }
        }
    }

    /**
     * Return not printed transaction
     * @param \Magento\Sales\Model\Order\Shipment $shipment
     * @return \StreamMarket\RoyalMailShipping\Model\Transaction | null
     */
    public function getAllocatedTransaction(\Magento\Sales\Model\Order\Shipment $shipment)
    {
        /* @var $collection \StreamMarket\RoyalMailShipping\Model\ResourceModel\Transaction\Collection */
        $collection = $this->transactionCollectionFactory->create();
        $collection->addFieldToFilter('shipment_id', $shipment->getId())
                ->addFieldToFilter('status',
                        \StreamMarket\RoyalMailShipping\Helper\Data::STATUS_ALLOCATED)
                ->addFieldToFilter('request_type',
                        \StreamMarket\RoyalMailShipping\Helper\Data::REQUEST_TYPE_CREATE_SHIPMENT);
        return $collection->getSize() ? $collection->getFirstItem() : null;
    }

}
