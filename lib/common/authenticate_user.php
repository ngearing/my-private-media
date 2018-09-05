<?php
/**
 * Authenticate User before servicing a file.
 *
 * @package mpm
 */

// Get WordPress location.
$parsed_uri = explode( 'wp-content', filter_input( INPUT_SERVER, 'SCRIPT_FILENAME' ) );
// The requested file.
$request_uri = filter_input( INPUT_SERVER, 'REQUEST_URI' );

// Init WordPress.
define( 'WP_USE_THEMES', false );
require_once $parsed_uri[0] . 'wp-blog-header.php';

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
		sanitize_title( pathinfo( $request_uri )['filename'] )
	)
);

// If the file is NOT private OR the user is logged in.
// Serve the file.
if ( ! $is_private || is_user_logged_in() ) {

	$file = wp_normalize_path( ABSPATH . $request_uri );

	try {

		if ( ! file_exists( $file ) ) {
			throw new Exception( 'File does not exist.' );
		}

		if ( ! is_readable( $file ) ) {
			throw new Exception( 'File is not readable.' );
		}

		// Get file mime type.
		$finfo = finfo_open( FILEINFO_MIME_TYPE );
		// Send mime type AND replace status.
		// WordPress will send a 404 status as it does not recognise this script.
		header( 'Content-Type: ' . finfo_file( $finfo, $file ), true, 200 );
		finfo_close( $finfo );

		// Use Content-Disposition: attachment to specify the filename.
		header( 'Content-Disposition: attachment; filename=' . basename( $file ) );

		// No cache.
		header( 'Expires: 0' );
		header( 'Cache-Control: must-revalidate' );
		header( 'Pragma: public' );

		// Define file size.
		header( 'Content-Length: ' . filesize( $file ) );

		ob_clean();
		flush();
		readfile( $file );

	} catch ( Exception $e ) {
		exit( $e->getMessage() );
	}
} else { // Else redirect to the login page.

	wp_safe_redirect(
		esc_url(
			wp_login_url( $request_uri )
		)
	);

}

exit();
