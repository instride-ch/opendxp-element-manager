# Installation

## Requirements

- OpenDXP `^1.3`
- PHP and Symfony versions supported by your OpenDXP installation

## Install with Composer

```bash
composer require instride/opendxp-element-manager
```

## Enable the bundle

If your OpenDXP setup does not auto-register bundles, add the bundle to `config/bundles.php`:

```php
return [
    // ...
    Instride\Bundle\OpenDxpElementManagerBundle\OpenDxpElementManagerBundle::class => ['all' => true],
];
```

## Optional dependencies

- `opendxp/object-merger`: useful if you plan to merge duplicates directly.

## Next step

Continue with the configuration guide to enable features per class.

- [Configuration](configuration.md)
