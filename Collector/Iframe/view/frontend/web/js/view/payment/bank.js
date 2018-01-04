define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'bank',
                component: 'Collector_Iframe/js/view/payment/method-renderer/bank-method'
            }
        );
        return Component.extend({});
    }
);