# Duplicate Checker

The duplicate checker is built on top of Symfony's validator and custom constraints. It can be used directly through the
`DuplicateService` or enabled automatically on save via the save manager.

## How it works

- The bundle builds a dedicated validator with its own mapping files.
- Each constraint violation returns the duplicated element as the invalid value.
- The `DuplicateService` returns an array of duplicates for a given element.

## Mapping files

Duplicate rules are defined in YAML mapping files. The bundle automatically loads any paths listed in
`opendxp_element_manager.duplication.mapping.paths`

## Fields constraint

The `Fields` constraint checks for duplicates based on one or more fields. It supports trimming values before
comparison.

Example mapping file (`config/duplication/product.yaml`):

```yaml
OpenDxp\Model\DataObject\Product:
    constraints:
        -   Instride\Bundle\OpenDxpElementManagerBundle\DuplicateChecker\Constraints\Fields:
                fields: [ 'sku' ]
                trim: true
                message: 'Duplicate product SKU'
                groups: [ product ]
```

## Using the duplicate service

You can use the service directly if you want to run a check outside of save events:

```php
/** @var Instride\Bundle\OpenDxpElementManagerBundle\DuplicateChecker\DuplicateServiceInterface $service */
$duplicates = $service->findDuplicates($object, ['product']);
```

## Using the save handler

Enable duplicate checks on save per class:

```yaml
opendxp_element_manager:
    classes:
        Product:
            duplicates:
                enabled_on_save: true
```
