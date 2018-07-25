
$(function() {

	es.reload = function() {
		es.redirect(window.location.href);
	}

	es.redirect = function(url) {
		window.location.replace(url);
	}

})