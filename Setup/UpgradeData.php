<?php

namespace Improntus\Ando\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Eav\Setup\EavSetupFactory;

/**
 * Class UpgradeData
 *
 * @author Improntus <http://www.improntus.com>
 * @package Improntus\Ando\Setup
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var EavSetupFactory
     */
    private $_eavSetupFactory;

    /**
     * InstallData constructor.
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->_eavSetupFactory = $eavSetupFactory;
    }

    /**
     * Upgrades data for a module
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $eavSetup = $this->_eavSetupFactory->create(['setup' => $setup]);

        if (version_compare($context->getVersion(), '1.0.1', '<'))
        {
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'alto',
                [
                    'frontend'  => '',
                    'label'     => 'Alto',
                    'input'     => 'text',
                    'class'     => '',
                    'global'    => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible'   => true,
                    'required'  => true,
                    'user_defined' => false,
                    'default'   => '',
                    'apply_to'  => '',
                    'visible_on_front'        => false,
                    'is_used_in_grid'         => false,
                    'is_visible_in_grid'      => false,
                    'is_filterable_in_grid'   => false,
                    'used_in_product_listing' => true
                ]
            );

            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'largo',
                [
                    'frontend'  => '',
                    'label'     => 'Largo',
                    'input'     => 'text',
                    'class'     => '',
                    'global'    => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible'   => true,
                    'required'  => true,
                    'user_defined' => false,
                    'default'   => '',
                    'apply_to'  => '',
                    'visible_on_front'        => false,
                    'is_used_in_grid'         => false,
                    'is_visible_in_grid'      => false,
                    'is_filterable_in_grid'   => false,
                    'used_in_product_listing' => true
                ]
            );

            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'ancho',
                [
                    'frontend'  => '',
                    'label'     => 'Ancho',
                    'input'     => 'text',
                    'class'     => '',
                    'global'    => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible'   => true,
                    'required'  => true,
                    'user_defined' => false,
                    'default'   => '',
                    'apply_to'  => '',
                    'visible_on_front'        => false,
                    'is_used_in_grid'         => false,
                    'is_visible_in_grid'      => false,
                    'is_filterable_in_grid'   => false,
                    'used_in_product_listing' => true
                ]
            );
        }

        $setup->endSetup();
    }
}
