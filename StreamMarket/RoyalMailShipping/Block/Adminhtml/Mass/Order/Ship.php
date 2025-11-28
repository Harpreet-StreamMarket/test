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

namespace StreamMarket\RoyalMailShipping\Block\Adminhtml\Mass\Order;

/**
 * Description of ship
 */
class Ship extends \Magento\Backend\Block\Template
{

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    private $orderCollectionFactory;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    private $jsonHelper;

    /**
     * @var \StreamMarket\RoyalMailShipping\Model\Service\MatrixFactory
     */
    private $serviceMatrixFactory;

    /**
     * @var \StreamMarket\RoyalMailShipping\Model\Carrier\Source\EnabledServiceTypes
     */
    private $methodProvider;

    /**
     * @var \StreamMarket\RoyalMailShipping\Model\Carrier\Source\EnabledServiceTypes
     */
    private $serviceProvide;

    public function __construct(\Magento\Backend\Block\Template\Context $context,
            \StreamMarket\RoyalMailShipping\Model\Carrier\Source\EnabledServiceTypes $serviceProvider,
            \StreamMarket\RoyalMailShipping\Model\Carrier\Source\EnabledMethods $methodProvider,
            \StreamMarket\RoyalMailShipping\Model\Service\MatrixFactory $serviceMatrixFactory,
            \Magento\Framework\Json\Helper\Data $jsonHelper,
            \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
            \Magento\Framework\Registry $registry, array $data = array())
    {
        parent::__construct($context, $data);
        $this->serviceProvide = $serviceProvider;
        $this->methodProvider = $methodProvider;
        $this->serviceMatrixFactory = $serviceMatrixFactory;
        $this->jsonHelper = $jsonHelper;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->registry = $registry;
    }

    public function getAllowedServiceTypes()
    {
        return $this->serviceProvide->getOptions();
    }

    public function getSrviceMatrixJson()
    {
        $allowedMethods = $this->methodProvider->getOptions();
        $serviceTypes = $this->getAllowedServiceTypes();
        /* @var $serviceMatrix \StreamMarket\RoyalMailShipping\Model\Service\Matrix */
        $serviceMatrix = $this->serviceMatrixFactory->create();
        $serviceMatrixArray = $serviceMatrix->getServiceOfferingsByServiceTypes(array_keys($serviceTypes),
                array_keys($allowedMethods));
        return $this->jsonHelper->jsonEncode($serviceMatrixArray);
    }

    public function getSelectedOrders()
    {
        $selectedIds = $this->registry->registry('selected_order_ids');
        $orderCollection = $this->orderCollectionFactory->create();
        $orderCollection->addFieldToFilter('entity_id',
                array('in' => $selectedIds));
        $orderCollection->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS)
                ->columns(array('entity_id', 'increment_id'));
        $orderCollection->getSelect()->limit(20);
        return $orderCollection;
    }

    public function getHeaderText()
    {
        return __('Ship order(s) with Royal Mail.');
    }

    public function getFormAction()
    {
        return $this->getUrl('*/*/process');
    }

}
