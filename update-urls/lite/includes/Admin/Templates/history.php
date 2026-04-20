<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap">
	<h2><?php esc_html_e( 'History', 'update-urls' ); ?></h2>

	<div class="bg-white">
		<?php
		if ( ! UU()->is_pro() ) {
			include_once KC_UU_ADMIN_TEMPLATES_DIR . '/pro-promo.php';
		} else {
			do_action( 'kc_uu_render_history_content' );
		}
		?>
	</div>
</div>
