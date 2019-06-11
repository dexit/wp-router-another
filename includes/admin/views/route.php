<?php
	$found = wp_router()->route_by_id( $route_id );
?>

<a href="<?= $this->url;?>">&laquo; Go back</a>
<hr>
<?php if ( $found ): ?>
	<h2>Editing: <code><?= $found->route; ?></code></h2>
	<input type="hidden" name="route-id" value="<?= $route_id; ?>">
	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row">Route title</th>
				<td>
					<input type="text" name="route-settings-options[title]" style="width: 100%; max-width: 300px;" value="<?= $found->get_option( 'title' ); ?>">
				</td>
			</tr>
			<?php foreach ( $found->options as $key => $value ): ?>
				<?php if ( $key !== 'title' ): ?>
					<tr>
						<th scope="row"><?= $key; ?></th>
						<td><code><?php
							if ( is_bool( $value ) ) {
								echo $value ? 'true' : 'false';
							} else {
								echo $value;
							}
						?></code></td>
					</tr>
				<?php endif ?>
			<?php endforeach ?>
		</tbody>
	</table>
	<?php
		settings_fields( $this->slug );
		//do_settings_sections( $this->slug );
		settings_errors();
		submit_button();
	?>
<?php else: ?>
	<strong>Route not found</strong>
<?php endif ?>
