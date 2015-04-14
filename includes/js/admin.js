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
		var carrier = $( "select[name='parcel[carrier]']" ).val( );
		var width = $( "input[name='parcel[width]']" ).val();
		var height = $( "input[name='parcel[height]']" ).val();
		var length = $( "input[name='parcel[length]']" ).val();
		var weight = $( "input[name='parcel[weight]']" ).val();
		
		var data = {
			'action': 'shipcloud_calculate_shipping',
			'carrier': carrier,
			'width': width,
			'height': height,
			'length': length,
			'weight': weight
		};
		
		$.post( ajaxurl, data, function( response ) {
			// var result = jQuery.parseJSON( response );
			console.log( response );
		});
	});
});