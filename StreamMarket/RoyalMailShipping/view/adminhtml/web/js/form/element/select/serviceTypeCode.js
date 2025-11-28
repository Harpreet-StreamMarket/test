/**
 * RoyalMailShipping by StreamMarket
 *
 * @category    StreamMarket
 * @package StreamMarket_RoyalMailShipping
 * @author  Product Development Team <support@StreamMarket.co.uk>
 * @license http://extensions.StreamMarket.co.uk/license
 *
 */
define([
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/select',
    'Magento_Ui/js/modal/modal'
], function (_, uiRegistry, select, modal) {
    'use strict';

    return select.extend({

        /**
         * On value change handler.
         *
         * @param {String} value
         */
        onUpdate: function (value) {
            this.updateServiceOfferings(value);
            return this._super();
        },
        updateServiceOfferings: function (serviceType) {
            var offeringSelect = $(uiRegistry.get('index = delivery_type_code').uid);
            for (var i = offeringSelect.options.length - 1; i > 0; i--) {
                offeringSelect.remove(i);
            }
            var serviceMatrix = JSON.parse(jQuery('#sm_service_matrix').attr('data'));
            $H(serviceMatrix).each(function (pair) {
                if (pair.key == serviceType) {
                    $H(pair.value).each(function (p) {
                        offeringSelect.options.add(new Option(p.value, p.key));
                    });
                }
            });
        },
    });
});