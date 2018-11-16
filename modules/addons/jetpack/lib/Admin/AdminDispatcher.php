<?php

include_once __DIR__ . '/AdminController.php';

/**
 * Dispatch handler for Jetpack admin pages
 */
class AdminDispatcher {
	/**
	 * Undocumented function
	 *
	 * @param array  $params Params for module dispatch action.
	 * @param string $action Dispatch action to perform.
	 * @return mixed controller action or error string if action is invalid.
	 */
	public function dispatch( $params, $action ) {
		$controller = new AdminController();
		if ( is_callable( array( $controller, $action ) ) ) {
			return $controller->$action( $params );
		}
		return '<p>Invalid action requested. Please go back and try again.</p>';

	}
}
