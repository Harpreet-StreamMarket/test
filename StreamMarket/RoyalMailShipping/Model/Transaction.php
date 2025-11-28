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

/**
 * Description of Transaction
 *
 * @method $this setShipmentId(int $shipmentID) Shipment ID
 * @method $this setOrderId(int $orderID) Order ID
 * @method $this setTransactionId(mixed $transactionID) Transaction ID
 * @method $this setRequestType(string $requestType) Request Type
 * @method $this setServiceOfferingCode(string $code) Service Offering Code
 * @method $this setStatus(string $status) Transaction Status
 * @method $this setMessage(string $message) Error Message
 * @method $this setShipmentNumber(string $shipmentNumber) Shipment Number
 * @method $this setLabelFile(string $filePath) label File Path
 * @method $this setManifestBatchNumber(int $batchNumber) Manifest batch number
 * @method $this setManifestedInBatch(int $batchNumber) Manifest batch number
 * @method $this setHasError(boolean $hasError) Transaction has error
 * @method $this setCreatedAt(string $dateTime) Created DateTime
 * @method $this setUpdatedAt(string $dateTime) Updated DateTime
 * @method int getShipmentId() Shipment ID
 * @method int getOrderId() Order ID
 * @method string getRequestType() Request Type
 * @method string getServiceOfferingCode() Service Offering Code
 * @method string getStatus() Transaction Status
 * @method string getMessage() Error Message
 * @method string getShipmentNumber() Shipment Number
 * @method string getLabelFile() label File Path
 * @method int getManifestBatchNumber() Manifest batch number
 * @method int getManifestedInBatch() Manifest batch number
 * @method boolean getHasError() Transaction has error
 * @method string getCreatedAt() Created DateTime
 * @method string getUpdatedAt() Updated DateTime
 * @author lenovo
 */
class Transaction extends AbstractModel
{

    /**
     * @var \Magento\Sales\Api\ShipmentRepositoryInterface
     */
    private $shipmentRepository;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $date;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    private $directoryList;

    const HAS_ERROR_YES = 1;
    const HAS_ERROR_NO = 0;

    protected $_txn_prefix = '1';

    public function __construct(\Magento\Framework\Model\Context $context,
            \Magento\Framework\Registry $registry,
            \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepository,
            \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
            \Magento\Framework\Stdlib\DateTime\DateTime $date,
            \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
            \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
            array $data = array())
    {
        parent::__construct($context, $registry, $resource, $resourceCollection,
                $data);
        $this->directoryList = $directoryList;
        $this->date = $date;
        $this->shipmentRepository = $shipmentRepository;
    }

    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('StreamMarket\RoyalMailShipping\Model\ResourceModel\Transaction');
    }

    public function beforeSave()
    {
        parent::beforeSave();
        if (!$this->getData('transaction_id')) {
            $this->setTransactionId($this->_getTransactionId());
        }
        if (!$this->getId()) {
            $this->setCreatedAt($this->date->gmtDate());
        }
        $this->setUpdatedAt($this->date->gmtDate());
        return $this;
    }

    private function _getTransactionId()
    {
        if ($this->getId()) {
            return $this->_txn_prefix . str_pad($this->getId(), 9, '0',
                            STR_PAD_LEFT);
        }
        return null;
    }

    public function getTransactionId()
    {
        if (!$this->getData('transaction_id') && $this->getId()) {
            $this->setData('transaction_id', $this->_getTransactionId());
        }
        return $this->getData('transaction_id');
    }

    public function getLabelFilePath()
    {
        if ($this->getId() && $this->getLabelFile()) {
            $mediaPath = $this->directoryList->getUrlPath(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
            return $mediaPath . $this->getLabelFile();
        }
        return null;
    }

    /**
     * Order Shipment for transaction
     * @return \Magento\Sales\Model\Order\Shipment | null
     */
    public function getOrderShipment()
    {
        if (!$this->getData('order_shipment') && $this->getShipmentId()) {
            /* @var $shipment \Magento\Sales\Model\Order\Shipment */
            $shipment = $this->shipmentRepository->get($this->getShipmentId());
            $shipment->load($this->getShipmentId());
            $this->setData('order_shipment', $shipment);
        }
        return $this->getData('order_shipment');
    }

}
