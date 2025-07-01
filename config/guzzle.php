<?php

return [
    'verify' => env('GUZZLE_SSL_VERIFY', true),
    'timeout' => env('GUZZLE_TIMEOUT', 30),
    'connect_timeout' => env('GUZZLE_CONNECT_TIMEOUT', 30),
]; 