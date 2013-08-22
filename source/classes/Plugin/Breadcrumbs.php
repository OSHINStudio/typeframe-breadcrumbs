<?php
class Plugin_Breadcrumbs extends Plugin {
	/**
	 * Add a breadcrumb to the end of the system stack.
	 * @param string $title
	 * @param string $url The URL of the breadcrumb. Use the current URL if null.
	 */
	public static function Add($title, $url = null) {
		self::AddBreadcrumb($title, $url);
	}
	/**
	 * Add a breadcrumb to the end of the system stack.
	 * @param string $title
	 * @param string $url
	 */
	public static function AddBreadcrumb($title, $url = null){
		//self::$ManualCrumbs[] = array('name' => $title, 'url' => (is_null($url) ? Typeframe::CurrentPage()->uri() : $url));
		if (is_null($url)) {
			$url = $_SERVER['REQUEST_URI'];
		}
		$parts = parse_url($url);
		$_SESSION['breadcrumbs'][$parts['path']] = array(
			'title' => $title,
			'query' => isset($parts['query']) ? $parts['query'] : ''
		);
	}
	/**
	 * If the specified URL was most recently "drilled into" with a query string, append
	 * the query to the end of the URL.  If the URL already includes a query string, the
	 * saved state is ignored.
	 * @param string $url
	 */
	public static function SavedState($url) {
		$url = Typeframe::Pagemill()->data()->parseVariables(Typeframe_Attribute_Url::ConvertShortUrlToExpression($url));
		if (strpos($url, '?') === false) {
			// Remove trailing slash
			if ( ($url > '/') && (substr($url, strlen($url) - 1, 1) == '/') ) {
				$url = substr($url, 0, strlen($url) - 1);
			}
			return $url . (!empty($_SESSION['breadcrumbs'][$url]['query']) ? '?' . $_SESSION['breadcrumbs'][$url]['query'] : (!empty($_SESSION['breadcrumbs']["{$url}/"]['query']) ? '?' . $_SESSION['breadcrumbs']["{$url}/"]['query'] : ''));
		}
		return $url;
	}
	public function output(\Pagemill_Data $data, \Pagemill_Stream $stream) {
		$data = $data->fork();
		$url = Typeframe::CurrentPage()->uri();
		if (substr($url, -1) == '/') $url = substr($url, 0, -1);
		$dirs = explode('/', substr($url, strlen(TYPEF_WEB_DIR)));
		$this->pluginTemplate = '/breadcrumbs/breadcrumbs.plug.html';
		$data['breadcrumbs'] = array();
		$currentUrl = TYPEF_WEB_DIR;
		$start = $data->parseVariables(Typeframe_Attribute_Url::ConvertShortUrlToExpression($this->getAttribute('start')));
		while (count($dirs) > 0) {
			$currentUrl .= '/' . array_shift($dirs);
			$currentUrl = preg_replace('/\/+/', '/', $currentUrl);
			if ($start && strpos($currentUrl, $start) === false) {
				continue;
			}
			if (isset($_SESSION['breadcrumbs'][$currentUrl])) {
				$bc = $_SESSION['breadcrumbs'][$currentUrl];
				$bc['url'] = $currentUrl . ($bc['query'] ? '?' . $bc['query'] : '');
				$data['breadcrumbs'][] = $bc;
			} else {
				$response = Typeframe::Registry()->responseAt($currentUrl);
				if ($currentUrl == $response->page()->uri()) {
					if ($response->application()->name() != '403' && $response->application()->name() != '404') {
						$settings = $response->page()->settings();
						if (!empty($settings['nickname'])) {
							$title = $settings['nickname'];
						} else {
							$title = $response->page()->title();
						}
						$bc = array(
							'title' => $title,
							'query' => ''
						);
						$_SESSION['breadcrumbs'][$currentUrl] = $bc;
						$bc['url'] = $currentUrl;
						$data['breadcrumbs'][] = $bc;
					}
				}
			}
		}
		parent::output($data, $stream);
		return;
	}
}
