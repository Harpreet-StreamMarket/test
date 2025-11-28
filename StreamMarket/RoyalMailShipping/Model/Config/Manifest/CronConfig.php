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

namespace StreamMarket\RoyalMailShipping\Model\Config\Manifest;

use Magento\Cron\Model\Config\Source\Frequency;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Cache\TypeListInterface;

/**
 * Description of CronConfig
 */
class CronConfig extends \Magento\Framework\App\Config\Value
{

    const CRON_MODEL_PATH = 'crontab/default/jobs/eod_menifest/run/model';
    const CRON_STRING_PATH = 'crontab/default/jobs/eod_menifest/schedule/cron_expr';

    /**
     * @var \Magento\Framework\App\Config\ValueFactory
     */
    protected $configValueFactory;

    /**
     * @var string
     */
    protected $runModelPath = '';

    public function __construct(
    \Magento\Framework\Model\Context $context,
            \Magento\Framework\Registry $registry, ScopeConfigInterface $config,
            TypeListInterface $cacheTypeList,
            \Magento\Framework\App\Config\ValueFactory $configValueFactory,
            \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
            \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
            $runModelPath = '', array $data = []
    )
    {
        $this->runModelPath = $runModelPath;
        $this->configValueFactory = $configValueFactory;
        parent::__construct($context, $registry, $config, $cacheTypeList,
                $resource, $resourceCollection, $data);
    }

    /**
     * Save config value
     * @param string $path
     * @param string $value
     */
    protected function _saveValue($path, $value)
    {
        $this->configValueFactory->create()->load(
                $path, 'path'
        )->setValue(
                $value
        )->setPath(
                $path
        )->save();
    }

    /**
     * After save shipping configuration value
     *
     * @return $this
     * @throws \Exception
     */
    public function afterSave()
    {
        $time = $this->getData('groups/smroyalmail/fields/manifest_time/value');
        $_frequency = $this->getData('groups/smroyalmail/fields/frequency/value');
        /* $cronExprArray = [Minute,Hour,Day of the Month,Month of the Year,Day of the Week] */
        $cronExprArray = [intval($time[1]), intval($time[0]),
            $_frequency == Frequency::CRON_MONTHLY ? '1' : '*', '*',
            $_frequency == Frequency::CRON_WEEKLY ? '1' : '*',
        ];

        try {
            $this->_saveValue(self::CRON_STRING_PATH, join(' ', $cronExprArray));
            $this->_saveValue(self::CRON_MODEL_PATH, $this->runModelPath);
        } catch (\Exception $ex) {
            throw new \Exception(__('Unable to save the manifest cron expression.'));
        }

        return parent::afterSave();
    }

}
