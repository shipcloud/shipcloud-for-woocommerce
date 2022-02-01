jQuery(function($){
	
	$(document).ready(function(){
	
		/*
		 * Add multiselection functionality to certain fields.
		 * @see https://github.com/nobleclem/$-MultiSelect
		 * 
		 * Carrier selection in shipcloud settings page
		 */
		$('#woocommerce_shipcloud_allowed_carriers').multiselect(
			{
				columns: 1,
				texts: {
		            placeholder    : wcsc_translate.placeholder,	// text to use in dummy input
		            search         : wcsc_translate.search,			// search input placeholder text
		            selectedOptions: wcsc_translate.selected,		// selected suffix text
		            selectAll      : wcsc_translate.selectAll,		// select all text
		            unselectAll    : wcsc_translate.unselectAll,	// unselect all text
		            noneSelected   : wcsc_translate.noneSelected	// None selected text
		        },
				search: true,
				onOptionClick: function(element, option){
					var elem 	= $(option.parentElement.parentElement.parentElement);
					var carrier = $(option).val();
					if(carrier.length>12){
						carrier = carrier.substring(0,12);
						if(at_least_one_selected(elem,carrier)){
							if(carrier=='dhl_express_'){
								$('#woocommerce_shipcloud_dhl_express_regular_pickup').closest('tr').show();
							}
						}else{
							if(carrier=='dhl_express_'){
								$('#woocommerce_shipcloud_dhl_express_regular_pickup').closest('tr').hide();
							}
						}
					}
		        },
				onSelectAll: function(element, selected){}
			}
		);
	
		/**
		 * Insures each number field value is parsed to float.
		 */
		$('input[type="number"]').on('change',function(evt){
			if(!$(this).hasClass('nofloat')){
				var val = parseFloat($(this).val()).toFixed(2);
				$(this).val(val);
			}
			var min = parseInt($(this).attr('min'));
			var max = parseInt($(this).attr('max'));
			if ($(this).val() < min){
				$(this).val(min);
			}
			else if ($(this).val() > max){
				$(this).val(max);
			} 
		}).trigger('change');
		
		/**
		 * [Edit address] or [Pen] button in create shipment dialogue clicked
		 */
		$('.btn-edit-address').on('click',function(e) {
	        var div_address_form = $(this).parent().parent();
	        var div_address_form_inputs = div_address_form.find('input');
	        var div_address_form_selects = div_address_form.find('select');

	        if (div_address_form.hasClass('disabled')) {

	            div_address_form_inputs.each(function() {
	                $(this).removeAttr('disabled');
	            });

	            div_address_form_selects.each(function() {
	                $(this).removeAttr('disabled');
	            });

	            div_address_form.removeClass('disabled')

	        } else {

	            div_address_form_inputs.each(function() {
	                $(this).attr('disabled', 'disabled');
	            });

	            div_address_form_selects.each(function() {
	                $(this).attr('disabled', 'disabled');
	            });

	            div_address_form.addClass('disabled');
	        }
	    });
	
		/**
		 * [Calculate price] button in create shipment dialogue clicked
		 */
	    $('#shipcloud_calculate_price').on('click',function() {
	        $('#shipment-center .info').empty();

	        var data = get_shipment_form_data('shipcloud_calculate_shipping', false);

	        var button = $(this);
	        button.addClass('button-loading');

	        $.post(ajaxurl, data, function(response) {
	            button.removeClass('button-loading');
			
	            if (!response.success) {
					print_errors(response.data.data);
	                return;
	            }

	            if (response.data.message !== '') {
	                print_notice(response.data.message);
	                $('#shipcloud_create_label').fadeIn();
	            }            
	        });


	    });
	
		/**
		 * [Add customs declaration] button in create shipment dialogue clicked
		 */
	    $('#shipcloud_add_customs_declaration').on('click',function() {
	        shipcloud.customsDeclaration = new shipcloud.ShipmentCustomsDeclarationView({
	            model: new shipcloud.ShipmentModel(),
	            el: '.section.parcels .customs-declaration-form'
	        });
	        shipcloud.customsDeclaration.render();
	        $('.customs-declaration').toggle();
	    });
	
		/**
		 * [Prepare label] button in create shipment dialogue clicked
		 */
	    $('#shipcloud_create_shipment').on('click',function() {
	        $('#shipment-center .info').empty();
	        var data = get_shipment_form_data('shipcloud_create_shipment', false);
			
			var button = $('#shipcloud_create_shipment');
	        button.addClass('button-loading');

	        $.post(ajaxurl, data, function(response) {
	            button.removeClass('button-loading');

	            if (!response.success) {
					print_errors(response.data.data);
	                return;
	            }

	            shipcloud.shipments.unshift(response.data.data, {
	                parse: true
	            });
	        });
	    });
	
		/**
		 * [Create label] button in create shipment dialogue clicked
		 */
	    $('#shipcloud_create_shipment_label').on('click',function() {
		
			$('#shipcloud_create_shipment_label').prop("disabled",true);
		
	        $('#shipment-center .info').empty();
	        var data = get_shipment_form_data('shipcloud_create_shipment_label', false);

	        var self = this;

	        var should_ask = $(this).data('ask-create-label-check');

	        if ('no' == should_ask) {
	            shipcloud_create_shipment_label(data, self);
	        } else {
	            var ask_create_label = $('#ask-create-label');

	            ask_create_label.dialog({
	                'dialogClass': 'wcsc-dialog wp-dialog',
	                'modal': true,
	                'autoOpen': false,
					'closeText'	 : '',
	                'closeOnEscape': true,
	                'minHeight': 80,
	                'buttons': [{
	                        text: wcsc_translate.yes,
	                        click: function() {
	                            shipcloud_create_shipment_label(data, self);
								$('#shipcloud_create_shipment_label').prop("disabled",false);
	                            $(this).dialog("close");
	                        }
	                    },
	                    {
	                        text: wcsc_translate.no,
	                        click: function() {
								$('#shipcloud_create_shipment_label').prop("disabled",false);
	                            $(this).dialog("close");
	                        }
	                    },
	                ],

	            });

	            ask_create_label.dialog("open");
	        }
	    });
		
		/**
		 * [Delete shipment] or [Trash] button in shipment view clicked
		 */
        $('.shipcloud_delete_shipment').on('click',function() {
            $('#shipment-center .info').empty();

            var order_id = $("#post_ID").val();
            var shipment_id = $(this).parent().parent().find("input[name=shipment_id]").val();

            var ask_delete_shipment = $('#ask-delete-shipment');

            ask_delete_shipment.dialog({
                'dialogClass': 'wcsc-dialog wp-dialog',
                'modal': true,
                'autoOpen': false,
				'closeText': '',
                'closeOnEscape': true,
                'minHeight': 80,
                'buttons': [{
                        text: wcsc_translate.yes,
                        click: function() {

                            shipcloud_delete_shipment(order_id, shipment_id);

                            $(this).dialog("close");
                        }
                    },
                    {
                        text: wcsc_translate.no,
                        click: function() {
                            $(this).dialog("close");
                        }
                    },
                ],

            });

            ask_delete_shipment.dialog("open");
        });
	
	});
	
	/**
	 * Returns shipment form data. 
	 *
	 * @param string shipment_id
	 * @param object button
	 * @return void
	 */
    function get_shipment_form_data(ajax_action, inverse) {
        var data = get_label_form_data(inverse);
        data.action = ajax_action;

        return data;
    }
	
	/**
	 * Returns label form data. 
	 *
	 * @param bool inverse
	 * @return string json label form data
	 */
    function get_label_form_data(inverse) {
        var labelForm = $('#shipcloud-io').shipcloudLabelForm();

        if (inverse) {
            return labelForm.getReturnLabelData();
        }

        return labelForm.getLabelData();
    }
	
	/**
	 * Creates a shipment label.
	 *
	 * @param string shipment_id
	 * @param object button
	 * @return void
	 */
    function shipcloud_create_shipment_label(data, targetButton) {
        var button = $(targetButton);
        button.addClass('button-loading-blue');

        $.post(ajaxurl, data, function(response) {
            button.removeClass('button-loading-blue');

            if (!response.success) {
				// console.log(JSON.stringify(response.data));
                print_errors(response.data.data);
				return;
            }

            if (response.data.message !== '') {
                print_notice(response.data.message);
            }

            shipcloud.shipments.unshift(response.data.data, {
                parse: true
            });
        });
    }
	
	/**
	 * Deletes a shipment. 
	 *
	 * @param string order_id
	 * @param string shipment_id
	 * @return void
	 */
    function shipcloud_delete_shipment(order_id, shipment_id) {
        var data = {
            'action': 'shipcloud_delete_shipment',
            'order_id': order_id,
            'shipment_id': shipment_id
        };

        $.post(ajaxurl, data, function(response) {
            var result = JSON.parse(response);

            if (result.status == 'ERROR') {
                print_errors(result.data.data);
            }

            if (result.status == 'OK') {
                $('#shipment-' + shipment_id).remove();
            }
        });
    }
	
	/**
	 * Checks whether at least one option of a given select item is selected. 
	 *
	 * @param object  element
	 * @param string  carrier
	 * @return bool
	 */
	function at_least_one_selected(element, carrier){
		var result = false;
		element.find('li.selected input').each(function(){
			var str = $(this).val();
			if (str.substring(0,carrier.length)==carrier) {
				result = true;
			}
		});
		return result;
	}
	
	/**
	 * Displays error messages in the UI. 
	 *
	 * @param array errors
	 * @return void
	 */
    function print_errors(errors) {
		
		if (typeof errors === "string") {
            // Received single error message, so we convert it to the expected format.
            errors = [errors];
        }

        var html = '<div class="error"><ul class="errors">';
        errors.forEach(function(entry) {
            html += '<li>' + entry + '</li>';
        });
        html += '</ul></div>';

        $('#shipment-center').find('.info').fadeIn().html(html);
    }

	/**
	 * Displays a message in the UI. 
	 *
	 * @param string text
	 * @return void
	 */
    function print_notice(text) {
        var html = '<div class="notice notice-info"><p>';
        html += text;
        html += '</p></div>';

        $('#shipment-center').find('.info').fadeIn().html(html);
    }
	
});
