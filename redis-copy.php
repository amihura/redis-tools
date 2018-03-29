<?php

require "predis_1.1.0.phar";

$sourceRedis = new Predis\Client("tcp://localhost:6376");
$targetRedis = new Predis\Client("tcp://localhost:6379");

$keys = $sourceRedis->keys('*');
foreach ($keys as $key) {
    $ttl = $sourceRedis->ttl($key);
    $type = $sourceRedis->type($key);
    echo "------------------\r\n";
    echo "Reading key from Source Redis: $key\r\n";
    if ($type == 'hash') {
        $targetRedis->hmset($key, $sourceRedis->hgetall($key));
    } elseif ($type == 'string') {
        $targetRedis->set($key, $sourceRedis->get($key));
    } elseif ($type == 'set') {
        $targetRedis->sadd($key, $sourceRedis->smembers($key));
    }
    if ($ttl > 0) {
        $targetRedis->expire($key, $ttl);
    }
    echo "Successfully copied to new Redis\r\n";
}
