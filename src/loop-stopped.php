<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$pool = \Amp\Mysql\pool(
    Amp\Mysql\ConnectionConfig::fromString(
        'host=mysql;user=root;pass=root;db=amp_test'
    )
);
$promise = Amp\call(function () use ($pool): \Generator {

    yield $pool->execute(<<<'NOWDOC'
CREATE TABLE IF NOT EXISTS `test_amp` (
  `uniq` int(11) DEFAULT NULL,
  `created` DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `test_amp_uniq_uindex` (`uniq`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
NOWDOC
    );

    yield $pool->execute("TRUNCATE test_amp;");

    yield $pool->execute("INSERT INTO test_amp (uniq) VALUES (0);");

    try {
        yield $pool->execute("INSERT INTO test_amp (uniq) VALUES (0);");;
    } catch (\Throwable $e) {
        echo $e->getMessage() . PHP_EOL . PHP_EOL;
    }

    try {
        yield $pool->execute("INSERT INTO test_amp (uniq) VALUES (:value);", ['value' => 2]);
    } catch (\Throwable $exception) {
        echo $exception->getMessage() . PHP_EOL . PHP_EOL;
    }
});

Amp\Promise\wait($promise);