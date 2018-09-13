<?php

/**
 * Authenticate User before servicing a file.
 */

// Get WordPress location.
$script   = realpath( filter_input( INPUT_SERVER, 'SCRIPT_FILENAME' ) );
$web_root = filter_input( INPUT_SERVER, 'DOCUMENT_ROOT' );

// The requested file.
$request_file = filter_input( INPUT_GET, 'file' );

if ( ! $request_file || __FILE__ !== $script ) {
	exit;
}

// Init WordPress.
define( 'WP_USE_THEMES', false );
require_once $web_root . '/wp-load.php';

// TODO add this option to settings.
$force_download = false;

// Check if the file is private.
global $wpdb;
$is_private = $wpdb->get_var(
	$wpdb->prepare(
		"SELECT meta_value
        FROM $wpdb->postmeta
        WHERE meta_key = 'is_private'
		AND meta_value = 'true'
        AND post_id = (
            SELECT id
            FROM $wpdb->posts
            WHERE post_name = %s
        )",
		sanitize_title( pathinfo( $request_file )['filename'] )
	)
);

// Get the full path.
$file_path = wp_upload_dir()['basedir'] . "/$request_file";
$file_url  = wp_upload_dir()['baseurl'] . "/$request_file";

// If the file is NOT private OR the user is logged in.
// Serve the file.
if ( ! $is_private || is_user_logged_in() ) {

	try {

		if ( ! file_exists( $file_path ) ) {
			throw new Exception( 'File does not exist.' );
		}

		if ( ! is_readable( $file_path ) ) {
			throw new Exception( 'File is not readable.' );
		}

		// Get file mime type.
		$finfo = finfo_open( FILEINFO_MIME_TYPE );
		// Send mime type AND replace status.
		// WordPress will send a 404 status as it does not recognise this script.
		header( 'Content-Type: ' . finfo_file( $finfo, $file_path ), true, 200 );
		finfo_close( $finfo );

		if ( $force_download ) {
			// Use Content-Disposition: attachment to specify the filename.
			header( 'Content-Disposition: attachment; filename=' . basename( $file_path ) );
		}

		// No cache.
		header( 'Expires: 0' );
		header( 'Cache-Control: must-revalidate' );
		header( 'Pragma: public' );

		// Define file size.
		header( 'Content-Length: ' . filesize( $file_path ) );

		// Send the file.
		ob_clean();
		flush();
		readfile( $file_path );

	} catch ( Exception $e ) {
		exit( $e->getMessage() );
	}
} else { // Else redirect to the login page.

	wp_safe_redirect(
		esc_url(
			wp_login_url( $file_url )
		)
	);

}

exit();
