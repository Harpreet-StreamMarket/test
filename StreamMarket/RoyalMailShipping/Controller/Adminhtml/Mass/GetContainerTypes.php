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
 * Description of GetContainerTypes
 */
class GetContainerTypes extends \Magento\Backend\App\Action
{

    /**
     * @var \StreamMarket\RoyalMailShipping\Model\Carrier
     */
    private $carrier;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $resultJsonFactory;

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Sales::shipment';

    public function __construct(
    \Magento\Backend\App\Action\Context $context,
            \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
            \StreamMarket\RoyalMailShipping\Model\Carrier $carrier
    )
    {
        parent::__construct($context);

        $this->resultJsonFactory = $resultJsonFactory;
        $this->carrier = $carrier;
    }

    /**
     * Edit Rate
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $serviceOfferingCode = $this->getRequest()->getPost('service_offering');
        $serviceType = $this->getRequest()->getPost('service_type');
        /** @var \Magento\Framework\Controller\Result\Json $result */
        $result = $this->resultJsonFactory->create();
        $result->setData(['success' => true, 'containers' => $this->carrier->getContainerTypesByOffering($serviceOfferingCode,
                    $serviceType)]);

        return $result;
    }

}
