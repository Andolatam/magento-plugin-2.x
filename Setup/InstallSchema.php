<?php

namespace Improntus\Ando\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * Class InstallSchema
 *
 * @author Improntus <http://www.improntus.com>
 * @package Improntus\Ando\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $columnaAltura = [
            'type'    => Table::TYPE_INTEGER,
            'nullable'=> true,
            'comment' => 'Altura de la calle de la dirección del cliente',
            'default' => null
        ];

        $columnaObservaciones = [
            'type'    => Table::TYPE_TEXT,
            'nullable'=> true,
            'comment' => 'Observaciones del cliente',
            'default' => null
        ];

        $columnaAndoQuoteId = [
            'type'    => Table::TYPE_INTEGER,
            'nullable'=> true,
            'comment' => 'Quote Id de cotizacion ando',
            'default' => null
        ];

        if (!$installer->getConnection()->tableColumnExists($installer->getTable('sales_order_address'), 'altura'))
        {
            $installer->getConnection()->addColumn($installer->getTable('sales_order_address'), 'altura', $columnaAltura);
        }

        if (!$installer->getConnection()->tableColumnExists($installer->getTable('quote_address'), 'altura'))
        {
            $installer->getConnection()->addColumn($installer->getTable('quote_address'), 'altura', $columnaAltura);
        }

        if (!$installer->getConnection()->tableColumnExists($installer->getTable('sales_order_address'), 'observaciones'))
        {
            $installer->getConnection()->addColumn($installer->getTable('sales_order_address'), 'observaciones', $columnaObservaciones);
        }

        if (!$installer->getConnection()->tableColumnExists($installer->getTable('quote_address'), 'observaciones'))
        {
            $installer->getConnection()->addColumn($installer->getTable('quote_address'), 'observaciones', $columnaObservaciones);
        }

        if (!$installer->getConnection()->tableColumnExists($installer->getTable('quote'), 'ando_quote_id'))
        {
            $installer->getConnection()->addColumn($installer->getTable('quote'), 'ando_quote_id', $columnaAndoQuoteId);
        }

        if (!$installer->getConnection()->tableColumnExists($installer->getTable('sales_order'), 'ando_quote_id'))
        {
            $installer->getConnection()->addColumn($installer->getTable('sales_order'), 'ando_quote_id', $columnaAndoQuoteId);
        }

        $installer->endSetup();
    }
}