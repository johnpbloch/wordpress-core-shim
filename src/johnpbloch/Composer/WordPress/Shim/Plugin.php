<?php

namespace johnpbloch\Composer\WordPress\Shim;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;

class Plugin implements PluginInterface {

	/** @var  Versions */
	private static $_versions;

	public static function get_versions() {
		if ( ! self::$_versions ) {
			self::$_versions = new Versions();
		}
		return self::$_versions;
	}

	/**
	 * Apply plugin modifications to composer
	 *
	 * @param Composer    $composer
	 * @param IOInterface $io
	 */
	public function activate( Composer $composer, IOInterface $io ) {
		$WordPressPackages = array();
		$versions          = array(
			'3.0',
			'3.0.1',
			'3.0.2',
			'3.0.3',
			'3.0.4',
			'3.0.5',
			'3.0.6',
			'3.0.*-dev',
			'3.1',
			'3.1.1',
			'3.1.2',
			'3.1.3',
			'3.1.4',
			'3.1.*-dev',
			'3.2',
			'3.2.1',
			'3.2.*-dev',
			'3.3',
			'3.3.1',
			'3.3.2',
			'3.3.3',
			'3.3.*-dev',
			'3.4',
			'3.4.1',
			'3.4.2',
			'3.4.*-dev',
			'3.5',
			'3.5.1',
			'3.5.2',
			'3.5.*-dev',
			'3.6',
			'3.6.*-dev',
			'dev-master',
		);
		foreach ( $versions as $version ) {
			$WordPressPackages[] = new Package(
				'wordpress-core',
				$version
			);
		}
		$composer->getRepositoryManager()->setRepositoryClass(
			'wordpress-core',
			__NAMESPACE__ . '\\Repository'
		);
		$composer->getRepositoryManager()->addRepository(
			$composer->getRepositoryManager()->createRepository(
				'wordpress-core',
				$WordPressPackages
			)
		);
	}

}
