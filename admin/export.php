<div class="wrap">
	<?php screen_icon(); ?>

	<h2>
		<?php _e( 'Profiles Export', 'eisma_profiles_exporter' ); ?>
	</h2>

	<?php 
	
	$results = Pronamic_EismaProfilesExpoter_Plugin::get_export();

	if ( ! empty( $results ) ) : ?>

		<h3>
			<?php _e( 'Overview', 'eisma_profiles_exporter' ); ?>
		</h3>

		<table cellspacing="0" class="widefat fixed">
			<thead>
				<tr>
					<th scope="col"><?php _e( 'Post ID', 'eisma_profiles_exporter' ); ?></th>

					<th scope="col"><?php _e( 'First Name', 'eisma_profiles_exporter' ); ?></th>
					<th scope="col"><?php _e( 'Last Name', 'eisma_profiles_exporter' ); ?></th>
					<th scope="col"><?php _e( 'Nickname', 'eisma_profiles_exporter' ); ?></th>

					<th scope="col"><?php _e( 'Function Name', 'eisma_profiles_exporter' ); ?></th>

					<th scope="col"><?php _e( 'Phone Number', 'eisma_profiles_exporter' ); ?></th>
					<th scope="col"><?php _e( 'Mobile Phone Number', 'eisma_profiles_exporter' ); ?></th>

					<th scope="col"><?php _e( 'User ID', 'eisma_profiles_exporter' ); ?></th>
					<th scope="col"><?php _e( 'Username', 'eisma_profiles_exporter' ); ?></th>
					<th scope="col"><?php _e( 'E-mail', 'eisma_profiles_exporter' ); ?></th>

					<th scope="col"><?php _e( 'Locations', 'eisma_profiles_exporter' ); ?></th>
					<th scope="col"><?php _e( 'Companies', 'eisma_profiles_exporter' ); ?></th>
					<th scope="col"><?php _e( 'Departments', 'eisma_profiles_exporter' ); ?></th>
					<th scope="col"><?php _e( 'Publications', 'eisma_profiles_exporter' ); ?></th>
				</tr>
			</thead>
	
			<tbody>
				<?php foreach ( $results as $result ) : ?>
					<tr>
						<td><?php echo $result->post_id; ?></td>

						<td><?php echo $result->profile_first_name; ?></td>
						<td><?php echo $result->profile_last_name; ?></td>
						<td><?php echo $result->profile_nickname; ?></td>

						<td><?php echo $result->profile_function_name; ?></td>

						<td><?php echo $result->profile_phone_number; ?></td>
						<td><?php echo $result->profile_mobile_phone_number; ?></td>

						<td><?php echo $result->user_id; ?></td>
						<td><?php echo $result->user_login; ?></td>
						<td><?php echo $result->user_email; ?></td>

						<?php foreach ( array( 'location', 'company', 'department', 'publication' ) as $taxonomy ) : ?>
			
							<td>
								<?php 
							
								$terms = get_the_terms( $result->post_id, $taxonomy );
	
								if ( $terms && ! is_wp_error( $terms ) ) {
									$categories = array();
									
									foreach ( $terms as $term ) {
										$categories[] = $term->name;
									}
	
									echo implode( ', ', $categories );
								}
	
								?>
							</td>

						<?php endforeach; ?>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

	<?php endif; ?>

	<form method="post" action="">
		<?php wp_nonce_field( 'eisma_profiles_export', 'eisma_profiles_export_nonce' ); ?>

		<p>
			<?php submit_button( __( 'Export to CSV', 'eisma_profiles_exporter' ), 'secondary', 'eisma_profiles_export' ); ?>
		</p>
	</form>

	<?php Pronamic_EismaProfilesExpoter_Plugin::include_file( 'pronamic.php' ); ?>
</div>