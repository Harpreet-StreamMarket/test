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
class Save extends \Magento\Backend\App\Action
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

        if (!$this->helper->isModuleEnabled()) {
            $this->messageManager->addErrorMessage(__('Module is not active or licence key is invalid.'));
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;
        }
        if ($data) {
            if (isset($data['is_active']) && $data['is_active'] === 'true') {
                $data['is_active'] = \StreamMarket\RoyalMailShipping\Model\Carrier\Rate::ACTIVE_YES;
            }
            if (empty($data['pk'])) {
                $data['pk'] = null;
            }

            /* @var $rate \StreamMarket\RoyalMailShipping\Model\Carrier\Rate  */
            $rate = $this->rateFactory->create();

            $id = $this->getRequest()->getParam('pk');
            if ($id) {
                $rate->load($id);
                if (!$rate->getId()) {
                    $this->messageManager->addErrorMessage(__('This rate no longer exists.'));
                    return $resultRedirect->setPath('*/*/');
                }
            }

            try {
                $data['method_code'] =  $data['delivery_type_code'];
                if (isset($data['allowed_country'])) {
                    $allowAllCountry = $data['allowed_country'];
                    $country = array();
                    if ($allowAllCountry == 0) {
                        $country[] = 0;
                    } elseif ($allowAllCountry == 1) {
                        $country = $data['dest_country_id'];
                    }
                    foreach ($country as $c):
                        $rate = $this->rateFactory->create();
                        $rate->unsetData();
                        $rate->addData($data);
                        $rate->setData('dest_country_id', $c);
                        $rate->save();
                    endforeach;
                } else {
                    $rate->addData($data);
                    $rate->save();
                }
                $this->messageManager->addSuccessMessage(__('You saved the rate.'));
                $this->dataPersistor->clear('royalmail_rate');
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit',
                                    ['rate_id' => $rate->getId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addExceptionMessage($e->getPrevious() ?: $e);
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e,
                        __('Something went wrong while saving the rate.'));
            }

            $this->dataPersistor->set('royalmail_rate', $data);
            if ($id) {
                $resultRedirect->setPath('*/*/edit',
                        ['rate_id' => $this->getRequest()->getParam('pk')]);
            } else {
                $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            }
            return $resultRedirect;
        }
        return $resultRedirect->setPath('*/*/');
    }

}
