jQuery( function( $ ) {
	$( '.btn_edit_address' ).click(
		function(){
			var div_address = $( this ).parent().parent().find( ".address" );
			var div_edit_address = $( this ).parent().parent().find( ".edit_address" );
			
			console.log( div_edit_address );
			
			div_address.hide();
			div_edit_address.show();
		}
	);
	
});