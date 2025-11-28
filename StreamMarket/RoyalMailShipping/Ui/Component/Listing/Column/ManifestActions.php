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

/**
 * Description of ManifestActions
 */
class ManifestActions extends Column
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

    /** Url path */
    const MANIFEST_URL_PATH_PRINT = 'smroyalmail/manifest/printDocument';
    const MANIFEST_URL_PATH_CREATE_PRINT = 'smroyalmail/manifest/generateDocument';

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
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData('name');
                if (isset($item['id'])) {
                    /* @var $transaction \StreamMarket\RoyalMailShipping\Model\Transaction */
                    $transaction = $this->transactionFactory->create();
                    $transaction->setData($item);
                    if (isset($item['label_file']) && $item['label_file']) {
                        $item[$name]['print'] = [
                            'href' => $this->storeManager->getStore()
                                    ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_LINK) . $transaction->getLabelFilePath(),
                            'label' => __('Print'),
                            'target' => '_blank'
                        ];
                    } elseif ($this->helper->isModuleEnabled()) {
                        $item[$name]['create_print'] = [
                            'href' => $this->urlBuilder->getUrl(self::MANIFEST_URL_PATH_CREATE_PRINT,
                                    ['batch_number' => $item['manifest_batch_number']]),
                            'label' => __('Create Print')
                        ];
                    }
                }
            }
        }

        return $dataSource;
    }

}
