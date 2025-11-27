<?php

namespace StreamMarket\RoyalMailShipping\Controller\Adminhtml\MassAction;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Filesystem\DirectoryList;

class Bulkshipmentrm extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
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
		protected $scopeConfig;

	private $shipmentCollectionFactory;

	 /**
     * @var FileFactory
     */
    private $fileFactory;
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
   // const ADMIN_RESOURCE = 'Magento_Sales::shipment';

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $collectionFactory,
		 \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		 \Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory $shipmentCollectionFactory,
		 \Magento\Framework\App\Response\Http\FileFactory $fileFactory
    ) {
        parent::__construct($context, $filter);
        $this->resultPageFactory = $resultPageFactory;
        $this->registry = $registry;
        $this->collectionFactory = $collectionFactory;
		$this->scopeConfig = $scopeConfig;
		$this->shipmentCollectionFactory = $shipmentCollectionFactory;
		$this->fileFactory = $fileFactory;
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
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$ghostScript_Path = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('carriers/smroyalmail/ghscript');
        $orderIds = $collection->getAllIds();
		$collection = $this->shipmentCollectionFactory->create();
        $collection->addFieldToFilter('order_id', ['in' => $orderIds]);
        $labelsContent = [];
		$ord_ids = implode(",", $orderIds);;

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
			$cmd = sprintf(
				'%s -q -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -sOutputFile=%s %s',
				escapeshellarg($ghostScript_Path),
				escapeshellarg($output_file),
				escapeshellarg($labels)
			);

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
			//return $resultPage;

    }
}
