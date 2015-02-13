(function($) { $( document ).ready( function() {

	function success() {
		location.reload();
//		$( '.article_protection_form' ).prepend("<p>saved</p>");
	}
	$( '.article_protection_form' ).submit(function(e){
//		console.log( $( this ).serialize() );
		e.preventDefault();
		$.post(wgScriptPath + '/api.php', $( this ).serialize() + '&format=xml', success);
	});
} ); })(jQuery);