# Custom Save Handlers

You can extend the save pipeline by implementing `ObjectSaveHandlerInterface` and
registering your handler as a service.

## Basic handler

```php
namespace App\SaveHandler;

use Instride\Bundle\OpenDxpElementManagerBundle\SaveManager\AbstractObjectSaveHandler;
use OpenDxp\Model\DataObject\Concrete;

final class MyHandler extends AbstractObjectSaveHandler
{
    public function preSave(Concrete $object, array $options): void
    {
        // Custom logic here
    }
}
```

## Register the service

```yaml
services:
    App\SaveHandler\MyHandler: ~
```

## Attach the handler to a class

```yaml
opendxp_element_manager:
    classes:
        Product:
            save_handlers:
                - App\SaveHandler\MyHandler
```
