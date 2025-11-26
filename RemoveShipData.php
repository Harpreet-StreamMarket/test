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
			\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
            )
    {
        $this->transactionFactory = $transactionFactory;
		$this->scopeConfig = $scopeConfig;
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
			 $allowedDir = BP . '/media/';

			foreach ($data as $shipdata) {
				$labelFile = $shipdata['label_file'] ?? '';
				$id = $shipdata['id'] ?? null;

				if (!$labelFile || !$id) {
					continue;
				}

				// Call the safe delete helper
				$this->safeDeleteFile($labelFile, $allowedDir);

				// Delete the DB row
				$this->deleteRows($id);
			}

		}else{
			echo "Enable Remove Lables";
			
		}
	}
	
	protected function safeDeleteFile(string $filePath, string $allowedDir): bool
	{
		// Sanitize file name
		$fileName = basename($filePath);

		// Construct full path
		$fullPath = rtrim($allowedDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $fileName;

		// Ensure file exists and is inside allowed directory
		if (!is_file($fullPath)) {
			return false; // nothing to delete
		}

		$realPath = realpath($fullPath);
		$realBase = realpath($allowedDir);

		if ($realPath === false || strpos($realPath, $realBase) !== 0) {
			return false; // outside allowed dir, do not delete
		}

		// Now delete safely
		return unlink($realPath);
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
