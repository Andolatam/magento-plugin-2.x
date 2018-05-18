<?php

namespace Improntus\Ando\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;

/**
 * Class InstallData
 *
 * @author Improntus <http://www.improntus.com>
 * @package Improntus\Ando\Setup
 */
class InstallData implements InstallDataInterface
{
    /**
     * @var CustomerSetupFactory
     */
    private $customerSetupFactory;

    /**
     * @var AttributeSetFactory
     */
    private $attributeSetFactory;

    /**
     * InstallData constructor.
     * @param CustomerSetupFactory $customerSetupFactory
     * @param AttributeSetFactory $attributeSetFactory
     */
    public function __construct(
        CustomerSetupFactory $customerSetupFactory,
        AttributeSetFactory $attributeSetFactory
    ) {
        $this->customerSetupFactory = $customerSetupFactory;
        $this->attributeSetFactory  = $attributeSetFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function install(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();

        $customerAddressSetup = $this->customerSetupFactory->create(['setup' => $setup]);

        $customerAddressEntity = $customerAddressSetup->getEavConfig()->getEntityType(
            \Magento\Customer\Api\AddressMetadataInterface::ENTITY_TYPE_ADDRESS
        );
        $attributeCustomerAddressSetId      = $customerAddressEntity->getDefaultAttributeSetId();
        $attributeCustomerAddressSet        = $this->attributeSetFactory->create();
        $attributeCustomerAddressGroupId    = $attributeCustomerAddressSet->getDefaultGroupId($attributeCustomerAddressSetId);

        $attributesAddressInfo = [
            'altura' => [
                'type' => 'int',
                'label' => 'Altura',
                'input' => 'text',
                'global'    => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible'   => true,
                'required'  => true,
                'user_defined' => true,
                'frontend_class'     => 'validate-number',
                'visible_on_front'   => true,
                'sort_order'  => 70,
                'position'    => 70,
                'system'      => 0,
            ],
            'observaciones' => [
                'type' => 'varchar',
                'label' => 'Observaciones',
                'input' => 'text',
                'global'    => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible'   => true,
                'required'  => false,
                'user_defined' => true,
                'visible_on_front'   => true,
                'sort_order'  => 1001,
                'position'    => 1001,
                'system'      => 0,
            ],

        ];

        foreach ($attributesAddressInfo as $attributeCode => $attributeParams)
        {
            $customerAddressSetup->addAttribute(
                'customer_address',
                $attributeCode,
                $attributeParams
            );
            $customerAddressAttribute = $customerAddressSetup->getEavConfig()->getAttribute(
                \Magento\Customer\Api\AddressMetadataInterface::ENTITY_TYPE_ADDRESS,
                $attributeCode
            )
                ->addData([
                    'attribute_set_id'   => $attributeCustomerAddressSetId,
                    'attribute_group_id' => $attributeCustomerAddressGroupId,
                    'used_in_forms'      =>
                        [
                            'customer_address_edit',
                            'adminhtml_customer_address'
                        ],
                ]);
            $customerAddressAttribute->save();
        }

        $setup->endSetup();
    }
}