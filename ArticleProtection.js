(function($) { $( document ).ready( function() {

	$( '.article_protection_form' ).submit(function(e){
		console.log( $( this ).serialize() );
		e.preventDefault();
		$.post(wgScriptPath + '/api.php', $( this ).serialize() + '&format=xml');
	});

} ); })(jQuery);