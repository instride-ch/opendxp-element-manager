# Validation on Save

When enabled, the `ValidationSaveHandler` runs Symfony validation before saving an object.

## Enable validation

```yaml
opendxp_element_manager:
    classes:
        Product:
            validations:
                enabled_on_save: true
                options:
                    group: product
```

## Validation group

The validation group defaults to `opendxp_element_manager`. Set a different group to align with your project validation rules.

## Behavior

- Validation runs during `preAdd` and `preUpdate` via `preSave`.
- If validation fails, a `ValidationException` is thrown with the collected messages.

