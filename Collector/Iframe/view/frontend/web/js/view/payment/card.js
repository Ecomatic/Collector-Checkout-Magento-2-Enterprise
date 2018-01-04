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
                type: 'card',
                component: 'Collector_Iframe/js/view/payment/method-renderer/card-method'
            }
        );
        return Component.extend({});
    }
);