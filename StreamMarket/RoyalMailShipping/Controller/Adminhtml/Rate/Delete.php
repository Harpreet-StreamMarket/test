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

namespace StreamMarket\RoyalMailShipping\Controller\Adminhtml\Rate;

use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Description of Save
 */
class Delete extends \Magento\Backend\App\Action
{

    /**
     * @var \StreamMarket\RoyalMailShipping\Helper\Data
     */
    private $helper;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var \StreamMarket\RoyalMailShipping\Model\Carrier\RateFactory
     */
    private $rateFactory;

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'StreamMarket_RoyalMailShipping::rates';

    public function __construct(\Magento\Backend\App\Action\Context $context,
            DataPersistorInterface $dataPersistor,
            \StreamMarket\RoyalMailShipping\Model\Carrier\RateFactory $rateFactory,
            \StreamMarket\RoyalMailShipping\Helper\Data $helper)
    {
        parent::__construct($context);
        $this->rateFactory = $rateFactory;
        $this->dataPersistor = $dataPersistor;
        $this->helper = $helper;
    }

    public function execute()
    {
		
        $data = $this->getRequest()->getPostValue();
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
            /* @var $rate \StreamMarket\RoyalMailShipping\Model\Carrier\Rate  */
		$rate = $this->rateFactory->create();

		$id = $this->getRequest()->getParam('rate_id');
            

            try {
                if ($id) {
				$rate->load($id);
				$rate->delete();
				if (!$rate->getId()) {
                    $this->messageManager->addErrorMessage(__('This rate no longer exists.'));
                }
            }
            }  catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e,
                        __('Something went wrong while saving the rate.'));
            }
        return $resultRedirect->setPath('*/*/');
    }
}
