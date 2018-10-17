<?php

namespace Ngearing\Wp;

class PostType {

	/**
	 * Name of post type, lowercase no spaces.
	 *
	 * @var string
	 */
	public $name;

	/**
	 * Title of post type.
	 *
	 * @var string
	 */
	public $title;

	/**
	 * Plural of post type.
	 *
	 * @var string
	 */
	public $plural;

	/**
	 * The post type object
	 *
	 * @var object
	 */
	public $post_type;

	/**
	 * Array of args.
	 *
	 * @var array
	 */
	private $args;

	public function __construct( $name, $args = [] ) {

		if ( \is_array( $name ) ) {
			$plural = $name[2] ?: false;
			$title  = $name[1] ?: false;
			$name   = $name[0];
		}
		$this->name   = $name;
		$this->title  = $title ?: ucwords( str_replace( [ '-', '_' ], ' ', $name ) );
		$this->plural = $plural ?: $this->pluralize( $this->title );

		if ( post_type_exists( $this->name ) ) {
			$this->post_type = get_post_type_object( $this->name );
		}

		$this->filter_args( $args );

		$this->post_type = register_post_type( $this->name, $this->args );

	}

	/**
	 * Set the title placeholder.
	 *
	 * @param string $text
	 * @return void
	 */
	public function set_title_placeholder( $text = '' ) {

		global $pagenow;

		if ( ! 'post-new.php' === $pagenow ) {
			return;
		}

		$this->title_placeholder = $text ?: $this->post_type->labels->singular_name . ' name here...';

		add_filter( 'enter_title_here', [ $this, 'get_title_placeholder' ] );

	}

	public function get_title_placeholder( $title ) {
		global $current_screen;

		if ( $current_screen && $this->name === $current_screen->post_type ) {
			return $this->title_placeholder;
		}
		return $title;
	}

	private function filter_args( $args ) {
		// For labels.
		$name   = $this->name;
		$title  = $this->title;
		$plural = $this->plural;

		$default_args = [
			'label'        => $name,
			'labels'       => [
				'name'                  => _x( $plural, 'post type general name' ),
				'singular_name'         => _x( $title, 'post type singular name' ),
				'add_new'               => _x( 'Add New', $name ),
				'add_new_item'          => __( "Add New $title" ),
				'edit_item'             => __( "Edit $title" ),
				'new_item'              => __( "New $title" ),
				'view_item'             => __( "View $title" ),
				'view_items'            => __( "View $plural" ),
				'search_items'          => __( "Search $plural" ),
				'not_found'             => __( "No $plural found." ),
				'not_found_in_trash'    => __( "No $plural found in Trash." ),
				'parent_item_colon'     => __( "Parent $title:" ),
				'all_items'             => __( "All $plural" ),
				'archives'              => __( "$title Archives" ),
				'attributes'            => __( "$title Attributes" ),
				'insert_into_item'      => __( "Insert into $title" ),
				'uploaded_to_this_item' => __( "Uploaded to this $title" ),
				'featured_image'        => _x( 'Featured Image', $name ),
				'set_featured_image'    => _x( 'Set featured image', $name ),
				'remove_featured_image' => _x( 'Remove featured image', $name ),
				'use_featured_image'    => _x( 'Use as featured image', $name ),
				'filter_items_list'     => __( "Filter $plural list" ),
				'items_list_navigation' => __( "$plural list navigation" ),
				'items_list'            => __( "$plural list" ),
			],
			'public'       => true,
			'show_in_rest' => true,
			'supports'     => [ 'title', 'author', 'revisions', 'editor' ],
			'has_archive'  => true,
		];

		if ( isset( $this->post_type ) ) {
			$default_args = (array) $this->post_type;
		}

		$this->args = array_replace_recursive( $default_args, $args );
	}

	/**
	 * Basic Pluralize function
	 *
	 * @param string $singular Singular form of word.
	 * @return string Pluralized word if quantity is not one, otherwise singular
	 */
	private function pluralize( $singular ) {
		$last_letter = strtolower( $singular[ strlen( $singular ) - 1 ] );
		switch ( $last_letter ) {
			case 'y':
				return substr( $singular, 0, -1 ) . 'ies';
			case 's':
				return $singular . 'es';
			default:
				return $singular . 's';
		}
	}
}
