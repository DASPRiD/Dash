<?php
require __DIR__ . '/../vendor/autoload.php';

function runParser(\Zend\Cache\Storage\StorageInterface $cache = null) {
    $parser = new \Dash\Router\Http\Parser\Segment('/', '/foo[/:bar]', ['bar' => 'baz']);
    $parser->setCache($cache);

    $parser->parse('/foo/baz', 0);
    $parser->compile(['bar' => 'baz'], []);
}

// Without cache
$startTime = microtime(true);

for ($i = 0; $i < 10000; $i++) {
    runParser();
}

$timeTaken = microtime(true) - $startTime;

echo "Without cache, 10,000 cycles:\n";
echo $timeTaken . "\n";

// With cache
$cache   = \Zend\Cache\StorageFactory::factory(array(
    'adapter' => [
        'name' => 'memory',
    ],
    'plugins' => [
        'Serializer',
    ]
));

$startTime = microtime(true);

for ($i = 0; $i < 10000; $i++) {
    runParser($cache);
}

$timeTaken = microtime(true) - $startTime;

echo "With cache, 10,000 cycles:\n";
echo $timeTaken . "\n";