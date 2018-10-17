<?php
/**
 * This is a template for the class-admin-page.php class.
 *
 * @package wp
 */

namespace Ngearing\Wp;

?>

<div class="wrap" id="myplugin-admin">
	<div id="icon-tools" class="icon32"><br></div>
	<h2><?php echo $this->get_page_title(); ?></h2>
	<?php if ( ! empty( $_GET['updated'] ) ) : ?>
		<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible">
			<p><strong><?php _e( 'Settings saved.' ); ?></strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
		</div>
	<?php endif; ?>
	<form action="options.php" method="POST">
		<?php settings_fields( $this->get_slug() ); ?>
		<?php do_settings_sections( $this->get_slug() ); ?>
		<?php submit_button( __( 'Save' ) ); ?>
	</form>
</div>
