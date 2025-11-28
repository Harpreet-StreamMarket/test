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

namespace StreamMarket\RoyalMailShipping\Controller\Adminhtml\Mass;

/**
 * Description of Ship
 */
class Ship extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
{

    /**
     * @var \StreamMarket\RoyalMailShipping\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    private $resultPageFactory;

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Sales::shipment';

    public function __construct(\Magento\Backend\App\Action\Context $context,
            \Magento\Ui\Component\MassAction\Filter $filter,
            \Magento\Framework\View\Result\PageFactory $resultPageFactory,
            \Magento\Framework\Registry $registry,
            \StreamMarket\RoyalMailShipping\Helper\Data $helper,
            \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $collectionFactory)
    {
        parent::__construct($context, $filter);
        $this->resultPageFactory = $resultPageFactory;
        $this->collectionFactory = $collectionFactory;
        $this->registry = $registry;
        $this->helper = $helper;
    }

    /**
     * Init actions
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magento_Sales::sales')
                ->addBreadcrumb(__('Royal Mail'), __('Royal Mail'))
                ->addBreadcrumb(__('Mass Shipment'), __('Mass Shipment'));
        return $resultPage;
    }

    /**
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
     * @return \Magento\Backend\Model\View\Result\Page | \Magento\Backend\Model\View\Result\Redirect
     */
    protected function massAction(\Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection)
    {
        /* @var $resultRedirect \Magento\Backend\Model\View\Result\Redirect  */
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$this->helper->isModuleEnabled()) {
            $this->messageManager->addErrorMessage(__('Module is not active or licence key is invalid.'));
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;
        }
        if (!$collection->getSize()) {
            $this->messageManager->addErrorMessage(__('Please select at least one order.'));
            return $resultRedirect->setPath('sales/order/index');
        }
        $limitOrderCount = 20;
        if ($collection->getSize() > $limitOrderCount) {
            $this->messageManager->addErrorMessage(__('You have selected too many orders. Please select at most %1 orders at a time.',
                            $limitOrderCount));
            /* @var $resultRedirect \Magento\Backend\Model\View\Result\Redirect  */
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('sales/order/index');
        }
        $selected = $collection->getAllIds();

        $this->registry->register('selected_order_ids', $selected);
        $resultPage = $this->_initAction();
        $resultPage->getConfig()->getTitle()->prepend(__('Ship order(s) with Royal Mail.'));
        return $resultPage;
    }

}
