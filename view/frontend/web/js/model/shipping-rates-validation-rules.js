/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([], function () {
    'use strict';

    return {
        /**
         * @return {Object}
         */
        getRules: function () {
            return {
                'postcode': {
                    'required': true
                },
                'country_id': {
                    'required': true
                },
                'city': {
                    'required': true
                },
                'street': {
                    'required': true
                },
                'altura': {
                    'required': true
                },
                'observaciones': {
                    'required': true
                },
                'firstname': {
                    'required': true
                },
                'lastname': {
                    'required': true
                },
                'telephone': {
                    'required': true
                }
            };
        }
    };
});
