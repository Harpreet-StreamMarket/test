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

use Magento\Framework\Setup\InstallSchemaInterface;

/**
 * Description of InstallSchema
 */
class InstallSchema implements InstallSchemaInterface
{

    /**
     * install tables
     *
     * @param \Magento\Framework\Setup\SchemaSetupInterface $setup
     * @param \Magento\Framework\Setup\ModuleContextInterface $context
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(\Magento\Framework\Setup\SchemaSetupInterface $setup,
            \Magento\Framework\Setup\ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        /**
         * Create table 'sm_royalmail_matrixrate'
         */
        if (!$installer->tableExists('sm_royalmail_matrixrate')) {
            $table = $installer->getConnection()->newTable(
                            $installer->getTable('sm_royalmail_matrixrate')
                    )
                    ->addColumn(
                            'pk', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                            null,
                            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                            'Primary key'
                    )->addColumn(
                            'website_id',
                            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null,
                            ['nullable' => false, 'default' => '0'],
                            'Website Id'
                    )->addColumn(
                            'dest_country_id',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 4,
                            ['nullable' => false, 'default' => '0'],
                            'Destination coutry ISO/2 or ISO/3 code'
                    )->addColumn(
                            'dest_region_id',
                            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null,
                            ['nullable' => false, 'default' => '0'],
                            'Destination Region Id'
                    )->addColumn(
                            'dest_city',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 30,
                            ['nullable' => false, 'default' => '*'],
                            'Destination City'
                    )->addColumn(
                            'dest_zip',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 10,
                            ['nullable' => false, 'default' => '*'],
                            'Destination Post Code (Zip)'
                    )->addColumn(
                            'dest_zip_to',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 10,
                            ['nullable' => false, 'default' => '*'],
                            'Destination Post Code (Zip)'
                    )->addColumn(
                            'condition_name',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 20,
                            ['nullable' => false], 'Rate Condition name'
                    )->addColumn(
                            'condition_from_value',
                            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                            '12,4',
                            ['nullable' => false, 'default' => '0.0000'],
                            'Rate condition from value'
                    )->addColumn(
                            'condition_to_value',
                            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                            '12,4',
                            ['nullable' => false, 'default' => '0.0000'],
                            'Rate condition to value'
                    )->addColumn(
                            'price',
                            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                            '12,4',
                            ['nullable' => false, 'default' => '0.0000'],
                            'Price'
                    )->addColumn(
                            'cost',
                            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                            '12,4',
                            ['nullable' => false, 'default' => '0.0000'], 'Cost'
                    )
                    ->addColumn(
                            'delivery_type',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255,
                            ['nullable' => false, 'default' => ''],
                            'Service Offering'
                    )
                    ->addColumn(
                            'delivery_type_code',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255,
                            ['nullable' => false, 'default' => ''],
                            'Service Offering Code'
                    )
                    ->addColumn(
                            'service_type_code',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255,
                            ['nullable' => false, 'default' => ''],
                            'Service Type Code'
                    )
                    ->addColumn(
                            'method_code',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255,
                            ['nullable' => false, 'default' => ''],
                            'Method Code'
                    )
                    ->addColumn(
                            'is_active',
                            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 1,
                            ['default' => 0], 'Is Active'
                    )
                    ->addColumn(
                            'created_at',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                            null, [], 'Created At'
                    )
                    ->addColumn(
                            'updated_at',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                            null, [], 'Updated At'
                    )
                    ->setComment('Royal Mail Rate Table');
            $installer->getConnection()->createTable($table);

            $installer->getConnection()->addIndex(
                    $installer->getTable('sm_royalmail_matrixrate'),
                    $setup->getIdxName(
                            $installer->getTable('sm_royalmail_matrixrate'),
                            ['website_id', 'dest_country_id', 'dest_region_id', 'dest_city', 'dest_zip', 'dest_zip_to', 'condition_name', 'condition_from_value', 'condition_to_value', 'delivery_type', 'delivery_type_code', 'service_type_code'],
                            \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                    ),
                    ['website_id', 'dest_country_id', 'dest_region_id', 'dest_city', 'dest_zip', 'dest_zip_to', 'condition_name', 'condition_from_value', 'condition_to_value', 'delivery_type', 'delivery_type_code', 'service_type_code'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            );
        }

        /**
         * Create table 'sm_royalmail_service_matrix'
         */
        if (!$installer->tableExists('sm_royalmail_service_matrix')) {
            $table = $installer->getConnection()->newTable(
                                    $installer->getTable('sm_royalmail_service_matrix')
                            )
                            ->addColumn(
                                    'id',
                                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                                    null,
                                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                                    'Primary key'
                            )->addColumn(
                            'service_type',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 10,
                            ['nullable => false'], 'Service Type'
                    )->addColumn(
                            'service_offering',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 10,
                            ['nullable => false'], 'Service Offering'
                    )->addColumn(
                            'service_format',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 10, [],
                            'Service Format'
                    )->addColumn(
                            'enhancement_type',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 10,
                            ['default' => ''], 'Service Enhancement type'
                    )->addColumn(
                            'signature',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 10, [],
                            'Signature Tracked products only'
                    )->addColumn(
                    'safe_place_available',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 1, [],
                    'Safe place'
            );
            $installer->getConnection()->createTable($table);
        }

        /**
         * Create table 'sm_royalmail_transactions'
         */
        if (!$installer->tableExists('sm_royalmail_transactions')) {
            $table = $installer->getConnection()
                            ->newTable(
                                    $installer->getTable('sm_royalmail_transactions')
                            )
                            ->addColumn(
                                    'id',
                                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                                    null,
                                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                                    'Primary key'
                            )->addColumn(
                            'shipment_id',
                            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null,
                            ['index' => true, 'unsigned' => true, 'nullable' => false],
                            'Shipment ID'
                    )->addColumn(
                            'order_id',
                            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null,
                            ['index' => true, 'unsigned' => true, 'nullable' => false],
                            'Order ID'
                    )->addColumn(
                            'transaction_id',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [],
                            'Transaction ID'
                    )->addColumn(
                            'request_type',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 30, [],
                            'Request Type'
                    )->addColumn(
                            'service_offering_code',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 30, [],
                            'Service Offering Code'
                    )->addColumn(
                            'status',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 20, [],
                            'Status'
                    )->addColumn(
                            'message',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null,
                            [], 'Message'
                    )->addColumn(
                            'shipment_number',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [],
                            'Shipment Number'
                    )->addColumn(
                            'label_file',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [],
                            'Label File'
                    )->addColumn(
                            'image1DBarcode',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [],
                            'Image 1D Barcode'
                    )->addColumn(
                            'image2DMatrix',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [],
                            'Image 2D Matrix'
                    )->addColumn(
                            'manifest_batch_number',
                            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null,
                            ['unsigned' => true], 'Manifest Batch Number'
                    )->addColumn(
                            'manifested_in_batch',
                            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null,
                            ['unsigned' => true], 'Manifested In Batch'
                    )->addColumn(
                            'reference',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [],
                            'Reference'
                    )->addColumn(
                            'request_xml',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, [],
                            'Request XML'
                    )->addColumn(
                            'has_error',
                            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 1,
                            ['nullable' => false, 'default' => 0], 'Has Error'
                    )->addColumn(
                            'created_at',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                            null, [], 'Created At'
                    )->addColumn(
                    'updated_at',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP, null, [],
                    'Updated At'
                    )
            ;
            $installer->getConnection()->createTable($table);
        }
        $installer->endSetup();
    }

}
