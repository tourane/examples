<?php

// -------------------------------------------------------- Your class & objects

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
  "caching" => array(
    "enabled" => true,
    "dir" => dirname(__FILE__ ) . '/tmp/generated/classes'
  ),
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

// ------------------------------------ Keep the following source code unchanged

$result = $db->find(array("type" => "prime", "max" => 15), null);
printf("find: %s\n", json_encode($result));

try {
  $db->close(array());
} catch (Exception $e) {
  $tracer->getLogger()->error("Error: " . $e->getMessage());
}

?>
