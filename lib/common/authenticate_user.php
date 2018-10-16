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

	// Generate nonce.
	$nonce = wp_create_nonce( 'mpm_nonce' );

	// Create cookie.
	setcookie( 'mpm_auth', $nonce, time() + 10, '/wp-content/uploads' );

	$root = $_SERVER['DOCUMENT_ROOT'];

	// Delete any existsing tokens.
	if ( ! is_readable( "$root/tmp" ) ) {
		mkdir( "$root/tmp" );
	}

	foreach ( scandir( "$root/tmp/" ) as $token ) {
		// Remove '.' '..' and any hidden files beginning with .
		if ( 0 === strpos( $token, '.' ) ) {
			continue;
		}

		if ( filemtime( $token ) < time() + 10 ) {
			unlink( "$root/tmp/$token" );
		}
	}

	// Create token file.
	touch( "$root/tmp/token-$nonce" );

	// Redirect to the file now.
	// Apache will check the cookie against the token.
	wp_safe_redirect( $file_url );

} else { // Else redirect to the login page.

	wp_safe_redirect(
		esc_url(
			wp_login_url( $file_url )
		)
	);

}

exit();
