/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent',
    'Magento_Checkout/js/model/shipping-rates-validator',
    'Magento_Checkout/js/model/shipping-rates-validation-rules',
    'Improntus_Ando/js/model/shipping-rates-validator',
    'Improntus_Ando/js/model/shipping-rates-validation-rules'
], function (
    Component,
    defaultShippingRatesValidator,
    defaultShippingRatesValidationRules,
    andoShippingRatesValidator,
    andoShippingRatesValidationRules
) {
    'use strict';

    defaultShippingRatesValidator.registerValidator('andomoto', andoShippingRatesValidator);
    defaultShippingRatesValidationRules.registerRules('andomoto', andoShippingRatesValidationRules);

    defaultShippingRatesValidator.registerValidator('andobicicleta', andoShippingRatesValidator);
    defaultShippingRatesValidationRules.registerRules('andobicicleta', andoShippingRatesValidationRules);

    return Component;
});
