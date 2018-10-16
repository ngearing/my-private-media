<?php

namespace MPM;

use MPM\Common\Loader;
use MPM\Common\htaccess;
use MPM\Common\Admin_Page;
use MPM\Controllers\Admin;

class Plugin {

	public function __construct( $name, $version, $path, $basename, $url ) {
		$this->name     = $name;
		$this->version  = $version;
		$this->path     = $path;
		$this->basename = $basename;
		$this->url      = $url;

		$this->load_dependencies();

		$this->register_admin_pages();

		$this->register_admin_hooks();

		// TODO: only run this function when settings updated.
		// FIX: Running this on every page load can cause some kind of apache loop
		// which ends up resolving as the authenticate_user.php file itself and sening
		// it to the browser.
		// $this->update_htaccess();
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

	private function register_admin_pages() {
		$admin_page = new Admin_Page( 'My Private Media', [ 'template_path' => dirname( __FILE__ ) . '/common/' ] );

		$this->loader->add_action( 'admin_menu', $admin_page, 'register' );
		$this->loader->add_action( 'admin_menu', $admin_page, 'configure' );
	}

	/**
	 * Update htaccess rules.
	 *
	 * @return void
	 */
	private function update_htaccess() {
		$file_types       = [ 'pdf' ]; // TODO add setting to change this.
		$auth_script_path = URL . 'lib/common/authenticate_user.php';

		$content = sprintf(
			'RewriteEngine On' . PHP_EOL .
			'# Check if file exists' . PHP_EOL .
			'RewriteCond %%{REQUEST_FILENAME} -f' . PHP_EOL .
			'# And is a pdf' . PHP_EOL .
			'RewriteCond %%{REQUEST_FILENAME} \.(%s)$ [NC]' . PHP_EOL .
			'# Check if cookie exists' . PHP_EOL .
			'RewriteCond %%{HTTP_COOKIE}          !%s [OR]' . PHP_EOL .
			'# cookie has value' . PHP_EOL .
			'RewriteCond %%{HTTP_COOKIE}          %2$s=(\w+)' . PHP_EOL .
			'# cookie matches token' . PHP_EOL .
			'RewriteCond %s/token-%%1       !-f' . PHP_EOL .
			'RewriteRule (.*) %s?file=$1 [L,QSA]',
			implode( '|', $file_types ),
			'mpm_auth',
			stripslashes( ABSPATH . 'tmp' ),
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

		$this->loader->add_action( 'admin_init', $this, 'handle_submit' );
	}

	/**
	 * Handle admin form submittions.
	 *
	 * @return void
	 */
	public function handle_submit() {
		if ( ! isset( $_POST['mpm'] ) ) {
			return;
		}

		$action = $_POST['mpm']['action'];

		if ( $action && method_exists( $this, $action ) ) {
			$this->$action();
		}
	}

	public function run() {

		$this->loader->run();

	}
}
