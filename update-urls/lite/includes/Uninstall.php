<?php


namespace KaizenCoders\UpdateURLS;


class Uninstall {
	/**
	 * Init Uninstall
	 *
	 * @since 1.4.9
	 */
	public function init() {
		kc_uu_fs()->add_action( 'after_uninstall', [ $this, 'uninstall_cleanup' ] );
	}

	/**
	 * Delete plugin data
	 *
	 * @since 1.4.9
	 */
	public function uninstall_cleanup() {

	}

}