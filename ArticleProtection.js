(function($) { $( document ).ready( function() {


	function show_message( txt ) {
		$( '.result_message' ).html(txt);
	}

	function success( data ) {
		var txt = '';

		if (typeof data.result != 'undefined') {
			if (typeof data.result.added != 'undefined')
				txt += "<p>Success! Edit permissions added for users: " + data.result.added + "</p>";
			if (typeof data.result.removed != 'undefined')
				txt += "<p>Success! Edit permissions removed for users: " + data.result.removed + "</p>";
			if (typeof data.result.username_error != 'undefined')
				txt += "<p>Error! This seems to be an invalid username: " + data.result.username_error + "</p>";
			if (typeof data.result.owner_error != 'undefined')
				txt += "<p>Error! The following user cannot be granted edit permissions as the user is an owner of the page: " + data.result.owner_error + "</p>";
			if (typeof data.result.editor_limit_exceeded != 'undefined')
				txt += "<p>Error! You have exceeded the maximum allowed editors for a page. The current limit is: " + data.result.editor_limit_exceeded + "</p>";
		} else {
			txt = "<p>No changes were done or something went wrong.</p>";
		}

		show_message( txt );
	}
	$( '.article_protection_form' ).submit(function(e){
//		console.log( $( this ).serialize() );
		$( '.result_message' ).empty();
		e.preventDefault();
		$.post(wgScriptPath + '/api.php', $( this ).serialize() + '&format=json', success);
	});

} ); })(jQuery);