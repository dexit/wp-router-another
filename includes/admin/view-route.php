<?php
	$found = wp_router()->route_by_id( $route_id );
?>

<a href="<?= $this->url;?>">&laquo; Go back</a>
<hr>
<?php if ( $found ): ?>
	<h2>Editing: <code><?= $found->route; ?></code></h2>
	<?php
		do_settings_sections( $this->section );
		settings_fields( $this->section );
		settings_errors();
		submit_button();
	?>
<?php else: ?>
	<strong>Route not found</strong>
<?php endif ?>
