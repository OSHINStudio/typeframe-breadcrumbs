<?php
Pagemill_Data::RegisterExprFunc('breadcrumb', 'Plugin_Breadcrumbs::SavedState');

// Remember query strings for GET requests so the state of a page can be
// included in its breadcrumb link
// The query string can be recovered using Plugin_Breadcrumbs::SavedState($url)
if (Typeframe::CurrentPage()->application()->name() == '403' || Typeframe::CurrentPage()->application()->name() == '404') {
	return;
}
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
	if (!isset($_SESSION['breadcrumbs'])) {
		$_SESSION['breadcrumbs'] = array();
	}
	if (Typeframe::CurrentPage()->uri() == Typeframe::CurrentPage()->applicationUri()) {
		$_SESSION['breadcrumbs'][Typeframe::CurrentPage()->uri()] = array(
			'title' => Typeframe::CurrentPage()->page()->title(),
			'query' => $_SERVER['QUERY_STRING']
		);
	}
	// Clear deeper URLs; i.e., if the user browsed to a page higher in the directory structure
	// (/foo) than a URL with a saved state (/foo/bar), unset the saved state
	foreach ($_SESSION['breadcrumbs'] as $k => $v) {
		if (strlen($k) > strlen(Typeframe::CurrentPage()->uri())) {
			unset($_SESSION['getcrumbs'][$k]);
		}
	}
}
