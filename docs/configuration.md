# Configuration

The bundle is configured under the `opendxp_element_manager` key. The configuration has two main sections:

- `duplication`: configure duplicate mapping paths.
- `classes`: configure save manager behavior per data object class.

## Minimal example

```yaml
opendxp_element_manager:
    duplication:
        mapping:
            paths:
                - '%kernel.project_dir%/config/duplication'

    classes:
        Product:
            naming_scheme:
                enabled: true
                options:
                    scheme: "['products', object.getId()]"
            unique_key:
                enabled: true
            duplicates:
                enabled_on_save: true
            validations:
                enabled_on_save: true
                options:
                    group: product
```

## Configuration reference

### duplication

- `mapping.paths`: list of directories or YAML files containing duplicate mapping configuration.

The bundle also auto-discovers mapping files from bundles:

- `config/duplication.yaml`
- `config/duplication/*.yaml`

### classes

Each entry key is a data object class name (for example `Product`).

- `save_manager_class` (default: `Instride\Bundle\OpenDxpElementManagerBundle\SaveManager\ObjectSaveManager`)
- `naming_scheme.enabled` (default: `false`)
- `naming_scheme.service` (default:
  `Instride\Bundle\OpenDxpElementManagerBundle\SaveManager\NamingScheme\ExpressionNamingScheme`)
- `naming_scheme.options`: options passed to the naming scheme implementation.
- `unique_key.enabled` (default: `false`)
- `duplicates.enabled_on_save` (default: `false`)
- `duplicates.options`: options passed to the duplicate handler.
- `validations.enabled_on_save` (default: `true`)
- `validations.options.group` (default: `opendxp_element_manager`)
- `save_handlers`: list of additional save handler service IDs.

## Notes on options

Options are passed to save handlers as an array with the keys `naming_scheme`, `duplicates`, and `validations`. For
example, `naming_scheme.options` becomes `$options['naming_scheme']` in a handler.

