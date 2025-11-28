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

namespace StreamMarket\RoyalMailShipping\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Description of InstallData
 */
class InstallData implements InstallDataInterface
{

    /**
     * @var \StreamMarket\RoyalMailShipping\Model\Service\Matrix
     */
    private $serviceMatrixFactory;

    const SERVICE_MATRIX_CSV_FILE = 'service_matrix.csv';

    /**
     * @var \Magento\Framework\File\Csv
     */
    private $csvProcessor;

    /**
     * @param \Magento\Framework\Module\Dir\Reader $moduleReader
     */
    private $moduleReader;

    public function __construct(
    \Magento\Framework\Module\Dir\Reader $moduleReader,
            \Magento\Framework\File\Csv $csvProcessor,
            \StreamMarket\RoyalMailShipping\Model\Service\MatrixFactory $serviceMatrixFactory
    )
    {
        $this->moduleReader = $moduleReader;
        $this->csvProcessor = $csvProcessor;
        $this->serviceMatrixFactory = $serviceMatrixFactory;
    }

    public function getServiceMatrixDataFile()
    {
        $etcDir = $this->moduleReader->getModuleDir(
                \Magento\Framework\Module\Dir::MODULE_ETC_DIR,
                'StreamMarket_RoyalMailShipping'
        );
        return $etcDir . DIRECTORY_SEPARATOR . self::SERVICE_MATRIX_CSV_FILE;
    }

    public function install(ModuleDataSetupInterface $setup,
            ModuleContextInterface $context)
    {
        $serviceMatrixDataFile = $this->getServiceMatrixDataFile();
        $csvData = $this->csvProcessor->getData($serviceMatrixDataFile);
        $data = [];
        $header = null;
        foreach ($csvData as $row):
            if (!is_null($header)) {
                $data = array_combine($header, $row);
                $serviceMatrix = $this->serviceMatrixFactory->create();
                $serviceMatrix->addData($data)->save();
            } else {
                $header = $row;
            }
        endforeach;
    }

}
