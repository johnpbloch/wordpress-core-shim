<?php

namespace johnpbloch\Composer\WordPress\Shim;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;

class Plugin implements PluginInterface {

	/**
	 * Apply plugin modifications to composer
	 *
	 * @param Composer    $composer
	 * @param IOInterface $io
	 */
	public function activate( Composer $composer, IOInterface $io ) {
		$WordPressPackages = array(
			'package' => array(
				$this->getPackageConfig( '3.0' ),
				$this->getPackageConfig( '3.0.1' ),
				$this->getPackageConfig( '3.0.2' ),
				$this->getPackageConfig( '3.0.3' ),
				$this->getPackageConfig( '3.0.4' ),
				$this->getPackageConfig( '3.0.5' ),
				$this->getPackageConfig( '3.0.6' ),
				$this->getPackageConfig( '3.0.*-dev' ),
				$this->getPackageConfig( '3.1' ),
				$this->getPackageConfig( '3.1.1' ),
				$this->getPackageConfig( '3.1.2' ),
				$this->getPackageConfig( '3.1.3' ),
				$this->getPackageConfig( '3.1.4' ),
				$this->getPackageConfig( '3.1.*-dev' ),
				$this->getPackageConfig( '3.2' ),
				$this->getPackageConfig( '3.2.1' ),
				$this->getPackageConfig( '3.2.*-dev' ),
				$this->getPackageConfig( '3.3' ),
				$this->getPackageConfig( '3.3.1' ),
				$this->getPackageConfig( '3.3.2' ),
				$this->getPackageConfig( '3.3.3' ),
				$this->getPackageConfig( '3.3.*-dev' ),
				$this->getPackageConfig( '3.4' ),
				$this->getPackageConfig( '3.4.1' ),
				$this->getPackageConfig( '3.4.2' ),
				$this->getPackageConfig( '3.4.*-dev' ),
				$this->getPackageConfig( '3.5' ),
				$this->getPackageConfig( '3.5.1' ),
				$this->getPackageConfig( '3.5.2' ),
				$this->getPackageConfig( '3.5.*-dev' ),
				$this->getPackageConfig( '3.6' ),
				$this->getPackageConfig( '3.6.*-dev' ),
				$this->getPackageConfig( 'dev-master' ),
			)
		);
		$composer->getRepositoryManager()->addRepository(
			$composer->getRepositoryManager()->createRepository(
				'package',
				$WordPressPackages
			)
		);
	}

	private function getPackageConfig( $version ) {
		$config = array(
			'name'              => 'wordpress-core',
			'type'              => 'wordpress-core',
			'version'           => $version,
			'license'           => 'GPL-2+',
			'require'           => array(
				'johnpbloch/wordpress-core-installer' => '~0.2'
			),
			'minimum-stability' => 'dev',
			'source'            => array(
				'type' => 'svn',
				'url'  => 'http://core.svn.wordpress.org/',
			),
		);

		$is_dev = ( false !== strpos( $version, 'dev' ) );

		if ( ! $is_dev ) {
			$config['source']['reference'] = "tags/$version/";
		} elseif ( 'dev-master' === $version || '*@dev' === $version ) {
			$config['source']['reference'] = 'trunk/';
		} else {
			$cleanVersion                  = str_replace( '.*-dev', '', $version );
			$config['source']['reference'] = "branches/$cleanVersion/";
		}

		if ( ! $is_dev ) {
			$config['dist'] = array(
				'type' => 'zip',
				'url'  => "http://wordpress.org/wordpress-$version.zip",
			);
		}

		return $config;
	}

}
