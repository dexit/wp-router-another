<table class="widefat striped">
	<thead>
		<tr>
			<td><?= __( 'Route', 'wprouter' ); ?></td>
			<td><?= __( 'Title', 'wprouter' ); ?></td>
			<td><?= __( 'Method(s)', 'wprouter' ); ?></td>
			<td><?= __( 'Permissions', 'wprouter' ); ?></td>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td><?= __( 'Route', 'wprouter' ); ?></td>
			<td><?= __( 'Title', 'wprouter' ); ?></td>
			<td><?= __( 'Method(s)', 'wprouter' ); ?></td>
			<td><?= __( 'Permissions', 'wprouter' ); ?></td>
		</tr>
	</tfoot>
	<tbody>
		<?php if ( $routes = wp_router()->get_routes() ): ?>
			<?php foreach ( $routes as $i => $route ): ?>
				<tr>
					<td>
						<strong><?= $route->route; ?></strong>
						<div class="row-actions">
							<span class="edit"><a href="<?= add_query_arg( 'route', $route->id, $this->url ); ?>"><?= __( 'Settings', 'wprouter' ); ?></a></span>
						</div>
					</td>
					<td>
						<?= $route->get_option( 'title' ) ?: '-'; ?>
					</td>
					<td>
						<?= implode( ', ', array_map( function( $el ) {
							return "<code>{$el}</code>";
						}, $route->methods ) ); ?>
					</td>
					<td>
						<?= $route->options[ 'private' ] ? 'Private' : 'Public'; ?>
					</td>
				</tr>
			<?php endforeach ?>
		<?php else: ?>
			<tr>
				<td colspan="3">
					<?= __( 'No routes', 'wprouter' ); ?>
				</td>
			</tr>
		<?php endif ?>
	</tbody>
</table>
