# Ride: Template Library (Frame)

Frame engine for the template library of the PHP Ride framework.

Read the documentation for [Frame](http://github.com/php-frame/frame). 

## Code Sample

Check this code sample to see how to initialize this library:

```php
use frame\library\DefaultTemplateContext;
use frame\library\TemplateEngine;

use ride\library\template\engine\FrameEngine;
use ride\library\template\engine\FrameResourceHandler;
use ride\library\system\System;

function createFrameTemplateEngine(System $system) {
    $resourceHandler = new FrameResourceHandler($system->getFileBrowser(), 'view/frame');
    $context = new DefaultTemplateContext($resourceHandler);
    
    $cache = new DirectoryTemplateCache('/path/to/cache');
    
    $engine = new TemplateEngine($context, $cache);
    $engine = new FrameEngine($engine);
    
    return $engine;
}
```

### Implementations

You can check the related implementations of this library:
- [ride/app-template-frame](https://github.com/all-ride/ride-app-template-frame)
- [ride/lib-template](https://github.com/all-ride/ride-lib-template)
- [ride/web-template-frame](https://github.com/all-ride/ride-web-template-smarty)
- [ride/web-template-frame-minifier](https://github.com/all-ride/ride-web-template-smarty-minifier)

## Installation

You can use [Composer](http://getcomposer.org) to install this library.

```
composer require ride/lib-template-frame
```
