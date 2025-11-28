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

namespace StreamMarket\RoyalMailShipping\Observer\Shipment;

/**
 * Description of TrackDeleteCommitAfter
 */
class TrackDeleteCommitAfter implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \StreamMarket\RoyalMailShipping\Helper\Data
     */
    private $helper;

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
            \StreamMarket\RoyalMailShipping\Helper\Data $helper,
            \Psr\Log\LoggerInterface $logger)
    {
        $this->transactionCollectionFactory = $transactionCollectionFactory;
        $this->scopeConfig = $scopeConfig;
        $this->helper = $helper;
        $this->logger = $logger;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->helper->isModuleEnabled()) {
            return;
        }
        $track = $observer->getEvent()->getTrack();
        if ($track->getId() && $track->getCarrierCode() == \StreamMarket\RoyalMailShipping\Model\Carrier::CODE) {
            /* remove shipping label for deleted track */
            try {
                $shipment = $track->getShipment();
                if ($shipment->getShippingLabel()) {
                    $tracks = $shipment->getAllTracks();
                    if (!count($tracks)) {
                        $shipment->setShippingLabel('')->save();
                    } else {
                        $labelsContent = array();
                        $trackNumbers = array();
                        foreach ($tracks as $_track):
                            $trackNumbers[] = $_track->getTrackNumber();
                        endforeach;
                        if ($trackNumbers) {
                            /* @var $labelTransactions \StreamMarket\RoyalMailShipping\Model\ResourceModel\Transaction\Collection */
                            $labelTransactions = $this->transactionCollectionFactory->create();
                            $labelTransactions->addFieldToFIlter('shipment_number',
                                            array('in' => $trackNumbers))
                                    ->addFieldToFilter('has_error',
                                            \StreamMarket\RoyalMailShipping\Model\Transaction::HAS_ERROR_NO)
                                    ->addFieldToFilter('shipment_id',
                                            $shipment->getId())
                                    ->addFieldToFilter('request_type',
                                            \StreamMarket\RoyalMailShipping\Helper\Data::REQUEST_TYPE_PRINT_LABEL);
                            foreach ($labelTransactions as $labelTransaction):
                                try {
                                    $labelFileName = $labelTransaction->getLabelFilePath();
                                    if (file_exists($labelFileName)) {
                                        $compatiblePdf = $this->helper->getCompatibleFileName($labelFileName);
                                        if ($compatiblePdf = $this->helper->getCompatiblePdf($labelFileName,
                                                $compatiblePdf)) {
                                            $labelsContent[] = file_get_contents($compatiblePdf);
                                        } else {
                                            $this->logger->log('Unable to generate compatible pdf.');
                                        }
                                    }
                                } catch (\Exception $e) {
                                    $this->logger->error($e->getMessage());
                                }
                            endforeach;
                        }
                        if ($labelsContent) {
                            $outputPdf = $this->helper->combineLabelsPdf($labelsContent);
                            $shipment->setShippingLabel($outputPdf->render())->save();
                        }
                    }
                }
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }
    }

}
