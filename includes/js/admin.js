jQuery( function( $ ) {
	
	$( '.btn_edit_address' ).click( function (){
		
		var div_address = $( this ).parent().parent().find( ".address" );
		var div_edit_address = $( this ).parent().parent().find( ".edit_address" );
		
		console.log( div_edit_address );
		
		div_address.hide();
		div_edit_address.show();
	});
	
	$( '.carrier_select' ).click( function (){
		
		var template = $( this ).parent();
		
		var carrier = template.find( "input[name='carrier']" ).val();
		var width = template.find( "input[name='width']" ).val();
		var height = template.find( "input[name='height']" ).val();
		var length = template.find( "input[name='length']" ).val();
		var weight = template.find( "input[name='weight']" ).val();
		
		$( "select[name='parcel[carrier]']" ).val( carrier );
		$( "input[name='parcel[width]']" ).val( width );
		$( "input[name='parcel[height]']" ).val( height );
		$( "input[name='parcel[length]']" ).val( length );
		$( "input[name='parcel[weight]']" ).val( weight );
	});
	
	$( '#shipcloud .carrier_delete' ).click( function (){
		
		var template = $( this ).parent();
		
		var carrier = template.find( "input[name='carrier']" ).val();
		var width = template.find( "input[name='width']" ).val();
		var height = template.find( "input[name='height']" ).val();
		var length = template.find( "input[name='length']" ).val();
		var weight = template.find( "input[name='weight']" ).val();
		
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
	
	$( '#shipcloud #shipcloud_calculate_shipping' ).click( function(){
		
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
		
		$.post( ajaxurl, data, function( response ) {
			
			var result = jQuery.parseJSON( response );
			
			if( result.errors ){
				var html = '<ul class="errors">';
				result.errors.forEach( function( entry ){
					html+= '<li>' + entry + '</li>';
				});
				html+= '</ul>';
				
				$( '.parcel .info' ).fadeIn().html( html ).delay( 5000 ).fadeOut( 2000 );
				
			}if( result.price ){
				var price_text = 'The calculated price is';
				var button_text = 'Create label';
				
				var html = '<div class="parcel_price">';
				html+= price_text + ' ' +  result.price;
				html+= '</div>';
				
				$( '.parcel .info' ).fadeIn().html( html );
				$( '#shipcloud_create_label').fadeIn();
			}
		});
	});
	
	$( '#shipcloud #shipcloud_create_label' ).click( function(){
		
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
		
		var carrier 	= $( "select[name='parcel[carrier]']" ).val( );
		var width 		= $( "input[name='parcel[width]']" ).val();
		var height 		= $( "input[name='parcel[height]']" ).val();
		var length 		= $( "input[name='parcel[length]']" ).val();
		var weight 		= $( "input[name='parcel[weight]']" ).val();
		
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
				}
			}
			catch( e )
			{
				$( '.shipment_labels' ).prepend( response );
			}
		});
	});
		
	$( "input[name='parcel[width]'],  input[name='parcel[height]'],  input[name='parcel[length]'],  input[name='parcel[weight]']" ).focusin(function(){
		
		$( '.parcel .info' ).fadeOut();
		$( '#shipcloud_create_label').fadeOut();
	});
	
	$( "select[name='parcel[carrier]']" ).change(function(){
		
		$( '.parcel .info' ).fadeOut();
		$( '#shipcloud_create_label').fadeOut();
	});
});