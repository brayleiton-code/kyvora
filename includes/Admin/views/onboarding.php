<?php
/**
 * Setup wizard view.
 *
 * @package WPPYSHardening
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap wppys-wrap wppys-onboarding">
	<div class="wppys-onboarding-header">
		<h1><?php esc_html_e( 'Kyvora', 'kyvora' ); ?></h1>
		<div class="wppys-steps">
			<span class="<?php echo 1 === $step || 2 === $step ? 'active' : ''; ?>">1</span>
			<span class="<?php echo 2 === $step ? 'active' : ''; ?>">2</span>
		</div>
	</div>

	<form method="post" class="wppys-onboarding-form">
		<?php wp_nonce_field( 'wppys_onboarding_step_' . $step ); ?>
		<input type="hidden" name="wppys_onboarding_step" value="<?php echo esc_attr( (string) $step ); ?>">

		<?php if ( 1 === $step ) : ?>
			<h2><?php esc_html_e( 'Step 1: Protection level', 'kyvora' ); ?></h2>
			<p><?php esc_html_e( 'Choose how strictly to harden this site. You can change this later.', 'kyvora' ); ?></p>

			<div class="wppys-level-cards">
				<label class="wppys-level-card">
					<input type="radio" name="level" value="essential" <?php checked( $level, 'essential' ); ?>>
					<h3><?php esc_html_e( 'Essential', 'kyvora' ); ?></h3>
					<ul>
						<li><?php esc_html_e( 'XML-RPC pingback protection', 'kyvora' ); ?></li>
						<li><?php esc_html_e( 'Security headers', 'kyvora' ); ?></li>
						<li><?php esc_html_e( 'Login rate limiting', 'kyvora' ); ?></li>
					</ul>
				</label>
				<label class="wppys-level-card">
					<input type="radio" name="level" value="recommended" <?php checked( $level, 'recommended' ); ?>>
					<h3><?php esc_html_e( 'Recommended', 'kyvora' ); ?></h3>
					<ul>
						<li><?php esc_html_e( 'Everything in Essential', 'kyvora' ); ?></li>
						<li><?php esc_html_e( 'REST API user protection', 'kyvora' ); ?></li>
						<li><?php esc_html_e( 'User enumeration block', 'kyvora' ); ?></li>
						<li><?php esc_html_e( 'Disable the file editor', 'kyvora' ); ?></li>
					</ul>
				</label>
				<label class="wppys-level-card">
					<input type="radio" name="level" value="paranoid" <?php checked( $level, 'paranoid' ); ?>>
					<h3><?php esc_html_e( 'Paranoid', 'kyvora' ); ?></h3>
					<ul>
						<li><?php esc_html_e( 'Everything in Recommended', 'kyvora' ); ?></li>
						<li><?php esc_html_e( 'Strictest local hardening preset', 'kyvora' ); ?></li>
					</ul>
				</label>
			</div>
			<p class="submit">
				<button type="submit" class="button button-primary"><?php esc_html_e( 'Continue', 'kyvora' ); ?></button>
			</p>
		<?php else : ?>
			<h2><?php esc_html_e( 'Step 2: Confirm', 'kyvora' ); ?></h2>
			<p>
				<?php
				printf(
					/* translators: %s: protection level name. */
					esc_html__( 'Protection level: %s. All of these protections run on this site. No account is required.', 'kyvora' ),
					esc_html( ucfirst( $level ) )
				);
				?>
			</p>
			<p class="submit">
				<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=kyvora&wppys_onboarding=1' ) ); ?>"><?php esc_html_e( 'Back', 'kyvora' ); ?></a>
				<button type="submit" class="button button-primary"><?php esc_html_e( 'Protect this site', 'kyvora' ); ?></button>
			</p>
		<?php endif; ?>
	</form>
</div>
