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
                type: 'campaign',
                component: 'Collector_Iframe/js/view/payment/method-renderer/campaign-method'
            }
        );
        return Component.extend({});
    }
);