<?php

namespace MPM;

use MPM\Common\htaccess;

class Deactivator {

	public static function run() {
		self::uninstall_htaccess();
	}

	public static function uninstall_htaccess() {

		$htacces = new htaccess( wp_upload_dir()['basedir'] . '/.htaccess' );
		$htacces->delete(
			'mpm'
		);
	}
}
