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
class RateActions extends Column
{

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    public function __construct(ContextInterface $context,
            UiComponentFactory $uiComponentFactory, UrlInterface $urlBuilder,
            array $components = array(), array $data = array())
    {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->urlBuilder = $urlBuilder;
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
                if (isset($item['pk'])) {
                    $item[$name]['create_print'] = [
                        'href' => $this->urlBuilder->getUrl('smroyalmail/rate/edit',
                                ['rate_id' => $item['pk']]),
                        'label' => __('Edit')
                    ];
                }
            }
        }

        return $dataSource;
    }

}
