(function($) { $( document ).ready( function() {
	$('#pt-watchlist').after('<li id="pt-article-protection"><a href="/mediawiki/index.php?title=Special:ArticleProtection/UserPermissions:' + wgUserName + '" title="See permissions for your pages" >' + mw.message( 'pages-link' ).text() + '</a></li>');
} ); })(jQuery);