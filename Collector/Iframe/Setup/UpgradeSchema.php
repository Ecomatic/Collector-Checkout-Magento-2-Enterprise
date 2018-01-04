<?php
/**
 * A Magento 2 module named Collector/Iframe
 * Copyright (C) 2017 Collector
 * 
 * This file is part of Collector/Iframe.
 * 
 * Collector/Iframe is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Collector\Iframe\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{

    /**
     * {@inheritdoc}
     */
    public function upgrade(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
		echo "running";
		try {
			$setup->startSetup();
			if (version_compare($context->getVersion(), "1.0.0", "<")) {
			//Your upgrade script
			}
			
			$table = $setup->getTable('quote');
				
			$columns = [
				'collector_btype' => [
					'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					'length' => '255',
					'nullable' => true,
					'comment' => 'Collector Customer Type',
				],
				'collector_private_id' => [
					'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					'length' => '255',
					'nullable' => true,
					'comment' => 'Collector ID',
				],
			];

			$connection = $setup->getConnection();
			foreach ($columns as $name => $definition) {
				$connection->addColumn($table, $name, $definition);
			}
			$setup->endSetup();
		}
		catch (\Exception $e){
			echo $e->getMessage();
		}
    }
}
