# Save Manager and Lifecycle

The bundle registers an `ObjectSaveManager` per configured class and hooks into OpenDXP data object events.

## Event integration

The `ObjectEventListener` listens to:

- `opendxp.dataobject.preAdd`
- `opendxp.dataobject.postAdd`
- `opendxp.dataobject.preUpdate`
- `opendxp.dataobject.postUpdate`
- `opendxp.dataobject.preDelete`
- `opendxp.dataobject.postDelete`

If a save manager is configured for the current object class, the corresponding method is called.

## Save handler pipeline

A save manager holds a list of save handlers. For each event it:

1. Calls the event-specific method if it exists (`preAdd`, `postUpdate`, etc.).
2. Calls `preSave` for `preAdd` and `preUpdate`.
3. Calls `postSave` for `postAdd` and `postUpdate`.

After that, handlers implementing `PostObjectSaveHandlerInterface` receive the same event in a second pass:

- `postPreSave` for `preAdd` and `preUpdate`
- `postPostSave` for `postAdd` and `postUpdate`
- The event-specific post method if it exists (for example `postPreUpdate`).

## Default save handlers

When enabled via configuration, these handlers are added automatically in this order:

- `NamingSchemeSaveHandler`: applies the naming scheme and sets key and parent path.
- `UniqueKeySaveHandler`: recalculates the unique object key.
- `ValidationSaveHandler`: validates the object and throws on errors.
- `DuplicationSaveHandler`: checks for duplicates and throws on matches.

Custom handlers from `save_handlers` are added after the built-in ones.

Next: [Custom Save Handlers](custom-save-handlers.md)
