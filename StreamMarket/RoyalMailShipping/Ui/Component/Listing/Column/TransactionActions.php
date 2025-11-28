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

namespace StreamMarket\RoyalMailShipping\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;
use StreamMarket\RoyalMailShipping\Helper\Data;

/**
 * Description of ManifestActions
 */
class TransactionActions extends Column
{

    /**
     * @var \StreamMarket\RoyalMailShipping\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \StreamMarket\RoyalMailShipping\Model\TransactionFactory
     */
    private $transactionFactory;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    public function __construct(ContextInterface $context,
            UiComponentFactory $uiComponentFactory, UrlInterface $urlBuilder,
            \StreamMarket\RoyalMailShipping\Model\TransactionFactory $transactionFactory,
            \Magento\Store\Model\StoreManagerInterface $storeManager,
            \StreamMarket\RoyalMailShipping\Helper\Data $helper,
            array $components = array(), array $data = array())
    {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->urlBuilder = $urlBuilder;
        $this->transactionFactory = $transactionFactory;
        $this->storeManager = $storeManager;
        $this->helper = $helper;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $name = $this->getData('name');
            $isActive = $this->helper->isModuleEnabled();
            /* @var $transaction \StreamMarket\RoyalMailShipping\Model\Transaction */
            foreach ($dataSource['data']['items'] as & $item) {
                $transaction = $this->transactionFactory->create();
                $transaction->setData($item);
                if ($isActive && !$transaction->getHasError() && !in_array($transaction->getStatus(), [Data::STATUS_MANIFESTED, Data::STATUS_CANCELLED, Data::STATUS_MANIFESTED_PRINTED])  && $transaction->getRequestType() == \StreamMarket\RoyalMailShipping\Helper\Data::REQUEST_TYPE_CREATE_SHIPMENT) {
                    $item[$name]['cancel'] = [
                        'href' => $this->urlBuilder->getUrl('smroyalmail/transaction/cancelShipment',
                                ['transaction_id' => $item['id']]),
                        'onclick' => "return confirm('" . __('Are you sure?') . "')",
                        'label' => __('Cancel Shipment')
                    ];
                }
				if ($isActive && !$transaction->getHasError() && !in_array($transaction->getStatus(), [Data::STATUS_MANIFESTED, Data::STATUS_CANCELLED, Data::STATUS_MANIFESTED_PRINTED,Data::STATUS_HOLD])  && $transaction->getRequestType() == \StreamMarket\RoyalMailShipping\Helper\Data::REQUEST_TYPE_CREATE_SHIPMENT) {
                    $item[$name]['hold'] = [
                        'href' => $this->urlBuilder->getUrl('smroyalmail/transaction/holdShipment',
                                ['transaction_id' => $item['id']]),
                        'onclick' => "return confirm('" . __('Are you sure?') . "')",
                        'label' => __('Hold Shipment')
                    ];
                }
				if ($isActive && !$transaction->getHasError() && in_array($transaction->getStatus(), [Data::STATUS_HOLD])  && $transaction->getRequestType() == \StreamMarket\RoyalMailShipping\Helper\Data::REQUEST_TYPE_CREATE_SHIPMENT) {
                    $item[$name]['release'] = [
                        'href' => $this->urlBuilder->getUrl('smroyalmail/transaction/releaseShipment',
                                ['transaction_id' => $item['id']]),
                        'onclick' => "return confirm('" . __('Are you sure?') . "')",
                        'label' => __('Release Shipment')
                    ];
                }
				if ($isActive && !$transaction->getHasError() && in_array($transaction->getStatus(), [Data::STATUS_HOLD])  && $transaction->getRequestType() == \StreamMarket\RoyalMailShipping\Helper\Data::REQUEST_TYPE_CREATE_SHIPMENT) {
                    $item[$name]['release'] = [
                        'href' => $this->urlBuilder->getUrl('smroyalmail/transaction/releaseShipment',
                                ['transaction_id' => $item['id']]),
                        'onclick' => "return confirm('" . __('Are you sure?') . "')",
                        'label' => __('Release Shipment')
                    ];
                }
                if ($isActive && !$transaction->getHasError() && !in_array($transaction->getStatus(), [Data::STATUS_MANIFESTED, Data::STATUS_CANCELLED, Data::STATUS_MANIFESTED_PRINTED,Data::STATUS_HOLD,Data::STATUS_PICKED])  && $transaction->getRequestType() == \StreamMarket\RoyalMailShipping\Helper\Data::REQUEST_TYPE_CREATE_SHIPMENT) {
                    $item[$name]['picked'] = [
                        'href' => $this->urlBuilder->getUrl('smroyalmail/transaction/pickedShipment',
                                ['transaction_id' => $item['id']]),
                        'onclick' => "return confirm('" . __('Are you sure?') . "')",
                        'label' => __('Picked Shipment')
                    ];
                }
				if ($isActive && !$transaction->getHasError() && !in_array($transaction->getStatus(), [Data::STATUS_MANIFESTED, Data::STATUS_CANCELLED, Data::STATUS_MANIFESTED_PRINTED,Data::STATUS_HOLD,Data::STATUS_PICKED])  && $transaction->getRequestType() == \StreamMarket\RoyalMailShipping\Helper\Data::REQUEST_TYPE_CREATE_SHIPMENT) {
                    $item[$name]['reprint'] = [
                        'href' => $this->urlBuilder->getUrl('smroyalmail/transaction/reprintLabel',
                                ['transaction_id' => $item['id']]),
                        'onclick' => "return confirm('" . __('Are you sure?') . "')",
                        'label' => __('Regenerate Label')
                    ];
                }
                if (isset($item['label_file']) && $item['label_file']) {
                    $item[$name]['print'] = [
                        'href' => $this->storeManager->getStore()
                                ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_LINK) . $transaction->getLabelFilePath(),
                        'target' => '_blank',
                        'label' => __('Print Label')
                    ];
                }
            }
        }

        return $dataSource;
    }

}
