
# PHP Extract Meta URL

**PHP Extract Meta URL** is a very simple library allowing to obtain various information of a Web site from its URL:

* title
* description
* keywords
* image
* favicon
* RSS feeds


## Requirements

* PHP 7.4+
* PHP cURL extension
* PHP json extension


## Installation

With Composer, run this command:

    composer require shevabam/php-extract-meta-url


## Usage

Simple example:

```php
<?php
require 'vendor/autoload.php';

$url = 'https://www.gadgets360.com/science/news/blue-origin-jeff-bezos-new-shepard-ns-23-nasa-3339832';

$x = new \PhpExtractMetaUrl\PhpExtractMetaUrl($url);
$x->setFormat('array'); // default: json
$result = $x->extract();

echo '<pre>';
print_r($result);
```

Returns:

```
Array
(
    [website] => Array
        (
            [url] => https://www.gadgets360.com/science/news/blue-origin-jeff-bezos-new-shepard-ns-23-nasa-3339832
            [scheme] => https
            [host] => www.gadgets360.com
        )

    [title] => Blue Origin Rocket Fails Shortly After Lift-Off: All Details | Technology News
    [description] => A Blue Origin rocket has failed mid-flight shortly after its lift-off a day ago. This has caused the rocket to abort its cargo capsule to safety before crashing into the Texas desert. The rocket aimed to send NASA-funded experiments and other payloads to the edge of the space. The booster crashed within the designated hazard area.
    [keywords] => Array
        (
            [0] => blue origin jeff bezos new shepard ns 23 nasa blue origin
            [1] => nasa
            [2] => jeff bezos
            [3] => new shepard
        )

    [image] => https://i.gadgets360cdn.com/large/blue_moon_bezos_reuters_1624520858507.jpg
    [favicon] => https://www.gadgets360.com/favicon.ico
    [rss] => Array
        (
            [0] => https://feeds.feedburner.com/gadgets360-latest
        )

)
```