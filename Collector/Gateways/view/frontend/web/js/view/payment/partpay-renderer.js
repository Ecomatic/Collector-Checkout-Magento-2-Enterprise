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
                type: 'collector_partpay',
                component: 'Collector_Gateways/js/view/payment/method-renderer/partpay'
            }
        );
        return Component.extend({});
    }
);