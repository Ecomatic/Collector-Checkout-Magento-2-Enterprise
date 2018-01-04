<?php
namespace Collector\Gateways\Setup;
 
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
 
class InstallSchema implements InstallSchemaInterface{
	public function install(SchemaSetupInterface $setup, ModuleContextInterface $context){
		try {
			$installer = $setup;

			$installer->startSetup();

			$quoteAddressTable = 'quote_address';
			$quoteTable = 'quote';
			$orderTable = 'sales_order';
			$invoiceTable = 'sales_invoice';
			$creditmemoTable = 'sales_creditmemo';
			
			//Setup two columns for quote, quote_address and order
			//Quote address tables
			$setup->getConnection()
				->addColumn(
					$setup->getTable($quoteAddressTable),
					'fee_amount',
					[
						'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
						'10,2',
						'default' => 0.00,
						'nullable' => true,
						'comment' =>'Fee Amount'
					]
				);
			$setup->getConnection()
				->addColumn(
					$setup->getTable($quoteAddressTable),
					'base_fee_amount',
					[
						'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
						'10,2',
						'default' => 0.00,
						'nullable' => true,
						'comment' =>'Base Fee Amount'
					]
				);
			//Order tables
			$setup->getConnection()
				->addColumn(
					$setup->getTable($orderTable),
					'fee_amount',
					[
						'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
						'10,2',
						'default' => 0.00,
						'nullable' => true,
						'comment' =>'Fee Amount'
					]
				);

			$setup->getConnection()
				->addColumn(
					$setup->getTable($orderTable),
					'base_fee_amount',
					[
						'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
						'10,2',
						'default' => 0.00,
						'nullable' => true,
						'comment' =>'Base Fee Amount'

					]
				);

			$setup->getConnection()
				->addColumn(
					$setup->getTable($orderTable),
					'fee_amount_refunded',
					[
						'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
						'10,2',
						'default' => 0.00,
						'nullable' => true,
						'comment' =>'Base Fee Amount Refunded'
					]
				);
			$setup->getConnection()
				->addColumn(
					$setup->getTable($orderTable),
					'base_fee_amount_refunded',
					[
						'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
						'10,2',
						'default' => 0.00,
						'nullable' => true,
						'comment' =>'Base Fee Amount Refunded'
					]
				);
			$setup->getConnection()
				->addColumn(
					$setup->getTable($orderTable),
					'fee_amount_invoiced',
					[
						'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
						'10,2',
						'default' => 0.00,
						'nullable' => true,
						'comment' =>'Fee Amount Invoiced'
					]
				);
			$setup->getConnection()
				->addColumn(
					$setup->getTable($orderTable),
					'base_fee_amount_invoiced',
					[
						'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
						'10,2',
						'default' => 0.00,
						'nullable' => true,
						'comment' =>'Base Fee Amount Invoiced'
					]
				);
			//Invoice tables
			$setup->getConnection()
				->addColumn(
					$setup->getTable($invoiceTable),
					'fee_amount',
					[
						'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
						'10,2',
						'default' => 0.00,
						'nullable' => true,
						'comment' =>'Fee Amount'

					]
				);
			$setup->getConnection()
				->addColumn(
					$setup->getTable($invoiceTable),
					'base_fee_amount',
					[
						'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
						'10,2',
						'default' => 0.00,
						'nullable' => true,
						'comment' =>'Base Fee Amount'

					]
				);
			//Credit memo tables
			$setup->getConnection()
				->addColumn(
					$setup->getTable($creditmemoTable),
					'fee_amount',
					[
						'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
						'10,2',
						'default' => 0.00,
						'nullable' => true,
						'comment' =>'Fee Amount'

					]
				);
			$setup->getConnection()
				->addColumn(
					$setup->getTable($creditmemoTable),
					'base_fee_amount',
					[
						'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
						'10,2',
						'default' => 0.00,
						'nullable' => true,
						'comment' =>'Base Fee Amount'

					]
				);
			
			
			$table = $installer->getTable('sales_order');
			
			$columns = [
				'collector_invoice_id' => [
					'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					'length' => '255',
					'nullable' => true,
					'comment' => 'Collector Invoice ID',
				],
				'collector_ssn' => [
					'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					'length' => '255',
					'nullable' => true,
					'comment' => 'Collector ssn',
				],
			];

			$connection = $installer->getConnection();
			foreach ($columns as $name => $definition) {
				$connection->addColumn($table, $name, $definition);
			}
			
			$table = $installer->getTable('sales_creditmemo');
			
			$columns = [
				'collector_refunded' => [
					'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					'length' => '255',
					'nullable' => false,
					'default' => '0',
					'comment' => 'Refund Sent To Collector',
				]
			];
			
			$connection = $installer->getConnection();
			foreach ($columns as $name => $definition) {
				$connection->addColumn($table, $name, $definition);
			}

			$installer->endSetup();
		}
		catch(Exception $e){}
	}
}
?>