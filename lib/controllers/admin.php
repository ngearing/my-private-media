<?php

namespace MPM\Controllers;

class Admin {



	public function __construct( $plugin_name, $version, $plugin_path, $plugin_url ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$this->plugin_path = $plugin_path;
		$this->plugin_url  = $plugin_url;
	}

	public function enqueue_styles() {

		wp_enqueue_style( "$this->name-admin-js", $this->plugin_url . '/src/styles/admin.css' );

	}

	public function enqueue_scripts() {

		wp_register_script( "$this->name-admin-js", $this->plugin_url . '/src/scripts/admin.js', [], $this->version, true );
		wp_localize_script( "$this->name-admin-js", 'wp', [ 'ajax_url' => admin_url( 'admin-ajax.php' ) ] );
		wp_enqueue_script( "$this->name-admin-js" );

	}

	public function update_post_columns( $columns ) {
		// Remove columns.
		unset( $columns['comments'] );

		// Add columns.
		$columns = array_slice( $columns, 0, 2, true )
				+ [ 'private' => 'Private' ]
				+ array_slice( $columns, 2, count( $columns ), true );

		return $columns;
	}

	public function sort_post_columns( $columns ) {

		$columns['private'] = 'name';

		return $columns;
	}

	public function post_columns_data( $column, $id ) {

		$is_private = get_post_meta( $id, 'is_private', true ) ?: 'false';
		$nonce      = wp_create_nonce( 'save_meta' );

		printf(
			'<label for="cb-private-%2$s">Set to Private?</label>
			<input class="cb-private cb-save" data-nonce="%3$s" id="cb-private-%2$s" name="is_private" type="checkbox" %s value="%s">',
			( ! $is_private || 'false' == $is_private ? false : 'checked' ),
			$id,
			$nonce
		);
	}

	public function save_meta() {

		if ( ! check_admin_referer( 'save_meta' ) ) {
			wp_send_json_error( [ 'message' => 'Failed nonce verification.' ] );
		}

		$key     = isset( $_POST['key'] ) ? wp_unslash( $_POST['key'] ) : false;
		$value   = isset( $_POST['value'] ) ? wp_unslash( $_POST['value'] ) : 'false';
		$post_id = isset( $_POST['post_id'] ) ? wp_unslash( $_POST['post_id'] ) : false;

		$prev = get_post_meta( $post_id, $key, true );

		if ( $prev == 'false' || $value == 'false' ) {
			$updated = delete_post_meta( $post_id, $key );
		} else {
			$updated = update_post_meta( $post_id, $key, $value, $prev );
		}

		$return = [
			$key,
			$value,
			$post_id,
			$updated,
		];

		if ( $updated ) {
			wp_send_json_success( $return );
		} else {
			wp_send_json_error( $return );
		}
	}
}
