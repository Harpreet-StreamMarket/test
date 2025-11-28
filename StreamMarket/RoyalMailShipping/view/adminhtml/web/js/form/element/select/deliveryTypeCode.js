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
            if (value == '') {
                return;
            }
            this.updateDeliveryTitle(value);
            return this._super();
        },
        updateDeliveryTitle: function (serviceOffering) {
            var serviceType = $(uiRegistry.get('index = service_type_code').uid);
            var offeringUi = uiRegistry.get('index = delivery_type_code');
            var element = $(offeringUi.uid);
            var serviceMatrix = JSON.parse(jQuery('#sm_service_matrix').attr('data'));
            var deliverType = $(uiRegistry.get('index = delivery_type').uid);
            var found = false;
            $H(serviceMatrix).each(function (pair) {
                if (pair.key == serviceType.value) {
                    $H(pair.value).each(function (p) {
                        if (p.key == serviceOffering) {
                            found = true;
                        }
                    });
                }
            });
            if (found) {
                for (var i = element.options.length - 1; i > 0; i--) {
                    if (element.options[i].value == serviceOffering) {
                        deliverType.value = element.options[i].innerHTML;
                        jQuery(deliverType).change();
                        return;
                    }
                }
            } else {
                alert('Selected service offering is not available for selected service type.');
                offeringUi.reset();
            }
        },
    });
});