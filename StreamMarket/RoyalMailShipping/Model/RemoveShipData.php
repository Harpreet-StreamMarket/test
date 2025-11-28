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

namespace StreamMarket\RoyalMailShipping\Model;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Filesystem\Driver\File;

class RemoveShipData
{
	const LABELS_ENABLE = 'carriers/smroyalmail/labels_enable';
	
	const LABELS_REMOVE = 'carriers/smroyalmail/labels_remove';
	/**
     * @var \StreamMarket\RoyalMailShipping\Model\TransactionFactory
     */
    private $transactionFactory;
	
	/**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

	
	public function __construct(
            \StreamMarket\RoyalMailShipping\Model\TransactionFactory $transactionFactory,
			\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
			File $fileDriver
            )
    {
        $this->transactionFactory = $transactionFactory;
		$this->scopeConfig = $scopeConfig;
		$this->fileDriver = $fileDriver;
    }

    public function execute()
    {
			$store_config = $this->getConfig(self::XML_CONFIG_PATH, 1);
			$store_config_status = $this->getConfig(self::LABELS_ENABLE, 1);
			if($store_config_status == 1 && $store_config != ''){
			 $current_date = date('Y-m-d h:i:s');
			 $days_ago = date('Y-m-d 00:00:00', strtotime('-'.$store_config .'days', strtotime($current_date)));
			 $transactions = $this->transactionFactory->create()->getCollection()->addFieldToSelect('id')->addFieldToSelect('label_file')->addFieldToSelect('created_at');
			 $transactions->addFieldToFilter('created_at', ['lteq' => $days_ago]);
			 $data = $transactions->getData();
			 foreach($data as $shipdata){
			 $label_file = $shipdata['label_file'];
			 $id = $shipdata['id'];
			 $file_to_delete = BP.'/media'.$label_file;
			 $this->deleteRows($id);
			if(file_exists($file_to_delete)){
				$this->fileDriver->deleteFile($file_to_delete);
			}
		}
		}else{
			echo "Enable Remove Lables";
			
		}
	}
	
	public function deleteRows($entity_id){
			$transactions = $this->transactionFactory->create()->load($entity_id);
			$transactions->delete();
	}
	
	public function getConfig($path, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

}
