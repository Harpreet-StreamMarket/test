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

namespace StreamMarket\RoyalMailShipping\Controller\Adminhtml\Transaction;

/**
 * Description of GenerateLabel
 */
class GenerateLabel extends \Magento\Backend\App\Action
{

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \StreamMarket\RoyalMailShipping\Model\CarrierFactory
     */
    private $carrierFactory;

    /**
     * @var \StreamMarket\RoyalMailShipping\Model\TransactionFactory
     */
    private $transactionFactory;

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'StreamMarket_RoyalMailShipping::transactions';

    public function __construct(\Magento\Backend\App\Action\Context $context,
            \StreamMarket\RoyalMailShipping\Model\TransactionFactory $transactionFactory,
            \StreamMarket\RoyalMailShipping\Model\CarrierFactory $carrierFactory,
            \Psr\Log\LoggerInterface $logger)
    {
        parent::__construct($context);
        $this->transactionFactory = $transactionFactory;
        $this->carrierFactory = $carrierFactory;
        $this->logger = $logger;
    }

    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $transactionId = $this->getRequest()->getParam('transaction_id');
        try {
            /* @var $transaction \StreamMarket\RoyalMailShipping\Model\Transaction */
            $transaction = $this->transactionFactory->create();
            $transaction->load($transactionId);
            if ($transaction->getId() && $transaction->getStatus() == \StreamMarket\RoyalMailShipping\Helper\Data::STATUS_ALLOCATED) {
                $shipment = $transaction->getOrderShipment();
                if ($shipment && $shipment->getId()) {
                    $request = new \Magento\Framework\DataObject();
                    $request->setShipmentNumber($transaction->getShipmentNumber())
                            ->setOrderShipment($shipment)
                            ->setShipmentTransaction($transaction);
                    /* @var $carrier \StreamMarket\RoyalMailShipping\Model\Carrier */
                    $carrier = $this->carrierFactory->create();
                    $labelTransaction = $carrier->createShippingLabel($request);
                    if ($labelTransaction) {
                        if (!$labelTransaction->hasErrors()) {
                            $labelFileName = $labelTransaction->getLabelFilePath();
                            try {
                                if (file_exists($labelFileName)) {//merge label to shipment labels
                                    $labelsContent = array();
                                    $labelContent = $shipment->getShippingLabel();
                                    if ($labelContent) {
                                        $labelsContent[] = $labelContent;
                                    }
                                    $compatiblePdf = str_replace(basename($labelFileName),
                                                    '', $labelFileName) . 'v1_6_' . basename($labelFileName);
                                    if ($compatiblePdf = $carrier->getCompatiblePdf($labelFileName,
                                            $compatiblePdf)) {
                                        $labelsContent[] = file_get_contents($compatiblePdf);
                                        $outputPdf = $carrier->combineLabelsPdf($labelsContent);
                                        $shipment->setShippingLabel($outputPdf->render())->save();
                                    } else {
                                        $this->logger->error('Unable to generate compatible pdf.');
                                    }
                                }
                            } catch (\Exception $e) {
                                $this->logger->error($e->getMessage());
                            }
                            $this->messageManager->addSuccessMessage(__('The PDF generated successfully for the shipment #%1.',
                                            $request->getShipmentNumber()));
                        } else {
                            $this->messageManager->addErrorMessage((string) $labelTransaction->getMessage());
                        }
                    } else {
                        $this->messageManager->addErrorMessage(__('Insufficient parameter to create transaction.'));
                    }
                }
            } else {
                $this->messageManager->addErrorMessage(__('Record no longer exists to generate label.'));
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }

}
