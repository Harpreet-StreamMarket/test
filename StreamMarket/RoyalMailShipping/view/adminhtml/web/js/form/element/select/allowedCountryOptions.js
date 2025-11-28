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
        initialize: function () {
            this._super();
            return this;
        },
        /**
         * On value change handler.
         *
         * @param {String} value
         */
        onUpdate: function (value) {
            this.toggleCountryField(value);
            return this._super();
        },
        toggleCountryField: function (value) {
            var field1 = uiRegistry.get('index = dest_country_id');
            if (field1.visibleValue == value) {
                field1.enable();
            } else {
                field1.disable();
            }
        }
    });
});