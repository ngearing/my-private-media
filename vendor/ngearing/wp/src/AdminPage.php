<?php
/**
 * Admin page functionality.
 *
 * @package wp
 */

namespace Ngearing\Wp;

/**
 * Admin page class
 *
 * Handles the setting up and render of an admin page.
 */
class AdminPage {

	public function __construct(
		$menu_title = 'My Plugin',
		$args = [
			'menu_slug',
			'page_title',
			'capability',
			'parent_slug',
			'template_path',
			'template_name',
			'render_func',
		]
	) {
		$this->title         = $menu_title;
		$this->slug          = isset( $args['menu_slug'] ) ? $args['menu_slug'] : strtolower( str_replace( ' ', '_', $menu_title ) );
		$this->page_title    = isset( $args['page_title'] ) ? $args['page_title'] : "$menu_title Admin Page";
		$this->capability    = isset( $args['capability'] ) ? $args['capability'] : 'install_plugins';
		$this->parent_slug   = isset( $args['parent_slug'] ) ? $args['parent_slug'] : 'options-general.php';
		$this->template_path = isset( $args['template_path'] ) ? $args['template_path'] : __DIR__;
		$this->template_name = isset( $args['template_name'] ) ? $args['template_name'] : 'admin-page-template';
		$this->render_func   = isset( $args['render_func'] ) ? $args['render_func'] : '';
	}

	/**
	 * Configure the admin page using Settings API.
	 *
	 * @return void
	 */
	public function configure() {
		// Register setting.
		\register_setting( $this->get_slug(), $this->get_slug() . '_option' );

		// Register section and field.
		\add_settings_section(
			$this->get_slug() . '-section',
			__( 'Section Title', $this->get_slug() ),
			[ $this, 'render_section' ],
			$this->get_slug()
		);
		\add_settings_field(
			$this->get_slug() . '-api-status',
			__( 'My option', $this->get_slug() ),
			[ $this, 'render_option_field' ],
			$this->get_slug(),
			$this->get_slug() . '-section'
		);
	}

	/**
	 * Register the menu.
	 *
	 * Should be called in the admin_menu action.
	 *
	 * @return void
	 */
	public function register() {
		call_user_func_array( 'add_submenu_page', $this->get_page_arguments() );
	}

	/**
	 * Renders the option field.
	 *
	 * @return void
	 */
	public function render_option_field() {
		$this->render_template( 'admin-page-option_field' );
	}

	/**
	 * Render the top section of plugin's admin page.
	 *
	 * @return void
	 */
	public function render_section() {
		$this->render_template( 'admin-page-section' );
	}

	/**
	 * Render the plugins' admin page.
	 *
	 * @return void
	 */
	public function render_page() {
		$this->render_template( $this->template_name );
	}

	/**
	 * Renders the given template if it's readable.
	 *
	 * @param string $template The template name.
	 * @return void
	 */
	private function render_template( $template_name, $template_path = '' ) {
		$template = ( $template_path ?: $this->template_path ) . '/' . $template_name . '.php';

		if ( ! is_readable( $template ) ) {
			return;
		}

		include $template;
	}

	/**
	 * Get the page arguments to register this admin page.
	 *
	 * @return array
	 */
	public function get_page_arguments() {
		return [
			$this->get_parent_slug(),
			$this->get_page_title(),
			$this->get_title(),
			$this->get_capability(),
			$this->get_slug(),
			$this->get_render_func() ?: [ $this, 'render_page' ],
		];
	}

	public function get_parent_slug() {
		return $this->parent_slug;
	}

	public function get_page_title() {
		return $this->page_title;
	}

	public function get_title() {
		return $this->title;
	}

	public function get_capability() {
		return $this->capability;
	}

	public function get_slug() {
		return $this->slug;
	}

	public function get_template_path() {
		return $this->template_path;
	}

	public function get_render_func() {
		return $this->render_func;
	}
}
