# Ride: Template Library (Huqis)

Huqis engine for the template library of the PHP Ride framework.

Read the documentation for [Huqis](http://github.com/huqis/huqis). 

## Code Sample

Check this code sample to see how to initialize this library:

```php
use huqis\DefaultTemplateContext;
use huqis\TemplateEngine;

use ride\library\template\engine\HuqisEngine;
use ride\library\template\engine\HuqisResourceHandler;
use ride\library\system\System;

function createFrameTemplateEngine(System $system) {
    $resourceHandler = new HuqisResourceHandler($system->getFileBrowser(), 'view/frame');
    $context = new DefaultTemplateContext($resourceHandler);
    
    $cache = new DirectoryTemplateCache('/path/to/cache');
    
    $engine = new TemplateEngine($context, $cache);
    $engine = new HuqisEngine($engine);
    
    return $engine;
}
```

### Implementations

You can check the related implementations of this library:
- [ride/app-template-huqis](https://github.com/all-ride/ride-app-template-frame)
- [ride/lib-template](https://github.com/all-ride/ride-lib-template)
- [ride/web-template-huqis](https://github.com/all-ride/ride-web-template-smarty)
- [ride/web-template-huqis-minifier](https://github.com/all-ride/ride-web-template-smarty-minifier)

## Installation

You can use [Composer](http://getcomposer.org) to install this library.

```
composer require ride/lib-template-huqis
```
