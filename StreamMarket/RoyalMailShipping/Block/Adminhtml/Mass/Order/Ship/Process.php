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

namespace StreamMarket\RoyalMailShipping\Block\Adminhtml\Mass\Order\Ship;

/**
 * Description of Process
 */
class Process extends \Magento\Backend\Block\Template
{

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    public function __construct(\Magento\Backend\Block\Template\Context $context,
            \Magento\Framework\Registry $registry, array $data = array())
    {
        parent::__construct($context, $data);
        $this->registry = $registry;
    }


}
