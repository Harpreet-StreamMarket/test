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

namespace StreamMarket\RoyalMailShipping\Controller\Adminhtml\Manifest;

use StreamMarket\RoyalMailShipping\Helper\Data;

/**
 * Description of GenerateDocument
 */
class GenerateDocument extends \Magento\Backend\App\Action
{

    /**
     * @var \StreamMarket\RoyalMailShipping\Model\CarrierFactory
     */
    private $carrierFactory;

    /**
     * @var \StreamMarket\RoyalMailShipping\Model\ResourceModel\Transaction\CollectionFactory
     */
    private $transactionCollectionFactory;

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'StreamMarket_RoyalMailShipping::manifests';

    public function __construct(\Magento\Backend\App\Action\Context $context,
            \StreamMarket\RoyalMailShipping\Model\ResourceModel\Transaction\CollectionFactory $transactionCollectionFactory,
            \StreamMarket\RoyalMailShipping\Model\CarrierFactory $carrierFactory)
    {
        parent::__construct($context);
        $this->transactionCollectionFactory = $transactionCollectionFactory;
        $this->carrierFactory = $carrierFactory;
    }

    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $batchNumber = trim($this->getRequest()->getParam('batch_number'));
        if (!$batchNumber) {
            $this->messageManager->addErrorMessage(__('Invalid manifest batch number.'));
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;
        }
        try {
            /* @var $transactions \StreamMarket\RoyalMailShipping\Model\ResourceModel\Transaction\Collection */
            $transactions = $this->transactionCollectionFactory->create();
            $transactions->addFieldToFilter('request_type',
                            Data::REQUEST_TYPE_CREATE_MANIFEST)
                    ->addFieldToFIlter('has_error',
                            \StreamMarket\RoyalMailShipping\Model\Transaction::HAS_ERROR_NO)
                    ->addFieldToFilter('manifest_batch_number', $batchNumber);
            if ($transactions->count()) {
                $manifest = $transactions->getFirstItem();
                if ($manifest->getStatus() == Data::STATUS_MANIFESTED) {
                    $request = new \Magento\Framework\DataObject();
                    $request->setManifestBatchNumber($batchNumber);
                    /* @var $carrier \StreamMarket\RoyalMailShipping\Model\Carrier */
                    $carrier = $this->carrierFactory->create();
                    $transaction = $carrier->printManifest($request);
                    if ($transaction && $transaction->hasErrors()) {
                        $this->messageManager->addErrorMessage($transaction->getMessage());
                    } else {

                        $this->messageManager->addSuccessMessage(__("Manifest print generated successfully for batch # %1.",
                                        $transaction->getManifestBatchNumber()));
                    }
                } elseif ($manifest->getStatus() == Data::STATUS_MANIFESTED_PRINTED) {
                    $this->messageManager->addErrorMessage(__("Manifest print already generated."));
                } else {
                    $this->messageManager->addErrorMessage(__("Invalid record."));
                }
            } else {
                $this->messageManager->addErrorMessage(__('Invalid manifest batch number.'));
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }

}
