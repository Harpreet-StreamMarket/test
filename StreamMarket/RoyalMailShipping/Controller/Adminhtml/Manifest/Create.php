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

/**
 * Create new manifest
 */
class Create extends \Magento\Backend\App\Action
{

    /**
     * @var \StreamMarket\RoyalMailShipping\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    private $resultPageFactory;

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'StreamMarket_RoyalMailShipping::manifests';

    public function __construct(\Magento\Backend\App\Action\Context $context,
            \Magento\Framework\View\Result\PageFactory $resultPageFactory,
            \StreamMarket\RoyalMailShipping\Helper\Data $helper)
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
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
        $resultPage->setActiveMenu('StreamMarket_RoyalMailShipping::manifests')
                ->addBreadcrumb(__('Royal Mail'), __('Royal Mail'))
                ->addBreadcrumb(__('Manifest'), __('Manifest'));
        return $resultPage;
    }

    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        if (!$this->helper->isModuleEnabled()) {
            $this->messageManager->addErrorMessage(__('Module is not active or Licence key is invalid.'));
            return $resultRedirect->setPath('*/*/index');
        }
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_initAction();
        $resultPage->addBreadcrumb(__('Create Manifest'), __('Create Manifest'));
        $resultPage->getConfig()->getTitle()->prepend(__('Manifests'));
        $resultPage->getConfig()->getTitle()
                ->prepend(__('Create Manifest'));

        return $resultPage;
    }

}
