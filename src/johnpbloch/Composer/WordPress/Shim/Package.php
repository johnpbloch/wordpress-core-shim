<?php

namespace johnpbloch\Composer\WordPress\Shim;

use Composer\Package\Link;
use Composer\Package\CompletePackage;
use Composer\Package\Version\VersionParser;

class Package extends CompletePackage {

	public function __construct( $name, $version, $prettyVersion = null ) {
		$versionParser = new VersionParser();
		if ( null === $prettyVersion ) {
			$prettyVersion = $version;
			$version       = $versionParser->normalize( $version );
		}

		parent::__construct( $name, $version, $prettyVersion );

		$this->type = 'wordpress-core';

		$this->sourceType      = 'svn';
		$this->sourceUrl       = 'http://core.svn.wordpress.org/';
		$this->sourceReference = Plugin::get_versions()->getSourceReference( $prettyVersion );

		if ( ! $this->isDev() ) {
			$this->distType         = 'zip';
			$this->distUrl          = Plugin::get_versions()->getUrl( $prettyVersion );
			$this->distSha1Checksum = Plugin::get_versions()->getSha( $prettyVersion );
		}

		$this->requires[] = new Link(
			$this->name,
			'johnpbloch/wordpress-core-installer',
			$versionParser->parseConstraints( '~0.2' ),
			'requires',
			'~0.2'
		);
	}

}
