jQuery( function( $ ) {
	
	$( '.btn_edit_address' ).click( function ( e ){

		var position = $( this ).position();
		var div_address = $( this ).parent().parent().find( ".address" );
		var div_edit_address = $( this ).parent().parent().find( ".edit_address" );

		// Centering container under edit button
		var container_left_position = position.left  - ( div_edit_address.width() / 2 );
		div_edit_address.css( 'top', ( position.top + 25 ) + 'px' );
		div_edit_address.css( 'left', ( container_left_position ) + 'px');

		if( 'none' == div_edit_address.css( 'display' ) ){
			div_edit_address.show();
		}else{
			var address = '';
			
			if( div_address.parent().hasClass( 'sender' ) ){
				address += $( "input[name='sender_address[first_name]']" ).val()  + ' ';
				address += $( "input[name='sender_address[last_name]']" ).val() + '<br />';
				address += $( "input[name='sender_address[company]']" ).val() + '<br />';
				address += $( "input[name='sender_address[street]']" ).val()  + ' ';
				address += $( "input[name='sender_address[street_nr]']" ).val() + '<br />';
				address += $( "input[name='sender_address[postcode]']" ).val() + ' ';
				address += $( "input[name='sender_address[city]']" ).val() + '<br />';
				address += $( "select[name='sender_address[country]']" ).val();
			}else{
		
				address += $( "input[name='recipient_address[first_name]']" ).val() + ' ';
				address += $( "input[name='recipient_address[last_name]']" ).val() + '<br />';
				address += $( "input[name='recipient_address[company]']" ).val() + '<br />';
				address += $( "input[name='recipient_address[street]']" ).val() + ' ';
				address += $( "input[name='recipient_address[street_nr]']" ).val() + '<br />';
				address += $( "input[name='recipient_address[postcode]']" ).val() +  ' ';
				address += $( "input[name='recipient_address[city]']" ).val() + '<br />';
				address += $( "select[name='recipient_address[country]']" ).val();
			}
			
			div_address.html( address );
						
			div_edit_address.hide();
		}
	});

	var edit_address = function(){

	}
	
	var carrier_select = function(){
		$( '.carrier_select' ).click( function (){
			
			var template = $( this ).parent();
			
			var carrier = template.find( "input[name='carrier']" ).val();
			var width = template.find( "input[name='width']" ).val();
			var height = template.find( "input[name='height']" ).val();
			var length = template.find( "input[name='length']" ).val();
			var weight = template.find( "input[name='weight']" ).val();

			$( '.shipcloud-tabs' ).tabs( "option", "active", 0 );

			var template_value = carrier + ';' + width + ';' + height + ';' + length + ';' + weight;

			$( "select[name='parcel_template']" ).val( template_value );

		});
	}
	carrier_select();
	
	var carrier_delete = function(){
		$( '#shipcloud .carrier_delete' ).click( function (){
			
			var template = $( this ).parent();
			
			var carrier = template.find( "input[name='carrier']" ).val();
			var width = template.find( "input[name='width']" ).val();
			var height = template.find( "input[name='height']" ).val();
			var length = template.find( "input[name='length']" ).val();
			var weight = template.find( "input[name='weight']" ).val();

			var template_value = carrier + ';' + width + ';' + height + ';' + length + ';' + weight;

			$( "select[name='parcel_template'] option[value='" + template_value + "']" ).remove();

			var parcel_template_count = $( "select[name='parcel_template'] option").size();
			if( parcel_template_count < 1 ){
				$( '#parcel_templates').hide();
				$( '#parcel_templates_missing').show();
			}

			var data = {
				'action': 'shipcloud_delete_parcel_template',
				'carrier': carrier,
				'width': width,
				'height': height,
				'length': length,
				'weight': weight
			};
			
			$.post( ajaxurl, data, function( response ) {
				var result = jQuery.parseJSON( response );
				if( result.deleted == true ){
					template.parent().remove();
				}
			});
		});
	}
	carrier_delete();
	
	$( '#shipcloud_add_parcel_template' ).click( function (){
		var template = $( '#parcel_options' );
		
		var carrier_name = template.find( "select[name='parcel[carrier]'] option:selected" ).text();
		var carrier = template.find( "select[name='parcel[carrier]']" ).val();
		var width = template.find( "input[name='parcel[width]']" ).val();
		var height = template.find( "input[name='parcel[height]']" ).val();
		var length = template.find( "input[name='parcel[length]']" ).val();
		var weight = template.find( "input[name='parcel[weight]']" ).val();
		
		var data = {
			'action': 'shipcloud_add_parcel_template',
			'carrier': carrier,
			'width': width,
			'height': height,
			'length': length,
			'weight': weight
		};
		
		$.post( ajaxurl, data, function( response ) {
			var result = jQuery.parseJSON( response );
			
			if( result.added == true ){
				
				var html = '<div class="parcel_template_added">';
				html+= wcsc_translate.parcel_added;
				html+= '</div>';
				
				$( '.parcel .info' ).fadeIn().html( html );
				
				var html = '<tr><td>' + carrier_name + '</td>';
				html+= '<td>' + width + ' ' + wcsc_translate.cm + '</td>';
				html+= '<td>' + height + ' ' + wcsc_translate.cm + '</td>';
				html+= '<td>' + length + ' ' + wcsc_translate.cm + '</td>';
				html+= '<td>' + weight + ' ' + wcsc_translate.kg + '</td>';
				html+= '<td>';
					html+= '<input type="button" class="carrier_delete button"  value="' + wcsc_translate.delete + '" />';
					html+= '<input type="button" class="carrier_select button"  value="' + wcsc_translate.select + '" />';
					html+= '<input type="hidden" value="' + carrier  + '" name="carrier" />';
					html+= '<input type="hidden" value="' + width  + '" name="width" />';
					html+= '<input type="hidden" value="' + height + '" name="height" />';
					html+= '<input type="hidden" value="' + length + '" name="length" />';
					html+= '<input type="hidden" value="' + weight + '" name="weight" />';
				html+= '</td></tr>';
				
				$( '#parcel_table tbody' ).append( html );
				
				var show = carrier_name + ' ' + width + ' x ' + height + ' x ' + length + ' ' + wcsc_translate.cm + ' x ' + weight + ' '+ wcsc_translate.kg;
				var value = carrier + ';' + width + ';' + height + ';' + length + ';' + weight;
				
				html = '<option value="' + value + '">' + show + '</option>';
				
				$( '#create_label #parcel_template' ).append( html );

				$( '#parcel_templates').show();
				$( '#parcel_templates_missing').hide();

				carrier_select();
				carrier_delete();
			}else{
				
				var html = '<div class="parcel_template_not_added">';
				html+= wcsc_translate.parcel_not_added;
				html+= '</div>';
				
				$( '.parcel .info' ).fadeIn().html( html );
			}
		});
	});

	$( '#shipcloud_verify_parcel_settings' ).click( function(){

		var sender_street 	= $( "input[name='sender_address[street]']" ).val( );
		var sender_street_nr= $( "input[name='sender_address[street_nr]']" ).val( );
		var sender_postcode = $( "input[name='sender_address[postcode]']" ).val( );
		var sender_city 	= $( "input[name='sender_address[city]']" ).val( );
		var sender_country 	= $( "select[name='sender_address[country]']" ).val( );

		var recipient_street 	= $( "input[name='recipient_address[street]']" ).val( );
		var recipient_street_nr= $( "input[name='recipient_address[street_nr]']" ).val( );
		var recipient_postcode = $( "input[name='recipient_address[postcode]']" ).val( );
		var recipient_city 	= $( "input[name='recipient_address[city]']" ).val( );
		var recipient_country 	= $( "select[name='recipient_address[country]']" ).val( );

		var carrier 	= $( "select[name='parcel[carrier]']" ).val( );
		var width 		= $( "input[name='parcel[width]']" ).val();
		var height 		= $( "input[name='parcel[height]']" ).val();
		var length 		= $( "input[name='parcel[length]']" ).val();
		var weight 		= $( "input[name='parcel[weight]']" ).val();

		var data = {
			'action': 'shipcloud_calculate_shipping',
			'sender_street' : sender_street,
			'sender_street_nr' : sender_street_nr,
			'sender_postcode' : sender_postcode,
			'sender_city' : sender_city,
			'sender_country' : sender_country,
			'recipient_street' : recipient_street,
			'recipient_street_nr': recipient_street_nr,
			'recipient_postcode' : recipient_postcode,
			'recipient_city' : recipient_city,
			'recipient_country' : recipient_country,
			'carrier': carrier,
			'width': width,
			'height': height,
			'length': length,
			'weight': weight
		};

		var button = $( this );
		button.addClass( 'button-loading' );

		$.post( ajaxurl, data, function( response ) {

			var result = jQuery.parseJSON( response );

			if( result.errors ){

				var html = '<ul class="errors">';
				result.errors.forEach( function( entry ){
					html+= '<li>' + entry + '</li>';
				});
				html+= '</ul>';

				$( '#wcsc-tab-templates .info' ).fadeIn().html( html ).delay( 5000 ).fadeOut( 2000 );
				$( '#shipcloud_add_parcel_template').fadeOut();

			}if( result.price ){
				var html = '<div class="notice">';
				html+= wcsc_translate.parcel_dimensions_check_yes;
				html+= '</div>';

				$( '#wcsc-tab-templates .info' ).fadeIn().html( html ).delay( 5000 ).fadeOut( 2000 );
				$( '#shipcloud_add_parcel_template').fadeIn();
			}

			button.removeClass( 'button-loading' );
		});
	});
	
	
	$( '#shipcloud_calculate_price' ).click( function(){
		
		var sender_street 	= $( "input[name='sender_address[street]']" ).val( );
		var sender_street_nr= $( "input[name='sender_address[street_nr]']" ).val( );
		var sender_postcode = $( "input[name='sender_address[postcode]']" ).val( );
		var sender_city 	= $( "input[name='sender_address[city]']" ).val( );
		var sender_country 	= $( "select[name='sender_address[country]']" ).val( );
		
		var recipient_street 	= $( "input[name='recipient_address[street]']" ).val( );
		var recipient_street_nr= $( "input[name='recipient_address[street_nr]']" ).val( );
		var recipient_postcode = $( "input[name='recipient_address[postcode]']" ).val( );
		var recipient_city 	= $( "input[name='recipient_address[city]']" ).val( );
		var recipient_country 	= $( "select[name='recipient_address[country]']" ).val( );

		var parcel_template 	= $( "select[name='parcel_template" ).val( );
		var parcel_dimendions 	= parcel_template.split( ';' );

		var carrier 	= parcel_dimendions[0];
		var width 		= parcel_dimendions[1];
		var height 		= parcel_dimendions[2];
		var length 		= parcel_dimendions[3];
		var weight 		= parcel_dimendions[4];
		
		var data = {
			'action': 'shipcloud_calculate_shipping',
			'sender_street' : sender_street,
			'sender_street_nr' : sender_street_nr,
			'sender_postcode' : sender_postcode,
			'sender_city' : sender_city,
			'sender_country' : sender_country,
			'recipient_street' : recipient_street,
			'recipient_street_nr': recipient_street_nr,
			'recipient_postcode' : recipient_postcode,
			'recipient_city' : recipient_city,
			'recipient_country' : recipient_country,
			'carrier': carrier,
			'width': width,
			'height': height,
			'length': length,
			'weight': weight
		};

		var button = $( this );
		button.addClass( 'button-loading' );
		
		$.post( ajaxurl, data, function( response ) {
			
			var result = jQuery.parseJSON( response );
			
			if( result.errors ){
				var html = '<ul class="errors">';
				result.errors.forEach( function( entry ){
					html+= '<li>' + entry + '</li>';
				});
				html+= '</ul>';
				
				$( '#wcsc-tab-label .info' ).fadeIn().html( html ).delay( 5000 ).fadeOut( 2000 );
				$( '#shipcloud_create_label').fadeOut();

			}if( result.price ){
				var html = '<div class="notice">';
				html+= wcsc_translate.price_text + ' ' +  result.price;
				html+= '</div>';
				
				$( '#wcsc-tab-label .info' ).fadeIn().html( html ).delay( 5000 ).fadeOut( 2000 );
				$( '#shipcloud_create_label').fadeIn();
			}
			button.removeClass( 'button-loading' );
		});


	});

	var shipcloud_create_label = function(){
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

		var parcel_template 	= $( "select[name='parcel_template']" ).val( );
		parcel_template = parcel_template.split( ";" );

		var carrier 	= parcel_template[ 0 ];
		var width 		= parcel_template[ 1 ];
		var height 		= parcel_template[ 2 ];
		var length 		= parcel_template[ 3 ];
		var weight 		= parcel_template[ 4 ];

		var data = {
			'action': 'shipcloud_create_label',
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
			'carrier': carrier,
			'width': width,
			'height': height,
			'length': length,
			'weight': weight
		};

		var button = $( '#shipcloud_create_label' );
		button.addClass( 'button-loading-blue' );

		$.post( ajaxurl, data, function( response ) {
			try
			{
				var result = JSON.parse( response );

				if( result.errors != null ){
					var html = '<ul class="errors">';
					result.errors.forEach( function( entry ){
						html+= '<li>' + entry + '</li>';
					});
					html+= '</ul>';

					$( '.parcel .info' ).fadeIn().html( html ).delay( 5000 ).fadeOut( 2000 );
				}else{
					shipcloud_order_pickup();
				}
			}
			catch( e )
			{
				$( '.shipment_labels' ).prepend( response );
				$( '#no_label_created' ).fadeOut();
			}

			button.removeClass( 'button-loading-blue' );
		});
	}

	var shipcloud_order_pickup = function(){
		$( '.shipcloud-order-pickup' ).click( function() {

			var carrier = $( this ).parent().find( "input[name='carrier']" ).val();
			var shipment_id = $( this ).parent().find( "input[name='shipment_id']" ).val();

			console.log( shipment_id );

			var data = {
				'action': 'shipcloud_request_pickup',
				'carrier': carrier,
				'shipment_id': shipment_id,
			};

			var button = $( this );
			button.addClass( 'button-loading-blue' );

			$.post(ajaxurl, data, function (response) {
				try {
					var result = JSON.parse(response);

					if (result.errors != null) {
						var html = '<ul class="errors">';
						result.errors.forEach(function (entry) {
							html += '<li>' + entry + '</li>';
						});
						html += '</ul>';

						$('.parcel .info').fadeIn().html(html).delay(5000).fadeOut(2000);
					} else {

					}
				}
				catch (e) {
					$('.shipment_labels').prepend(response);
					$('#no_label_created').fadeOut();
				}

				button.removeClass('button-loading-blue');
			});
		});
	}
	shipcloud_order_pickup();


	
	$( '#shipcloud_create_label' ).click( function(){
		var ask_create_label = $( '#ask_create_label' );

		ask_create_label.dialog({                   
            'dialogClass'   : 'wp-dialog',           
            'modal'         : true,
            'autoOpen'      : false, 
            'closeOnEscape' : true,
            'minHeight'     : 80,
            'buttons'       : [{
                    text: wcsc_translate.yes,
                    click: function() {
                            shipcloud_create_label();
                            $( this ).dialog( "close" );
                        }
                    },
                    {
                    text: wcsc_translate.no,
                    click: function() {
                        	$( this ).dialog( "close" );
                        }
                    },
                ],
                
        });
        
        ask_create_label.dialog( "open" );
	});

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

	/**
	 * Initializing tabs
	 */
	$( '.shipcloud-tabs' ).tabs();

	/**
	 * Function to switch to parcel templates
	 */
    $('.shipcloud-switchto-parcel-tamplates').click( function () {
        $( '.shipcloud-tabs' ).tabs( "option", "active", 1 );
    });

	/**
	 * CSS Corrections
	 */
	$( '#shipcloud' ).find( '.ui-tabs' ).removeClass( 'ui-tabs' );
	$( '#shipcloud' ).find( '.ui-widget' ).removeClass( 'ui-widget' );
	$( '#shipcloud' ).find( '.ui-widget-content' ).removeClass( 'ui-widget-content' );
	$( '#shipcloud' ).find( '.ui-corner-all' ).removeClass( 'ui-corner-all' );
	$( '#shipcloud' ).find( '.ui-tabs-nav' ).removeClass( 'ui-tabs-nav' );
	$( '#shipcloud' ).find( '.ui-helper-reset' ).removeClass( 'ui-helper-reset' );
	$( '#shipcloud' ).find( '.ui-helper-clearfix' ).removeClass( 'ui-helper-clearfix' );
	$( '#shipcloud' ).find( '.ui-widget-header' ).removeClass( 'ui-widget-header' );
	$( '#shipcloud' ).find( '.ui-state-default' ).removeClass( 'ui-state-default' );
	$( '#shipcloud' ).find( '.ui-corner-top' ).removeClass( 'ui-corner-top' );
	$( '#shipcloud' ).find( '.ui-state-active' ).removeClass( 'ui-state-active' );
	$( '#shipcloud' ).find( '.ui-tabs-panel' ).removeClass( 'ui-tabs-panel' );
	$( '#shipcloud' ).find( '.ui-corner-bottom' ).removeClass( 'ui-corner-bottom' );
	 
});