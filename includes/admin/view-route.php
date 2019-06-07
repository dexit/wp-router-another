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
					<input type="text" name="route-settings-options[title]" style="width: 100%; max-width: 300px;" value="<?= $found->get_meta( 'title' ); ?>">
				</td>
			</tr>
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
