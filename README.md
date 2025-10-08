# Hryvinskyi_SeoRobotsFrontend

Frontend module for managing robots meta tags in Magento 2.

## Features

- Apply custom robots meta tags based on URL patterns and actions
- Support for HTTPS-specific robots settings
- NOINDEX/NOFOLLOW for 404 pages
- Custom robots for paginated pages
- **Extension point for custom robots providers**

## Extension Point

This module provides an extension point that allows other modules to provide custom robots meta tags based on specific conditions.

### How to Extend

Implement the `RobotsProviderInterface` in your module:

```php
<?php

namespace YourVendor\YourModule\Model;

use Hryvinskyi\SeoRobotsFrontend\Model\RobotsProviderInterface;
use Magento\Framework\App\HttpRequestInterface;

class CustomRobotsProvider implements RobotsProviderInterface
{
    /**
     * @inheritDoc
     */
    public function getRobots(HttpRequestInterface $request): ?string
    {
        // Your custom logic here
        // Return robots string like "NOINDEX,NOFOLLOW" or null if not applicable

        if ($someCondition) {
            return 'NOINDEX,NOFOLLOW';
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function getSortOrder(): int
    {
        return 100; // Lower numbers execute first
    }
}
```

Then register your provider in `di.xml`:

```xml
<type name="Hryvinskyi\SeoRobotsFrontend\Model\ApplyRobots">
    <arguments>
        <argument name="robotsProviders" xsi:type="array">
            <item name="your_provider" xsi:type="object">YourVendor\YourModule\Model\CustomRobotsProvider</item>
        </argument>
    </arguments>
</type>
```

### Execution Order

Providers are executed in order of their `getSortOrder()` value (ascending). If a provider returns a non-null value, it will be applied to the page. Later providers can override earlier ones.

## Configuration

Configuration is available in:
**Stores > Configuration > Hryvinskyi SEO > Robots**

## Dependencies

- Magento 2.4+
- Hryvinskyi_SeoRobotsApi

## Author

Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>

## License

Proprietary
