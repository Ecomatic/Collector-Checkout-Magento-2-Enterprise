define([
	'jquery'
], function ($, collectorajax) {
	document.addEventListener('collectorCheckoutCustomerUpdated', function(event){
		if (shouldUpdateCustomer){
			var param = {
				field1 : "ajax", 
				field2 : "updatecustomer",
				field3 : null
			};
			$.ajax({
				url: cajaxurl,
				data: param,
				type: "POST",
				dataType: 'json',
				beforeSend: function() {
					jQuery('body').addClass('is-suspended');
					window.collector.checkout.api.suspend();
				},					   
				success: function(data) {
					if(data.cart){
						jQuery('div.collector-cart').replaceWith(data.cart);
					}
				},
				complete: function() {
					jQuery('body').removeClass('is-suspended');
					window.collector.checkout.api.resume();
					require([
						'Magento_Customer/js/customer-data'
					], function (customerData) {
						var sections = ['cart'];
						customerData.invalidate(sections);
						customerData.reload(sections, true);
					});
				}
			});
		}
	});
	document.addEventListener('collectorCheckoutOrderValidationFailed', function(event){
	});
	document.addEventListener('collectorCheckoutLocked', function(event){
	});
	document.addEventListener('collectorCheckoutUnlocked', function(event){
	});
});