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

namespace StreamMarket\RoyalMailShipping\Model\System\Source;

use \Magento\Store\Model\StoreManagerInterface;

/**
 * Description of Websites
 */
class Websites implements \Magento\Framework\Data\OptionSourceInterface
{

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(StoreManagerInterface $storeManager)
    {

        $this->storeManager = $storeManager;
    }

    public function getWebsites()
    {
        return $this->storeManager->getWebsites();
    }

    public function toOptionArray()
    {
        $options[] = ['label' => '', 'value' => ''];
        $availableOptions = $this->getOptionArray();
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }

    public function getOptionArray()
    {
        $arr = [];
        foreach ($this->getWebsites() as $website):
            $arr[$website->getId()] = $website->getName();
        endforeach;
        return $arr;
    }

}
