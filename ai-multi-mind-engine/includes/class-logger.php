<?php
/**
 * API Logger
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AMM_Logger {

	/**
	 * Log an API interaction
	 */
	public static function log( $provider, $request, $response ) {
		$log_enabled = get_option( 'amm_api_logging', 'no' ) === 'yes';
		if ( ! $log_enabled ) return;

		$upload_dir = wp_upload_dir();
		$log_file = $upload_dir['basedir'] . '/amm-api-debug.log';
		$entry = sprintf(
			"[%s] PROVIDER: %s\nREQUEST: %s\nRESPONSE: %s\n---\n",
			current_time( 'mysql' ),
			$provider,
			substr( json_encode( $request ), 0, 500 ),
			substr( json_encode( $response ), 0, 500 )
		);

		file_put_contents( $log_file, $entry, FILE_APPEND );
	}
}
