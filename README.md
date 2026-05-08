![Pimcore Element Manager](docs/images/github_banner.png)

### Requirements

* OpenDxp `^1.3`

### Installation

- Install with composer
  ```
  composer require instride/opendxp-element-manager:^3.0
  ```

- Add to `config/bundles.php`
  ```php
    return [
        // ...
        Instride\Bundle\OpenDxpElementManagerBundle\OpenDxpElementManagerBundle::class => ['all' => true],
    ];
  ```

### Documentation

- [Documentation index](docs/index.md)
