<?php
require("../vendor/autoload.php");
$openapi = \OpenApi\scan(__DIR__.'/');
header('Content-Type: application/x-yaml');
echo $openapi->toYaml();