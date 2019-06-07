<?php
	$found = wp_router()->route_by_id( $route_id );
?>

<a href="<?= $this->url;?>">&laquo; Go back</a>
<hr>

<?php if ( $found ): ?>
	<?php settings_fields( 'routerplugin-settings' ); ?>
	<?php do_settings_sections( 'routerplugin-settings' ); ?>
	<?php submit_button(); ?>
<?php else: ?>
	<strong>Route not found</strong>
<?php endif ?>
