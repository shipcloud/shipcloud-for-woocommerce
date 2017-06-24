jQuery( function( $ ) {

	$( '.btn-edit-address' ).click( function ( e ){
		var div_address_form = $( this ).parent().parent();
		var div_address_form_inputs = div_address_form.find( 'input' );
		var div_address_form_selects = div_address_form.find( 'select' );

		if( div_address_form.hasClass( 'disabled' ) ){

			div_address_form_inputs.each(function() {
				$( this ).removeAttr( 'disabled' );
			});

			div_address_form_selects.each(function() {
				$( this ).removeAttr( 'disabled' );
			});

			div_address_form.removeClass( 'disabled' )

		}else{

			div_address_form_inputs.each(function() {
				$( this).attr( 'disabled', 'disabled' );
			});

			div_address_form_selects.each(function() {
				$( this ).attr( 'disabled', 'disabled' );
			});

			div_address_form.addClass( 'disabled' );
		}
	});

	$( '.insert-to-form' ).click( function (){
		var parcel = $( this ).parent().find( "select[name='parcel_list']").val();

		if( 'none' != parcel ) {
			var form_table = $('.parcel-form-table');

			parcel = parcel.split( ';' );

			form_table.find( "input[name='parcel_width']" ).val( parcel[ 0 ] );
			form_table.find( "input[name='parcel_height']" ).val( parcel[ 1 ] );
			form_table.find( "input[name='parcel_length']" ).val( parcel[ 2 ] );
			form_table.find( "input[name='parcel_weight']" ).val( parcel[ 3 ] );
			form_table.find( "select[name='parcel_carrier']" ).val( parcel[ 4 ] );
		}
	});

	var get_shipment_form_data = function( ajax_action, inverse ){
        var sender = {
            'first_name': $("input[name='sender_address[first_name]']").val(),
            'last_name' : $("input[name='sender_address[last_name]']").val(),
            'company'   : $("input[name='sender_address[company]']").val(),
            'street'    : $("input[name='sender_address[street]']").val(),
            'street_no' : $("input[name='sender_address[street_nr]']").val(),
            'zip_code'  : $("input[name='sender_address[zip_code]']").val(),
            'city'      : $("input[name='sender_address[city]']").val(),
            'state'     : $("input[name='sender_address[state]']").val(),
            'country'   : $("select[name='sender_address[country]']").val()
        };

        var recipient = {
            'first_name': $("input[name='recipient_address[first_name]']").val(),
            'last_name' : $("input[name='recipient_address[last_name]']").val(),
            'company'   : $("input[name='recipient_address[company]']").val(),
            'street'    : $("input[name='recipient_address[street]']").val(),
            'street_no' : $("input[name='recipient_address[street_nr]']").val(),
            'care_of'   : $("input[name='recipient_address[care_of]']").val(),
            'zip_code'  : $("input[name='recipient_address[zip_code]']").val(),
            'city'      : $("input[name='recipient_address[city]']").val(),
            'state'     : $("input[name='recipient_address[state]']").val(),
            'country'   : $("select[name='recipient_address[country]']").val(),
            'phone'     : $("input[name='recipient_address[phone]']").val(),
        };


        var data = {
            'action'           : ajax_action,
            'order_id'         : $("#post_ID").val(),
            'sender'           : sender,
            'recipient'        : recipient,
            'parcel_id'        : $("select[name='parcel_id']").val(),
            'carrier'          : $("select[name='parcel_carrier']").val(),
            'width'            : $("input[name='parcel_width']").val(),
            'height'           : $("input[name='parcel_height']").val(),
            'length'           : $("input[name='parcel_length']").val(),
            'weight'           : $("input[name='parcel_weight']").val(),
            'description'      : $("input[name='parcel_description']").val(),
            'other_description': $("input[name='other_description']").val()
        };

        if (inverse) {
            data.sender = recipient;
            data.recipient = sender;
        }

        return data;
	};

	$( '#shipcloud_calculate_price' ).click( function(){
		$( '#shipment-center .info').empty();

		var data = get_shipment_form_data( 'shipcloud_calculate_shipping', false );

		var button = $( this );
		button.addClass( 'button-loading' );

		$.post( ajaxurl, data, function( response )
		{
			var result = jQuery.parseJSON( response );

			if( result.status == 'ERROR' )
			{
				print_errors( result.errors );
			}

			if( result.status == 'OK' )
			{
				print_notice( result.html );
				$( '#shipcloud_create_label').fadeIn();
			}

			button.removeClass( 'button-loading' );
		});


	});

	$( '#shipcloud_create_shipment' ).click( function()
	{
		$( '#shipment-center .info').empty();
		var data = get_shipment_form_data( 'shipcloud_create_shipment', false );

		var button = $( '#shipcloud_create_shipment' );
		button.addClass( 'button-loading' );

		$.post( ajaxurl, data, function( response )
		{
			var result = JSON.parse( response );

			if( result.status == 'ERROR' )
			{
				print_errors( result.errors );
			}

			if( result.status == 'OK' )
			{
				$( '.shipment-labels' ).prepend( result.html );
				shipcloud_create_label_buttons();
				shipcloud_delete_shipment_buttons();

				// $( '#shipcloud_create_label').fadeIn();
			}

			button.removeClass( 'button-loading' );
		});
	});

	$( '#shipcloud_create_shipment_return' ).click( function()
	{
		$( '#shipment-center .info').empty();
		var data = get_shipment_form_data( 'shipcloud_create_shipment', true );

		var button = $( '#shipcloud_create_shipment_return' );
		button.addClass( 'button-loading' );

		$.post( ajaxurl, data, function( response )
		{
			var result = JSON.parse( response );

			if( result.status == 'ERROR' )
			{
				print_errors( result.errors );
			}

			if( result.status == 'OK' )
			{
				$( '.shipment-labels' ).prepend( result.html );
				shipcloud_create_label_buttons();
				shipcloud_delete_shipment_buttons();

				// $( '#shipcloud_create_label').fadeIn();
			}

			button.removeClass( 'button-loading' );
		});
	});

	$( '#shipcloud_create_shipment_label' ).click( function(){
		$( '#shipment-center .info').empty();
		var data = get_shipment_form_data( 'shipcloud_create_shipment_label', false );

		var ask_create_label = $('#ask-create-label');

		ask_create_label.dialog({
			'dialogClass': 'wcsc-dialog wp-dialog',
			'modal': true,
			'autoOpen': false,
			'closeOnEscape': true,
			'minHeight': 80,
			'buttons': [{
				text: wcsc_translate.yes,
				click: function () {

					shipcloud_create_shipment_label(data);

					$(this).dialog("close");
				}
			},
				{
					text: wcsc_translate.no,
					click: function () {
						$(this).dialog("close");
					}
				},
			],

		});

		ask_create_label.dialog("open");
	});

	$( '#shipcloud_create_shipment_return_label' ).click( function(){
		$( '#shipment-center .info').empty();
		var data = get_shipment_form_data( 'shipcloud_create_shipment_label', true );

		var ask_create_label = $('#ask-create-label');

		ask_create_label.dialog({
			'dialogClass': 'wcsc-dialog wp-dialog',
			'modal': true,
			'autoOpen': false,
			'closeOnEscape': true,
			'minHeight': 80,
			'buttons': [{
				text: wcsc_translate.yes,
				click: function () {

					shipcloud_create_shipment_return_label(data);

					$(this).dialog("close");
				}
			},
				{
					text: wcsc_translate.no,
					click: function () {
						$(this).dialog("close");
					}
				},
			],

		});

		ask_create_label.dialog("open");
	});

	function shipcloud_delete_shipment_buttons() {
		$('.shipcloud_delete_shipment').click(function () {
			$( '#shipment-center .info').empty();

			var order_id = $("#post_ID").val();
			var shipment_id = $(this).parent().parent().find("input[name=shipment_id]").val();

			var ask_delete_shipment = $('#ask-delete-shipment');

			ask_delete_shipment.dialog({
				'dialogClass': 'wcsc-dialog wp-dialog',
				'modal': true,
				'autoOpen': false,
				'closeOnEscape': true,
				'minHeight': 80,
				'buttons': [{
					text: wcsc_translate.yes,
					click: function () {

						shipcloud_delete_shipment(order_id, shipment_id);

						$(this).dialog("close");
					}
				},
					{
						text: wcsc_translate.no,
						click: function () {
							$(this).dialog("close");
						}
					},
				],

			});

			ask_delete_shipment.dialog("open");
		});
	}
	shipcloud_delete_shipment_buttons();

	var shipcloud_create_shipment_label = function( data )
	{
		var button = $( '#shipcloud_create_shipment_label' );
		button.addClass( 'button-loading-blue' );

		$.post( ajaxurl, data, function( response ) {

			var result = JSON.parse( response );

			if( result.status == 'ERROR' )
			{
				print_errors( result.errors );
			}

			if( result.status == 'OK' )
			{
				$( '.shipment-labels' ).prepend( result.html );
				shipcloud_create_label_buttons();
				shipcloud_delete_shipment_buttons();
			}

			button.removeClass( 'button-loading-blue' );
		});
	}

	var shipcloud_create_shipment_return_label = function( data )
	{
		var button = $( '#shipcloud_create_shipment_return_label' );
		button.addClass( 'button-loading-blue' );

		$.post( ajaxurl, data, function( response ) {

			var result = JSON.parse( response );

			if( result.status == 'ERROR' )
			{
				print_errors( result.errors );
			}

			if( result.status == 'OK' )
			{
				$( '.shipment-labels' ).prepend( result.html );
				shipcloud_create_label_buttons();
				shipcloud_delete_shipment_buttons();
			}

			button.removeClass( 'button-loading-blue' );
		});
	}

	var shipcloud_create_label = function( shipment_id, button )
	{
		var order_id = $( "#post_ID" ).val();

		var data = {
			'action': 'shipcloud_create_label',
			'order_id': order_id,
			'shipment_id': shipment_id
		};

        button.addClass( 'button-loading-blue' );

		$.post( ajaxurl, data, function( response )
		{
			var result = JSON.parse( response );

			if( result.status == 'ERROR' )
			{
				print_errors( result.errors );
			}

			if( result.status == 'OK' )
			{
				var div_create_label = button.parent();
				var div_download_label = button.parent().parent().find( '.button-download-label' );
				var div_delete_label = button.parent().parent().find( '.button-delete-shipment' );
				var tracking_nr_text = button.parent().parent().parent().find( '.tracking-number' );
				var price_text = button.parent().parent().parent().find( '.price' );

				tracking_nr_text.text( result.carrier_tracking_no );
				price_text.html( result.price );

				div_download_label.find( 'a' ).attr( 'href', result.label_url );
				div_download_label.find( 'a' ).attr( 'target', '_blank' );

				div_delete_label.remove();

				div_create_label.removeClass( 'show' );
				div_create_label.addClass( 'hide' );

				div_download_label.removeClass( 'hide' );
				div_download_label.addClass( 'show' );

				$( '.shipment-labels' ).prepend( result.html );
			}

            button.removeClass( 'button-loading-blue' );
		});
	}

	var shipcloud_delete_shipment = function ( order_id, shipment_id ) {
		var data = {
			'action': 'shipcloud_delete_shipment',
			'order_id': order_id,
			'shipment_id': shipment_id
		};

		$.post( ajaxurl, data, function( response ) {
			var result = JSON.parse(response);

			if (result.status == 'ERROR') {
				print_errors(result.errors);
			}

			if (result.status == 'OK') {
				$('#shipment-' + shipment_id).remove();
			}
		});
	};

	var shipcloud_create_label_buttons = function () {
		$('.shipcloud_create_label').click( function ( e ) {
			$( '#shipment-center .info').empty();

			var ask_create_label = $('#ask-create-label');
            var button = $( this );

			ask_create_label.dialog({
				'dialogClass': 'wcsc-dialog wp-dialog',
				'modal': true,
				'autoOpen': false,
				'closeOnEscape': true,
				'minHeight': 80,
				'buttons': [{
					text: wcsc_translate.yes,
					click: function ()
                    {
						var shipment_id = $(this).parent().parent().find("input[name='shipment_id']").val();

						shipcloud_create_label( shipment_id, button );

						$(this).dialog("close");
					}
				},
					{
						text: wcsc_translate.no,
						click: function () {
							$(this).dialog("close");
						}
					},
				],
			});

			ask_create_label.dialog("open");
		});
	}
	shipcloud_create_label_buttons();

	function print_errors( errors ){
		var html = '<div class="error"><ul class="errors">';
		errors.forEach( function( entry ){
			html+= '<li>' + entry + '</li>';
		});
		html+= '</ul></div>';

		$( '#shipment-center .info' ).fadeIn().html( html );
	}

	function print_notice( text )
	{
		$( '#shipment-center .info' ).fadeIn().html( text );
	}

	/**
	 * Hiding parcel template adding button if value in form has changed
	 */
	$( "input[name='parcel[width]'],  input[name='parcel[height]'],  input[name='parcel[length]'],  input[name='parcel[weight]']" ).focusin(function(){
		$( '.parcel .info' ).fadeOut();
		$( '#shipcloud_add_parcel_template').fadeOut();
	});

	$( "select[name='parcel[carrier]']" ).change(function(){
		$( '.parcel .info' ).fadeOut();
		$( '#shipcloud_add_parcel_template').fadeOut();
	});

	$( "select[name='parcel_template']" ).change(function(){
		$( '#parcel_templates .info' ).fadeOut();
	});

	/**
	 * Function to switch to parcel templates
	 */
    $('.shipcloud-switchto-parcel-tamplates').click( function () {
        $( '.shipcloud-tabs' ).tabs( "option", "active", 1 );
    });
});
