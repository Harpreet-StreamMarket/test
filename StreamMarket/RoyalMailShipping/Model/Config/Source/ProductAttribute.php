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

namespace StreamMarket\RoyalMailShipping\Model\Config\Source;

use Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class ProductAttribute
 * @package Mageplaza\Shopbybrand\Model\Config\Source
 */
class ProductAttribute implements OptionSourceInterface
{
    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * ProductAttribute constructor.
     *
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(CollectionFactory $collectionFactory)
    {
        $this->_collectionFactory = $collectionFactory;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        /** @var Collection $attributes */
        $attributes = $this->_collectionFactory->create()->addVisibleFilter();
        $arrAttribute = [
            [
                'label' => __('-- Please select --'),
                'value' => '',
            ],
        ];

        foreach ($attributes as $attribute) {
                $arrAttribute[] = [
                    'label' => $attribute->getFrontendLabel(),
                    'value' => $attribute->getAttributeCode()
                ];
           
        }

        return $arrAttribute;
    }
}
