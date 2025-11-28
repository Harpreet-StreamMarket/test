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

namespace StreamMarket\RoyalMailShipping\Block\Adminhtml\Order\View\Tab;

/**
 * Description of Transactions
 */
class Transactions extends \Magento\Framework\View\Element\Text\ListText implements
\Magento\Backend\Block\Widget\Tab\TabInterface
{

    /**
     * @var \StreamMarket\RoyalMailShipping\Model\ResourceModel\Transaction\Collection
     */
    private $transactionCollection;

    public function __construct(\Magento\Framework\View\Element\Context $context,
            \StreamMarket\RoyalMailShipping\Model\ResourceModel\Transaction\CollectionFactory $transactionCollectionFactory,
            array $data = array())
    {
        parent::__construct($context, $data);
        $this->transactionCollection = $transactionCollectionFactory->create();
        $this->transactionCollection->addFieldToFilter('main_table.order_id',
                $this->getRequest()->getParam('order_id'));
    }

    public function canShowTab()
    {
        return $this->transactionCollection->getSize();
    }

    public function getTabLabel()
    {
        return __('Royal Mail Transactions');
    }

    public function getTabTitle()
    {
        return __('Royal Mail Transactions');
    }

    public function isHidden()
    {
        return false;
    }

}
