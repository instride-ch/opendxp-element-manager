# Naming Schemes

The bundle ships with `ExpressionNamingScheme`, which uses Symfony's ExpressionLanguage to calculate an object key and optional path segments.

## ExpressionNamingScheme options

The following options are available (with defaults):

- `parent_path`: `/`
- `archive_path`: `/_temp`
- `scheme`: `''`
- `auto_prefix_path`: `true`
- `skip_path_for_variant`: `false`
- `initial_key_mapping`: `null`

All options are exposed to the expression. The expression receives an `object` and `path` variable in addition to the option keys.

## How expressions are evaluated

- If the expression returns a string, it becomes the object key.
- If the expression returns an array, the last element is treated as the key and the preceding elements are used as path segments.
- If `auto_prefix_path` is `true`, the path segments are appended to `parent_path`.
- If `auto_prefix_path` is `false`, the path segments are treated as the full path.

If the resulting key is empty, a unique key is generated.

## Variant behavior

If `skip_path_for_variant` is `true`, the parent path is not changed for object variants.

## Mapping the initial key

When `initial_key_mapping` is set and the object is created via the admin UI, the initial key is copied into the mapped field before any rename happens.

## Example

```yaml
opendxp_element_manager:
    classes:
        Product:
            naming_scheme:
                enabled: true
                options:
                    parent_path: '/products'
                    archive_path: '/archive'
                    scheme: "['brands', object.getBrand(), object.getSku()]"
                    auto_prefix_path: true
                    skip_path_for_variant: true
                    initial_key_mapping: 'name'
```
