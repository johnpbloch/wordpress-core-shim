<?php

namespace johnpbloch\Composer\WordPress\Shim;

use Composer\Cache;
use Composer\Config;
use Composer\IO\IOInterface;
use Composer\Package\Loader\ArrayLoader;
use Composer\Package\Loader\ValidatingArrayLoader;
use Composer\Package\PackageInterface;
use Composer\Package\Version\VersionParser;
use Composer\Repository\RepositoryInterface;
use Composer\Util\RemoteFilesystem;

class Repository implements RepositoryInterface {

	const PACKAGE_NAME = 'wordpress-core';

	/** @var IOInterface */
	protected $io;
	/** @var Cache */
	protected $cache;
	/** @var Config */
	protected $config;
	/** @var array */
	protected $versions;
	/** @var int */
	protected $ttl;
	/** @var ValidatingArrayLoader */
	protected $loader;

	/**
	 * @param IOInterface $io
	 * @param Config      $config
	 * @param int         $cacheTTL
	 */
	public function __construct( IOInterface $io, Config $config, $cacheTTL = null ) {
		$this->io     = $io;
		$this->config = $config;
		$this->cache  = new Cache( $io, $config->get( 'cache-repo-dir' ) . '/wordpress-core-versions' );
		$this->ttl    = $cacheTTL;
	}

	/**
	 * Checks if specified package registered (installed).
	 *
	 * @param PackageInterface $package package instance
	 *
	 * @return bool
	 */
	public function hasPackage( PackageInterface $package ) {
		return (
			static::PACKAGE_NAME === $package->getName() &&
			$this->isValidVersion( $package->getVersion() )
		);
	}

	/**
	 * Searches for the first match of a package by name and version.
	 *
	 * @param string $name    package name
	 * @param string $version package version
	 *
	 * @return PackageInterface|null
	 */
	public function findPackage( $name, $version ) {
		if ( static::PACKAGE_NAME === $name && $this->isValidVersion( $version ) ) {
			return $this->getPackage( $version );
		}
		return null;
	}

	/**
	 * Searches for all packages matching a name and optionally a version.
	 *
	 * @param string $name    package name
	 * @param string $version package version
	 *
	 * @return array
	 */
	public function findPackages( $name, $version = null ) {
		$packages = array();
		if ( static::PACKAGE_NAME === $name ) {
			if ( $version ) {
				$parser  = new VersionParser();
				$version = $parser->normalize( $version );
				if ( $this->isValidVersion( $version ) ) {
					$packages[] = $this->getPackage( $version );
				}
			} else {
				return $this->getPackages();
			}
		}
		return $packages;
	}

	/**
	 * Returns list of registered packages.
	 *
	 * @return array
	 */
	public function getPackages() {
		$packages = array();
		foreach ( $this->getVersions() as $version ) {
			if ( 2 === count( explode( '.', $version ) ) ) {
				$packages[] = $this->getPackage( $version . '-dev' );
			}
			$packages[] = $this->getPackage( $version );
		}
		$packages[] = $this->getPackage( 'trunk' );
	}

	/**
	 * Searches the repository for packages containing the query
	 *
	 * @param  string $query search query
	 * @param  int    $mode  a set of SEARCH_* constants to search on, implementations should do a best effort only
	 *
	 * @return array[] an array of array('name' => '...', 'description' => '...')
	 */
	public function search( $query, $mode = 0 ) {
		$matches = array();
		if ( preg_match( '/(^|[\s\-])(wordpress|core)($|[\s\-])/i', $query ) ) {
			$matches[] = array(
				'name'        => static::PACKAGE_NAME,
				'description' => ''
			);
		}
		return $matches;
	}

	/**
	 * Returns the number of core packages
	 *
	 * @return int 1
	 */
	public function count() {
		return 1;
	}

	/**
	 * @param string $version
	 *
	 * @return PackageInterface
	 */
	protected function getPackage( $version ) {
		$version = $this->wpVersion( $version );
		if ( '-dev' === substr( $version, - 4 ) ) {
			$version = substr( $version, 0, - 4 ) . '.*-dev';
		} elseif ( 'trunk' === $version ) {
			$version = 'dev-master';
		}
		$config  = $this->getPackageConfig( $version );
		$package = $this->getLoader()->load( $config );
		return $package;
	}

	/**
	 * @param string $version
	 *
	 * @return array
	 */
	protected function getPackageConfig( $version ) {
		$config = array(
			'name'              => static::PACKAGE_NAME,
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
		} elseif ( 'dev-master' === $version ) {
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

	/**
	 * @return ValidatingArrayLoader
	 */
	protected function getLoader() {
		if ( ! $this->loader ) {
			$this->loader = new ValidatingArrayLoader( new ArrayLoader(), false );
		}
		return $this->loader;
	}

	/**
	 * @param string $version
	 * @param bool   $normalize
	 *
	 * @return bool
	 */
	protected function isValidVersion( $version, $normalize = true ) {
		if ( $normalize ) {
			$version = $this->wpVersion( $version );
		}

		return (
			'trunk' === $version ||
			(
				'-dev' === substr( $version, - 4 ) &&
				in_array( substr( $version, 0, - 4 ), $this->getVersions() )
			) ||
			in_array( $version, $this->getVersions() )
		);
	}

	/**
	 * @param string $version
	 *
	 * @return string
	 */
	protected function wpVersion( $version ) {
		if ( in_array( $version, array( '9999999-dev', '*@dev', 'dev-master', 'trunk', ) ) ) {
			return 'trunk';
		}
		if ( '-dev' === substr( $version, - 4 ) ) {
			return str_replace( '.9999999', '', $version );
		}
		if ( preg_match( '/^(\d+\.\d+)\.(\d+)\.(\d+)$/', $version, $matches ) ) {
			$buildVersion = $matches[1];
			if ( (int) $matches[2] ) {
				$buildVersion .= '.' . $matches[2];
				if ( (int) $matches[3] ) {
					$buildVersion .= '.' . $matches[3];
				}
			}
			return $buildVersion;
		}
		return $version;
	}

	/**
	 * @return array
	 */
	protected function getVersions() {
		if ( $this->versions ) {
			return $this->versions;
		}
		$this->cache->gc(
			$this->ttl ? : $this->config->get( 'cache-files-ttl' ),
			$this->config->get( 'cache-files-maxsize' )
		);
		$this->versions = json_decode( $this->cache->read( 'versions.json' ), true );
		if ( empty( $this->versions ) || ! is_array( $this->versions ) ) {
			$fs   = new RemoteFilesystem( $this->io );
			$data = $fs->getContents(
				'core.svn.wordpress.org',
				'http://core.svn.wordpress.org/tags/',
				false
			);
			preg_match_all(
				'@<li><a[^>]+>(\d+(\.\d+)+)/</a></li>@',
				$data,
				$matches
			);
			$this->versions = array_values( array_filter(
				$matches[1],
				function ( $item ) {
					return (bool) version_compare( $item, '3.0', '>=' );
				}
			) );
			if ( ! is_array( $this->versions ) ) {
				$this->versions = array();
			}
			$this->cache->write( 'versions.json', json_encode( $this->versions ) );
		}
		return $this->versions;
	}

}
