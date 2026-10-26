<?php
/**
 * Dashboard view.
 *
 * @package WPPYSHardening
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$plugin   = \WPProtectYourSite\Plugin::get_instance();
$registry = $plugin->get_module_registry();
$settings = $plugin->get_settings();
$score    = $registry->calculate_score();
$statuses = $registry->get_statuses();

$modules = array(
	'xmlrpc'           => __( 'XML-RPC Protection', 'kyvora' ),
	'rest_api'         => __( 'REST API Protection', 'kyvora' ),
	'user_enumeration' => __( 'User Enumeration Block', 'kyvora' ),
	'headers'          => __( 'Security Headers', 'kyvora' ),
	'file_editor'      => __( 'File Editor Disable', 'kyvora' ),
	'login_rate_limit' => __( 'Login Rate Limit', 'kyvora' ),
);
?>
<div class="wrap wppys-wrap">
	<h1 class="wp-heading-inline"><?php echo esc_html( get_admin_page_title() ); ?></h1>
	<hr class="wp-header-end">

	<div class="wppys-dashboard">
		<div class="wppys-card wppys-status-card">
			<div class="wppys-status-header">
				<span class="wppys-status-badge wppys-status-<?php echo esc_attr( $registry->get_level_color() ); ?>">
					<?php echo esc_html( $registry->get_level_label() ); ?>
				</span>
				<div class="wppys-score">
					<span class="wppys-score-value"><?php echo esc_html( (string) $score ); ?></span>
					<span class="wppys-score-label"><?php esc_html_e( 'Security Score', 'kyvora' ); ?></span>
				</div>
			</div>
		</div>

		<div class="wppys-card">
			<h2><?php esc_html_e( 'Hardening Modules', 'kyvora' ); ?></h2>
			<p class="description"><?php esc_html_e( 'Turn modules on or off. Every module in this plugin is available without an account.', 'kyvora' ); ?></p>

			<form id="wppys-modules-form">
				<?php wp_nonce_field( 'wppys_admin', 'wppys_nonce_modules' ); ?>
				<table class="form-table">
					<thead>
						<tr>
							<th scope="col"><?php esc_html_e( 'Module', 'kyvora' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Status', 'kyvora' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Action', 'kyvora' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $modules as $key => $title ) : ?>
							<?php
							$enabled = ! empty( $settings['modules'][ $key ] );
							$status  = isset( $statuses[ $key ] ) ? $statuses[ $key ] : array();
							?>
							<tr>
								<td><strong><?php echo esc_html( $title ); ?></strong></td>
								<td>
									<?php if ( isset( $status['status'] ) && 'ok' === $status['status'] ) : ?>
										<span class="dashicons dashicons-yes-alt" style="color:#0F9F6E;"></span>
									<?php elseif ( isset( $status['status'] ) && 'error' === $status['status'] ) : ?>
										<span class="dashicons dashicons-no-alt" style="color:#C81E1E;" title="<?php echo esc_attr( $status['error'] ); ?>"></span>
									<?php else : ?>
										<span class="dashicons dashicons-minus" style="color:#999;"></span>
									<?php endif; ?>
								</td>
								<td>
									<label class="wppys-toggle">
										<input type="checkbox" name="module_<?php echo esc_attr( $key ); ?>" value="1" <?php checked( $enabled ); ?>>
										<span class="wppys-toggle-slider"></span>
									</label>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>

				<h3><?php esc_html_e( 'Protection Level', 'kyvora' ); ?></h3>
				<table class="form-table">
					<tr>
						<th scope="row"><label for="level"><?php esc_html_e( 'Level', 'kyvora' ); ?></label></th>
						<td>
							<select name="level" id="level">
								<option value="essential" <?php selected( $settings['level'], 'essential' ); ?>><?php esc_html_e( 'Essential', 'kyvora' ); ?></option>
								<option value="recommended" <?php selected( $settings['level'], 'recommended' ); ?>><?php esc_html_e( 'Recommended', 'kyvora' ); ?></option>
								<option value="paranoid" <?php selected( $settings['level'], 'paranoid' ); ?>><?php esc_html_e( 'Paranoid', 'kyvora' ); ?></option>
							</select>
							<p class="description"><?php esc_html_e( 'Essential: XML-RPC pingbacks, headers, and login rate limiting. Recommended adds REST user protection, enumeration blocking, and disabling the file editor. Paranoid is the strictest preset.', 'kyvora' ); ?></p>
						</td>
					</tr>
				</table>

				<p class="submit">
					<button type="button" class="button button-primary" id="wppys-save-settings"><?php esc_html_e( 'Save Changes', 'kyvora' ); ?></button>
					<span class="spinner"></span>
				</p>
			</form>
		</div>

		<div class="wppys-card">
			<h2><?php esc_html_e( 'Setup', 'kyvora' ); ?></h2>
			<p class="description"><?php esc_html_e( 'Run the setup wizard again.', 'kyvora' ); ?></p>
			<button type="button" class="button button-secondary" id="wppys-reset-onboarding"><?php esc_html_e( 'Reset setup', 'kyvora' ); ?></button>
		</div>
	</div>
</div>
