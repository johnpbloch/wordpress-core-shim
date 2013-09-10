<?php

namespace johnpbloch\Composer\WordPress\Shim;

class Versions {
	protected $versions;

	public function __construct() {
		$this->versions = array(
			'3.0'         => array(
				'sha' => '2085eae56921106228e1e12e3f79e620aa135e2c',
			),
			'3.0.1'       => array(
				'sha' => '5e4336beacae0760ebc24b5815ab0f92f2dd3e4f',
			),
			'3.0.2'       => array(
				'sha' => '3193a8de9b3d2eee836a28cad2caacf7834ee65d',
			),
			'3.0.3'       => array(
				'sha' => 'faa3a7c78703f655eff24b2d0201541256b2539d',
			),
			'3.0.4'       => array(
				'sha' => '7bb4d1d093bfca314aa807d0da92e8247fac7a51',
			),
			'3.0.5'       => array(
				'sha' => 'd50e5e9427d9a8dcf217f3fb19a7a492912bd998',
			),
			'3.0.6'       => array(
				'sha' => '5d2aafd034f69d7994ee10d584bc7392c1ca82a4',
			),
			'3.1'         => array(
				'sha' => '7178dca8876b124f51e8fdd3650282900cecb403',
			),
			'3.1.1'       => array(
				'sha' => '0b11bbc4f031ba1d22a6451efe1e7b9c5bb4713b',
			),
			'3.1.2'       => array(
				'sha' => '83e4e5beac4e72cde759382d39d9477728f945ac',
			),
			'3.1.3'       => array(
				'sha' => '491a8307e17c98a50cef7358fd36107ec9c3df45',
			),
			'3.1.4'       => array(
				'sha' => 'ca2233341acf94e08c13c8441469b3c9ac6bdbea',
			),
			'3.2'         => array(
				'sha' => 'e6db18518f7ac5b65c9c24fbc4c717611508b239',
			),
			'3.2.1'       => array(
				'sha' => 'c0dcdfed1d5daf4821769bc9c21b4f9741357420',
			),
			'3.3'         => array(
				'sha' => '9035bdd6fa290bdbe966d649e5f162a292108518',
			),
			'3.3.1'       => array(
				'sha' => '81a8012a5190ab983fe0eae44cd58f8fb825d622',
			),
			'3.3.2'       => array(
				'sha' => 'b7ab94b5974823ed35af351998fc2657bffbf4d6',
			),
			'3.3.3'       => array(
				'sha' => '0f889a844680d64fd5f119328c69a418d37f90ac',
			),
			'3.4'         => array(
				'sha' => '916c2b02e5c59280ffcef66a284985b5b98f3450',
			),
			'3.4.1'       => array(
				'sha' => 'faf433bdd80c5b88f951301cc1b7fbc5f8d15ab9',
			),
			'3.4.2'       => array(
				'sha' => '72b3d1d9e93eefdb1efc992b89a553485a91b1a2',
			),
			'3.5'         => array(
				'sha' => 'e48b86ba46ff44493b6363c1ef938a0723c780eb',
			),
			'3.5.1'       => array(
				'sha' => 'ddc7ec590e1b16189b310f6e6247a950f017b0e5',
			),
			'3.5.2'       => array(
				'sha' => 'c3b83bb819a8c1a26938f9a3687c293560a6a18e',
			),
			'3.6'         => array(
				'sha' => '3d1808049cf628af31198d2397da9f4047a587e2',
			),
			'latest'      => array(
				'alias' => '3.6'
			),
			'9999999-dev' => array(
				'reference' => 'trunk',
			),
			'dev-master'  => array(
				'alias' => '9999999-dev'
			),
			'trunk'       => array(
				'alias' => '9999999-dev'
			)
		);
	}

	private function resolveVersion( $version ) {
		$version = rtrim( $version, '0.' );
		if ( isset( $this->versions[$version] ) ) {
			if ( isset( $this->versions[$version]['alias'] ) ) {
				return $this->resolveVersion( $this->versions[$version]['alias'] );
			}
			return $version;
		}
		return false;
	}

	private function getVersion( $version ) {
		$version = $this->resolveVersion( $version );
		if ( ! $version ) {
			throw new \InvalidArgumentException;
		}
		return $this->versions[$version];
	}

	public function getSha( $version ) {
		$versionArray = $this->getVersion( $version );
		return $versionArray['sha'] ? : null;
	}

	public function getUrl( $version ) {
		$version = $this->resolveVersion( $version );
		return $version ? "http://wordpress.org/wordpress-$version.zip" : null;
	}

	public function getSourceReference( $version ) {
		$rversion = $this->resolveVersion( $version );
		if ( $rversion ) {
			$versionArray = $this->getVersion( $rversion );
			if ( isset( $versionArray['reference'] ) ) {
				return $versionArray['reference'];
			}
			return "tags/$rversion/";
		} elseif ( preg_match( '/^(\d+\.\d+)-dev$/', $version, $matches ) ) {
			return "branches/{$matches[1]}";
		}
		return null;
	}

}
