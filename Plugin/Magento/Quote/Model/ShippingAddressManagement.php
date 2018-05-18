<?php

namespace Improntus\Ando\Plugin\Magento\Quote\Model;

/**
 * Class ShippingAddressManagement
 *
 * @author Improntus <http://www.improntus.com>
 * @package Improntus\Ando\Plugin\Magento\Quote\Model
 */
class ShippingAddressManagement
{
    /**
     * @var \Improntus\Ando\Helper\Data
     */
    protected $helper;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * ShippingAddressManagement constructor.
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Improntus\Ando\Helper\Data $helper
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Improntus\Ando\Helper\Data $helper
    )
    {
        $this->logger = $logger;
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Quote\Model\ShippingAddressManagement $subject
     * @param $cartId
     * @param \Magento\Quote\Api\Data\AddressInterface $address
     */
    public function beforeAssign(
        \Magento\Quote\Model\ShippingAddressManagement $subject,
        $cartId,
        \Magento\Quote\Api\Data\AddressInterface $address
    )
    {
        $extAttributes = $address->getExtensionAttributes();

        if (!empty($extAttributes))
        {
            $this->helper->transportFieldsFromExtensionAttributesToObject(
                $extAttributes,
                $address,
                'extra_checkout_shipping_address_fields'
            );
        }
    }
}