<?php
namespace StreamMarket\RoyalMailShipping\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;

class UpdateOrderStatus implements DataPatchInterface
{
   /**
    * @var \Magento\Framework\Setup\ModuleDataSetupInterface
    */
   private $moduleDataSetup;

   public function __construct(
       \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
   ) {
       $this->moduleDataSetup = $moduleDataSetup;
   }

   /**
    * {@inheritdoc}
    */
   public function apply()
   {
       // Insert statuses
       // use insertOnDuplicate(), insertArray() etc here
       $this->moduleDataSetup->getConnection()->insertOnDuplicate(
           $this->moduleDataSetup->getTable('sales_order_status'),
           ['status' => 'shipping_label_generate_error', 'label' => 'Shipping label Generate Error']
       );

       //Bind status to state
       $states = [
           [
               'status'     => 'shipping_label_generate_error',
               'state'      => 'complete',
               'is_default' => 0,
			   'visible_on_front' => 1
           ],
		   [
               'status'     => 'shipping_label_generate_error',
               'state'      => 'processing',
               'is_default' => 0,
			   'visible_on_front' => 1
           ]
		   ];
       foreach ($states as $state) {
           $this->moduleDataSetup->getConnection()->insertOnDuplicate(
               $this->moduleDataSetup->getTable('sales_order_status_state'),
               $state
           );
       }
   }


   /**
    * {@inheritdoc}
    */
   public static function getDependencies()
   {
       return [];
   }

   /**
    * {@inheritdoc}
    */
   public function getAliases()
   {
       return [];
   }
}