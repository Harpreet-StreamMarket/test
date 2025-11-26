<?php

/**
 * RoyalMailShipping by StreamMarket
 *
 * @category    StreamMarket
 * @package StreamMarket_RoyalMailShipping
 * @author  Product Development Team <support@streammarket.co.uk>
 * @license http://extensions.streammarket.co.uk/license
 *
 */

namespace StreamMarket\RoyalMailShipping\Controller\Adminhtml\Mass;

use Magento\Shipping\Model\Shipping\LabelGenerator;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Description of PrintAllLabels
 */
class PrintAllLabels extends \Magento\Backend\App\Action
{

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory
     */
    private $shipmentCollectionFactory;

    /**
     * @var FileFactory
     */
    private $fileFactory;

    /**
     * @var LabelGenerator
     */
    private $labelGenerator;

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Sales::shipment';

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory $shipmentCollectionFactory,
        FileFactory $fileFactory,
        LabelGenerator $labelGenerator
    ) {
        parent::__construct($context);
        $this->labelGenerator = $labelGenerator;
        $this->fileFactory = $fileFactory;
        $this->shipmentCollectionFactory = $shipmentCollectionFactory;
    }

    public function execute()
    {
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$ghostScript_Path = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('carriers/smroyalmail/ghscript');	
        
		$orderIds = $this->getRequest()->getParam('order_ids');
        if (!is_array($orderIds)) {
            $orderIds = explode(',', $orderIds);
        }
		
        $collection = $this->shipmentCollectionFactory->create();
        $collection->addFieldToFilter('order_id', ['in' => $orderIds]);
        $labelsContent = [];
		$ord_ids = $this->getRequest()->getParam('order_ids');

              if ($collection->getSize()) {
             $objectManager = \Magento\Framework\App\ObjectManager::getInstance();  
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
            /** @var \Magento\Sales\Model\Order\Shipment $shipment */
        $sqlSelect = "SELECT label_file FROM sm_royalmail_transactions WHERE order_id IN ($ord_ids) and request_type ='createShipment' and status ='Printed'";    
        $select = $connection->fetchAll($sqlSelect);
				
		$labels = "";
		//define('DS', DIRECTORY_SEPARATOR);
				foreach($select as $val){
					$label = $val['label_file'];
					$label_img = BP.DIRECTORY_SEPARATOR.'pub'.DIRECTORY_SEPARATOR.'media'.DIRECTORY_SEPARATOR.$label;
					$output_file = BP.DIRECTORY_SEPARATOR.'pub'.DIRECTORY_SEPARATOR.'media'.DIRECTORY_SEPARATOR.'sm_royalmail'.DIRECTORY_SEPARATOR.'ShippingLabels.pdf';
					$output_dir = BP.DIRECTORY_SEPARATOR.'pub'.DIRECTORY_SEPARATOR.'media';
					$labels .= $label_img.' ';
				}
		}
		if (shell_exec("$ghostScript_Path --version")){
			$cmd = "$ghostScript_Path -q -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -sOutputFile=$output_file $labels";
			$output = shell_exec($cmd);
			$filepath = BP.DIRECTORY_SEPARATOR.'pub'.DIRECTORY_SEPARATOR.'media'.DIRECTORY_SEPARATOR.'sm_royalmail'.DIRECTORY_SEPARATOR.'ShippingLabels.pdf';
			$downloadedFileName = 'ShippingLabels.pdf';
			$content['type'] = 'filename';
			$content['value'] = $filepath;
			$content['rm'] = 1;
			return $this->fileFactory->create($downloadedFileName, $content, DirectoryList::PUB);
		}else{
			$this->messageManager->addErrorMessage(__('Ghost script is not configured correctly.'));
			return $this->resultRedirectFactory->create()->setPath('sales/shipment/');
		}
	}
}
