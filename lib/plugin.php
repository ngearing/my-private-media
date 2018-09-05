<?php

namespace MPM;

use MPM\Common\Loader;
use MPM\Common\htaccess;
use MPM\Controllers\Admin;

class Plugin {

	public function __construct( $name, $version, $path, $basename, $url ) {
		$this->name     = $name;
		$this->version  = $version;
		$this->path     = $path;
		$this->basename = $basename;
		$this->url      = $url;

		$this->load_dependencies();

		$this->register_admin_hooks();

		$this->update_htaccess();
	}

	/**
	 * Load dependencies we will be using.
	 *
	 * @return void
	 */
	private function load_dependencies() {
		$this->loader   = new Loader();
		$this->htaccess = new htaccess( wp_upload_dir()['basedir'] . '/.htaccess' );
	}

	/**
	 * Update htaccess rules.
	 *
	 * @return void
	 */
	private function update_htaccess() {
		$file_types       = [ 'pdf', 'doc', 'docx' ];
		$auth_script_path = PATH . 'lib/common/authenticate_user.php';

		$content = sprintf(
			'RewriteEngine on' . PHP_EOL .
			'RewriteCond %%{REQUEST_FILENAME} \.(%s)$' . PHP_EOL .
			'RewriteCond %%{REQUEST_FILENAME} -f' . PHP_EOL .
			'RewriteRule (.*) %s [L]',
			implode( '|', $file_types ),
			$auth_script_path
		);

		$this->htaccess->update(
			$content,
			'mpm'
		);
	}

	private function register_admin_hooks() {
		$admin = new Admin( $this->name, $this->version, $this->path, $this->url );

		$this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_scripts' );

		$this->loader->add_filter( 'manage_media_columns', $admin, 'update_post_columns' );
		$this->loader->add_filter( 'manage_upload_sortable_columns', $admin, 'sort_post_columns' );
		$this->loader->add_action( 'manage_media_custom_column', $admin, 'post_columns_data', 10, 2 );

		$this->loader->add_action( 'wp_ajax_save_meta', $admin, 'save_meta' );
		$this->loader->add_action( 'wp_ajax_nopriv_save_meta', $admin, 'save_meta' );
	}

	public function run() {

		$this->loader->run();

	}
}

