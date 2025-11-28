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

namespace StreamMarket\RoyalMailShipping\Observer\System\Config;

/**
 * Description of SaveBefore
 */
class SaveBefore implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * @var \StreamMarket\RoyalMailShipping\Helper\Data
     */
    private $helper;
	
	private $curlrequest;

    public function __construct(
	\StreamMarket\RoyalMailShipping\Helper\Data $helper,
	\StreamMarket\RoyalMailShipping\Helper\CurlRequest $curlrequest,
            \Magento\Framework\Message\ManagerInterface $messageManager)
    {
        $this->helper = $helper;
        $this->messageManager = $messageManager;
		$this->curlrequest = $curlrequest;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $params = $observer->getEvent()->getControllerAction()->getRequest()->getParams();
        if (isset($params['section']) && $params['section'] == 'carriers') {
            if (!$this->helper->validate($params['groups']['smroyalmail']['fields']['product_key']['value'])) {
                $this->messageManager->addErrorMessage(__('Extension licence key is invalid for RoyalMail shipping module.'));
            }
        }
	}
}
