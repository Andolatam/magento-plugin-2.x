var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/action/set-billing-address': {
                'Improntus_Ando/js/action/set-billing-address-mixin': true
            },
            'Magento_Checkout/js/action/set-shipping-information': {
                'Improntus_Ando/js/action/set-shipping-information-mixin': true
            },
            'Magento_Checkout/js/action/create-shipping-address': {
                'Improntus_Ando/js/action/create-shipping-address-mixin': true
            }
        }
    },
    map: {
        '*': {
            'Magento_Checkout/template/shipping-address/address-renderer/default':
                'Improntus_Ando/template/shipping-address/address-renderer/default',
            'Magento_Checkout/template/shipping-information/address-renderer/default':
                'Improntus_Ando/template/shipping-information/address-renderer/default',
            'Magento_Checkout/js/view/shipping-address/address-renderer/default':
                'Improntus_Ando/js/view/shipping-address/address-renderer/default',
            'Magento_Checkout/js/view/billing-address':
                'Improntus_Ando/js/view/billing-address',
            'Magento_Checkout/js/view/shipping-information/address-renderer/default':
                'Improntus_Ando/js/view/shipping-information/address-renderer/default',
            'Magento_Checkout/template/billing-address':
                'Improntus_Ando/template/billing-address',
            'Magento_Checkout/template/billing-address/details':
                'Improntus_Ando/template/billing-address/details',
            'Magento_Checkout/template/billing-address/form':
                'Improntus_Ando/template/billing-address/form',
            'Magento_Checkout/template/billing-address/list':
                'Improntus_Ando/template/billing-address/list'
        }
    }
};