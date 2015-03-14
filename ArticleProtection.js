(function($) { $( document ).ready( function() {

	function success( data ) {
		var txt = "";
		if (data.result.added)
			txt += "<p>Success! Edit permissions added for users: " + data.result.added + "</p>";
		if (data.result.removed)
			txt += "<p>Success! Edit permissions removed for users: " + data.result.removed + "</p>";
		if (data.result.username_error)
			txt += "<p>Error! This seems to be an invalid username: " + data.result.username_error + "</p>";
		if (data.result.editor_limit_exceeded)
			txt += "<p>Error! You have exceeded the maximum allowed editors for a page. The current limit is: " + data.result.editor_limit_exceeded + "</p>";
		if (txt == '')
			txt = "<p>No changes were done or something went wrong.</p>";

		$( '.result_message' ).html(txt);
	}
	$( '.article_protection_form' ).submit(function(e){
//		console.log( $( this ).serialize() );
		$( '.result_message' ).empty();
		e.preventDefault();
		$.post(wgScriptPath + '/api.php', $( this ).serialize() + '&format=json', success);
	});

} ); })(jQuery);