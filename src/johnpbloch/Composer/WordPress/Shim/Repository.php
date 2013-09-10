<?php

namespace johnpbloch\Composer\WordPress\Shim;

use Composer\Config;
use Composer\IO\IOInterface;
use Composer\Repository\ArrayRepository;

class Repository extends ArrayRepository {

	/** @var IOInterface */
	private $io;
	/** @var Config */
	private $config;

	public function __construct( array $packages, IOInterface $io, Config $config ) {
		$this->io     = $io;
		$this->config = $config;
		parent::__construct( $packages );
	}

	public function findPackages( $name, $version = null ) {
		$this->io->write( sprintf( 'Finding package $s...', $name ) );
		parent::findPackages( $name, $version );
	}

}
