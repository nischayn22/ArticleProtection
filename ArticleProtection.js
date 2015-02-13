(function($) { $( document ).ready( function() {

	function success( data ) {
		var txt = "";
		if (data.result.added)
			txt += "<p>Edit permissions added for users: " + data.result.added + "</p>";
		if (data.result.removed)
			txt += "<p>Edit permissions removed for users: " + data.result.removed + "</p>";
		if (txt == '')
			txt = "<p>No changes were done or something went wrong.</p>";

		$( '.result_message' ).html(txt);
	}
	$( '.article_protection_form' ).submit(function(e){
//		console.log( $( this ).serialize() );
		e.preventDefault();
		$.post(wgScriptPath + '/api.php', $( this ).serialize() + '&format=json', success);
	});
} ); })(jQuery);