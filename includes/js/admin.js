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

    /**
	 * Get action data.
	 *
     * @param ajax_action
     * @param inverse
	 *
	 * @deprecated 2.0.0 Use get_label_form_data instead and add the ajax action in your context.
     */
	var get_shipment_form_data = function( ajax_action, inverse ){
        var data = get_label_form_data(inverse);
        data.action = ajax_action;

        return data;
	};

	var get_label_form_data = function (inverse) {
        var labelForm = $('#shipcloud-io').shipcloudLabelForm();

        if (inverse) {
        	return labelForm.getReturnLabelData();
		}

        return labelForm.getLabelData();
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
			button.removeClass( 'button-loading' );

			if( ! response.success )
			{
				print_errors( _( response.data ).pluck('message') );
				return;
			}

			$( '.shipment-labels' ).prepend( response.data.html );
			shipcloud_create_label_buttons();
			shipcloud_delete_shipment_buttons();
		});
	});

	$( '#shipcloud_create_shipment_return' ).click( function()
	{
		$( '#shipment-center .info').empty();
		var data = get_shipment_form_data( 'shipcloud_create_shipment', true );

		var button = $( '#shipcloud_create_shipment_return' );
		button.addClass( 'button-loading' );

		$.post( ajaxurl, data, function (response) {
            button.removeClass('button-loading');

            if (!response.success) {
                print_errors(_(response.data).pluck('message'));
                return;
            }

            $('.shipment-labels').prepend(response.data.html);
            shipcloud_create_label_buttons();
            shipcloud_delete_shipment_buttons();
        });
	});

	$( '#shipcloud_create_shipment_label' ).click( function(){
		$( '#shipment-center .info').empty();
		var data = get_shipment_form_data( 'shipcloud_create_shipment_label', false );

		var ask_create_label = $('#ask-create-label');

		var self = this;

		ask_create_label.dialog({
			'dialogClass': 'wcsc-dialog wp-dialog',
			'modal': true,
			'autoOpen': false,
			'closeOnEscape': true,
			'minHeight': 80,
			'buttons': [{
				text: wcsc_translate.yes,
				click: function () {
					shipcloud_create_shipment_label(data, self);

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

		var self = this;

		ask_create_label.dialog({
			'dialogClass': 'wcsc-dialog wp-dialog',
			'modal': true,
			'autoOpen': false,
			'closeOnEscape': true,
			'minHeight': 80,
			'buttons': [{
				text: wcsc_translate.yes,
				click: function () {

					shipcloud_create_shipment_label(data, self);

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

	var shipcloud_create_shipment_label = function( data, targetButton )
	{
		var button = $( targetButton );
		button.addClass( 'button-loading-blue' );

		$.post( ajaxurl, data, function( response ) {
			button.removeClass( 'button-loading-blue' );

			if( ! response.success )
			{
                print_errors(_(response.data).pluck('message'));
				return;
			}

            $(response.data.html).prependTo('.shipment-labels')
				.find('.widget-top').effect('highlight', {color: '#46b450'}, 3000);

			shipcloud_create_label_buttons();
			shipcloud_delete_shipment_buttons();
		});
	};

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
						var shipment_id = button.closest('div.data').find("input[name='shipment_id']").val();

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
