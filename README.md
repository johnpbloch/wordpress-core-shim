# WordPress Core Shim

WordPress Core Shim is a meta package for Composer to create virtual packages for WordPress Core. It allows you to require core without needing to set up your own satis repository (which can get complicated) or need to rely on somebody else's (which can be out of date or unavailable). WordPress Core Shim uses the main WordPress repository and standard distribution packages to download WordPress without relying on a middle-man.

### Installation

Because of the nature of Composer Plugins, it is a bit tricky to get these packages working correctly. The easiest way to install the package is to do so globally:

```
composer global require johnpbloch/wordpress-core-shim:~0.1
```

This will ensure that it is loaded when you need to use it for any local repo. Please note, you will need to run that command as the user who will be executing the `composer install` or `composer update` commands. If you run that as root, it will only let root install WordPress core with this plugin.

Once you have it installed, simply require the `wordpress-core` package:

```json
{
  "require": {
    "wordpress-core": "~3.6"
  }
}
```

You can also declare it as a dependency directly in your main package, but be aware that you will need to temporarily remove the `wordpress-core` package from your requirements, run `composer update`, and then add the `wordpress-core` requirement back and run `composer update` again.

### Versions

WordPress Core Shim provides all versions from 3.0 on. That includes branches ("N.N.*-dev" format) and trunk (dev-master).

### License

GPL v2+