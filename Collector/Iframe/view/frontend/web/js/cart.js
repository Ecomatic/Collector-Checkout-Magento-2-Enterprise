define([
	'jquery'
], function ($, collectorajax) {
	return {
		call:function(ajaxUrl){
			$(document).on('click', '.col-inc', function() {
				var param = {
					field1 : "ajax", 
					field2 : "inc",
					field3 : this.id
				};
				var qty = 'qty_' + (this.id).split("_")[1];
				var sum = 'sum_' + (this.id).split("_")[1];
				var price = 'price_' + (this.id).split("_")[1];
				$.ajax({
					url: ajaxUrl,
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
			});
			$(document).on('click', '.col-sub', function() {
				var param = {
					field1 : "ajax", 
					field2 : "sub",
					field3 : this.id
				};
				var qty = 'qty_' + (this.id).split("_")[1];
				var sum = 'sum_' + (this.id).split("_")[1];
				var price = 'price_' + (this.id).split("_")[1];
				$.ajax({
					url: ajaxUrl,
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
			});
			$(document).on('click', '.newsletter', function(){
				var param = {
					field1 : "ajax",
					field2 : "newsletter",
					field3 : document.getElementById('newsletter-checkbox').checked
				};
				$.ajax({
					url: ajaxUrl,
					data: param,
					type: "POST",
					dataType: 'json',
					beforeSend: function(){
						jQuery('body').addClass('is-suspended');
						window.collector.checkout.api.suspend();
					},
					success: function(data){
						
					},
					complete: function(){
						jQuery('body').removeClass('is-suspended');
						window.collector.checkout.api.resume();
					}
				});
			});
			$(document).on('click', '.col-del', function() {
				var param = {
					field1 : "ajax",
					field2 : "del",
					field3 : this.id
				};
				$.ajax({
					url: ajaxUrl,
					data: param,
					type: "POST",
					dataType: 'json',
					beforeSend: function() {
						jQuery('body').addClass('is-suspended');
						window.collector.checkout.api.suspend();
					},					   
					success: function(data) {
						if (data == "redirect"){
							require([
							'Magento_Customer/js/customer-data'
						], function (customerData) {
							var sections = ['cart'];
							customerData.invalidate(sections);
							customerData.reload(sections, true);
						});
							window.location.href = window.location.protocol + "//" + window.location.host + "/";
						}
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
			});
			$(document).on('click', '.col-radio', function() {
				var param = {
					field1 : "ajax", 
					field2 : "radio",
					field3 : this.id
				};
				$.ajax({
					url: ajaxUrl,
					data: param,
					type: "POST",
					dataType: 'json',
					beforeSend: function() {
						// Suspend the Checkout, showing a spinner...
						jQuery('body').addClass('is-suspended');
						window.collector.checkout.api.suspend();
					},
					success:function(data){
						if(data.cart){
							jQuery('div.collector-cart').replaceWith(data.cart);
						}
						if(data.checkout){
							jQuery('div.collector-checkout').replaceWith(data.checkout);
						}
					},
					complete: function() {
						// ... and finally resume the Checkout after the backend call is completed to update the checkout
						jQuery('body').removeClass('is-suspended');
						window.collector.checkout.api.resume();
					},
				});
			});
			$(document).on('click', '.col-codeButton', function(){
				var param = {
					field1 : "ajax",
					field2 : "submit",
					field3 : document.getElementById("col-code").value
				};
				$.ajax({
					url: ajaxUrl,
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
			});
			$(document).on('click', '#businesstypes a', function(e) {
				e.preventDefault();
				var ctype = jQuery(this).attr('id');
				jQuery.ajax({
					url:ajaxUrl,
					type:'POST',
					dataType: 'json',
					data: {
						field1 : "ajax", 
						field2 : "btype",
						field3 : ctype
					},
					beforeSend: function() {
						// Suspend the Checkout, showing a spinner...
						jQuery('body').addClass('is-suspended');
						window.collector.checkout.api.suspend();
					},
					success:function(data){
						if(data.cart){
							jQuery('div.collector-cart').replaceWith(data.cart);
						}
						if(data.checkout){
							jQuery('div.collector-checkout').replaceWith(data.checkout);
						}
						if (ctype == "b2b"){
							jQuery("#b2c").addClass("col-inactive");
							jQuery("#b2c").removeClass("col-active");
							jQuery("#b2b").addClass("col-active");
							jQuery("#b2b").removeClass("col-inactive");
						}
						else if (ctype == "b2c"){
							jQuery("#b2b").addClass("col-inactive");
							jQuery("#b2b").removeClass("col-active");
							jQuery("#b2c").addClass("col-active");
							jQuery("#b2c").removeClass("col-inactive");
						}					
					},
					complete: function() {
						// ... and finally resume the Checkout after the backend call is completed to update the checkout
						jQuery('body').removeClass('is-suspended');
						window.collector.checkout.api.resume();
					},
				});
			});
		}
	}
});
