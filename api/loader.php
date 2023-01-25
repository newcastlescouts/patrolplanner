<?php

require_once(__DIR__ . '/../vendor/autoload.php');
require_once(__DIR__ . '/config.php');

$headers = array_change_key_case(getallheaders(), CASE_LOWER);
// check if header X-NCLScouts-Session is set, and if so, set the session ID to that
if ($headers['authorization'] ?? false) {
    // remove the "X-NCLScouts-Session " part
    session_id(substr($headers['authorization'], 20));
}

session_start();