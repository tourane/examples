# Tourane examples

## Prerequisites

* PHP 5.6 or 7.0
* Composer

Check the version of PHP & Composer:

```shell
$ php --version
PHP 5.6.40-7+ubuntu18.04.1+deb.sury.org+1 (cli) 
Copyright (c) 1997-2016 The PHP Group
Zend Engine v2.6.0, Copyright (c) 1998-2016 Zend Technologies
    with Zend OPcache v7.0.6-dev, Copyright (c) 1999-2016, by Zend Technologies

$ composer --version
Composer version 1.8.5 2019-04-09 17:46:47
```

## Installation

Checkout the source code of example from github:

```shell
git clone https://github.com/tourane/examples.git
```

```shell
cd examples
```

Install dependencies:

```shell
composer install
```

## Examples

### Logging

Your source code before tracing methods:

```php
class MongoClient {
  public function find($query, $opts) {
    $items = array( 1, 3, 5, 7, 11);
    return array(
      "items" => $items,
      "total" => count($items)
    );
  }
  public function close($opts) {
    return true;
  }
}

$db = new MongoClient();

$result = $db->find(array("type" => "prime", "max" => 15), null);
printf("find: %s\n", json_encode($result));

try {
  $db->close(array());
} catch (Exception $e) {
  $tracer->getLogger()->error("Error: " . $e->getMessage());
}
```

After updating:

```php
class MongoClient {
  public function find($query, $opts) {
    $items = array( 1, 3, 5, 7, 11);
    return array(
      "items" => $items,
      "total" => count($items)
    );
  }
  public function close($opts) {
    return true;
  }
}

$db = new MongoClient();

// ------------------------- Define the tracer object & Wrap the original object

require_once __DIR__ . '/vendor/autoload.php';

use Tourane\ProxyKit\Adapter;

// Define the tracer object
$tracer = new Adapter(array(
  "logging" => array(
    "file" => array(
      "dir" => dirname(__FILE__ ) . '/log',
      "filename" => "example.log"
    ),
    "extra" => array(
      "ProcessId" => true
    )
  )
));

// Wrap the original object
$db = $tracer->wrap($db, array(
  "loggingMethods" => array(
    "find" => array(),
    "close" => array(
      "logArguments" => true,
      "logReturnValue" => true
    )
  )
));

// -----------------------------------------------------------------------------

$result = $db->find(array("type" => "prime", "max" => 15), null);
printf("find: %s\n", json_encode($result));

try {
  $db->close(array());
} catch (Exception $e) {
  $tracer->getLogger()->error("Error: " . $e->getMessage());
}
```

Execute the example:

```shell
php logging.php
```

