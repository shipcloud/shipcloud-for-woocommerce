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

	var get_shipment_form_data = function( ajax_action ){

		var order_id = $( "#post_ID" ).val();

		var sender_first_name 	= $( "input[name='sender_address[first_name]']" ).val( );
		var sender_last_name 	= $( "input[name='sender_address[last_name]']" ).val( );
		var sender_company 	= $( "input[name='sender_address[company]']" ).val( );
		var sender_street 	= $( "input[name='sender_address[street]']" ).val( );
		var sender_street_nr= $( "input[name='sender_address[street_nr]']" ).val( );
		var sender_postcode = $( "input[name='sender_address[postcode]']" ).val( );
		var sender_city 	= $( "input[name='sender_address[city]']" ).val( );
		var sender_country 	= $( "select[name='sender_address[country]']" ).val( );

		var recipient_first_name 	= $( "input[name='recipient_address[first_name]']" ).val( );
		var recipient_last_name 	= $( "input[name='recipient_address[last_name]']" ).val( );
		var recipient_company 	= $( "input[name='recipient_address[company]']" ).val( );
		var recipient_street 	= $( "input[name='recipient_address[street]']" ).val( );
		var recipient_street_nr= $( "input[name='recipient_address[street_nr]']" ).val( );
		var recipient_postcode = $( "input[name='recipient_address[postcode]']" ).val( );
		var recipient_city 	= $( "input[name='recipient_address[city]']" ).val( );
		var recipient_country 	= $( "select[name='recipient_address[country]']" ).val( );

		var parcel_id 	= $( "select[name=parcel_id" ).val( );

		var carrier 	= $( "select[name='parcel_carrier']" ).val( );
		var width 		= $( "input[name='parcel_width']" ).val( );
		var height 		= $( "input[name='parcel_height']" ).val( );
		var length 		= $( "input[name='parcel_length']" ).val( );
		var weight 		= $( "input[name='parcel_weight']" ).val( );

		var parcel_title = '';

		var data = {
			'action': ajax_action,
			'order_id' : order_id,
			'sender_first_name' : sender_first_name,
			'sender_last_name' : sender_last_name,
			'sender_company' : sender_company,
			'sender_street' : sender_street,
			'sender_street_nr' : sender_street_nr,
			'sender_postcode' : sender_postcode,
			'sender_city' : sender_city,
			'sender_country' : sender_country,
			'recipient_first_name' : recipient_first_name,
			'recipient_last_name' : recipient_last_name,
			'recipient_company' : recipient_company,
			'recipient_street' : recipient_street,
			'recipient_street_nr': recipient_street_nr,
			'recipient_postcode' : recipient_postcode,
			'recipient_city' : recipient_city,
			'recipient_country' : recipient_country,
			'parcel_id' : parcel_id,
			'carrier': carrier,
			'width': width,
			'height': height,
			'length': length,
			'weight': weight
		};

		return data;
	}

	$( '#shipcloud_calculate_price' ).click( function(){
		$( '#shipment-center .info').empty();

		var data = get_shipment_form_data( 'shipcloud_calculate_shipping' );

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
		var data = get_shipment_form_data( 'shipcloud_create_shipment' );

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

	$( '#shipcloud_create_shipment_label' ).click( function(){
		$( '#shipment-center .info').empty();
		var data = get_shipment_form_data( 'shipcloud_create_shipment_label' );

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