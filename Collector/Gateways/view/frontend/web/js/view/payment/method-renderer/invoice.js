define(
    [
        'Magento_Checkout/js/view/payment/default',
		'jquery',
        "mage/validation"
    ],
    function (Component, $) {
        'use strict';
 
        return Component.extend({
            defaults: {
                template: 'Collector_Gateways/payment/invoice',
				getssn: ''
            },
			getCode: function () {
                return 'collector_invoice';
            },
			initObservable: function () {
                this._super()
                    .observe('getssn');
                return this;
            },
			getData: function () {
				return {
					"method": 'collector_invoice',
					"additional_data": {
						'ssn': this.getssn(),
					}
				}
			},
			validate: function () {
                var form = 'form[data-role=invoice-form]';
                return $(form).validation() && $(form).validation('isValid');
            }
        });
    }
);