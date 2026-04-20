<?php

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

$is_pro    = UU()->is_pro();
$tab       = ! empty( $_GET['tab'] ) ? \KaizenCoders\UpdateURLS\Helper::clean( $_GET['tab'] ) : 'backup-import';
$tools_url = admin_url( 'admin.php?page=update-urls-tools' );

?>

<div class="wrap">

	<h2><?php esc_html_e( 'Tools', 'update-urls' ); ?></h2>

	<h2 class="nav-tab-wrapper">
		<a href="<?php echo esc_url( add_query_arg( 'tab', 'backup-import', $tools_url ) ); ?>"
		   class="nav-tab <?php echo 'backup-import' === $tab ? 'nav-tab-active' : ''; ?>">
			<?php esc_html_e( 'Backup & Import', 'update-urls' ); ?>
		</a>
		<a href="<?php echo esc_url( add_query_arg( 'tab', 'system-info', $tools_url ) ); ?>"
		   class="nav-tab <?php echo 'system-info' === $tab ? 'nav-tab-active' : ''; ?>">
			<?php esc_html_e( 'System Info', 'update-urls' ); ?>
		</a>
	</h2>

	<?php if ( 'backup-import' === $tab ) : ?>
		<div class="bg-white">
			<?php if ( $is_pro ) : ?>
				<?php do_action( 'kc_uu_render_backup_import_content' ); ?>
			<?php else : ?>
				<?php include_once KC_UU_ADMIN_TEMPLATES_DIR . '/pro-promo.php'; ?>
			<?php endif; ?>
		</div>
	<?php elseif ( 'system-info' === $tab ) : ?>
		<div class="bg-white">
			<div class="flex flex-auto">
				<div class="p-10 min-h-full w-full">
					<div class="grid grid-col-3">
						<textarea readonly="readonly" class="w-full h-96 text-white bg-black font-mono text-xs p-3"
						          onclick="this.focus(); this.select()"
						          name="kc-uu-sysinfo"><?php echo esc_textarea( \KaizenCoders\UpdateURLS\Tracker::get_system_info() ); ?></textarea>
					</div>
				</div>
			</div>
		</div>
	<?php endif; ?>

</div>
