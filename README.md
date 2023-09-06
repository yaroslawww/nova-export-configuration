# Laravel nova export configuration

![Packagist License](https://img.shields.io/packagist/l/yaroslawww/nova-export-configuration?color=%234dc71f)
[![Packagist Version](https://img.shields.io/packagist/v/yaroslawww/nova-export-configuration)](https://packagist.org/packages/yaroslawww/nova-export-configuration)
[![Total Downloads](https://img.shields.io/packagist/dt/yaroslawww/nova-export-configuration)](https://packagist.org/packages/yaroslawww/nova-export-configuration)
[![Build Status](https://scrutinizer-ci.com/g/yaroslawww/nova-export-configuration/badges/build.png?b=main)](https://scrutinizer-ci.com/g/yaroslawww/nova-export-configuration/build-status/main)
[![Code Coverage](https://scrutinizer-ci.com/g/yaroslawww/nova-export-configuration/badges/coverage.png?b=main)](https://scrutinizer-ci.com/g/yaroslawww/nova-export-configuration/?branch=main)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/yaroslawww/nova-export-configuration/badges/quality-score.png?b=main)](https://scrutinizer-ci.com/g/yaroslawww/nova-export-configuration/?branch=main)

Functionality to create managed export configuration in laravel nova.

| Nova | Package |
|------|---------|
| V4   | V1      |

## Installation

You can install the package via composer:

```bash
composer require yaroslawww/nova-export-configuration

# optional publish configs
php artisan vendor:publish --provider="NovaExportConfiguration\ServiceProvider" --tag="config"
```

Update filesystem configuration if you will used default disks.

```php
// config/filesystems.php
'exports_configured'                 => [
    'driver' => 'local',
    'root'   => storage_path('app/exports_configured'),
],
```

## Usage

Need configure model and repos.

## Credits

- [![Think Studio](https://yaroslawww.github.io/images/sponsors/packages/logo-think-studio.png)](https://think.studio/)
