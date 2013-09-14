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
		$composer->getRepositoryManager()->addRepository(
			new Repository( $io, $composer->getConfig(), 86400 )
		);
	}

}
