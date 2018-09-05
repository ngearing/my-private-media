<?php

namespace MPM;

use MPM\Common\htaccess;

class Activator {

	public static function run() {
		self::install_htaccess();
	}

	public static function install_htaccess() {

		$htacces = new htaccess( wp_upload_dir()['basedir'] . '/.htaccess' );
		$htacces->create(
			'# Uploads auth rules go here.',
			'mpm'
		);

	}
}
