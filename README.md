![OpenDXP Element Manager](docs/images/github_banner.png)

### Requirements

- [OpenDXP](https://github.com/open-dxp/opendxp) `^1.3`

### Installation

- Install with composer
  ```
  composer require instride/opendxp-element-manager:^1.0
  ```

- Add to `config/bundles.php`
  ```php
  return [
      // ...
      Instride\Bundle\OpenDxpElementManagerBundle\OpenDxpElementManagerBundle::class => ['all' => true],
  ];
  ```

### Documentation

- [Getting started](docs/index.md)
- [Installation](docs/installation.md)
- [Configuration](docs/configuration.md)
