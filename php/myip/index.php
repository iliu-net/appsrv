<?php
Header("Content-type: text/plain");

foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_CLIENT_IP',
          'REMOTE_ADDR'] as $key) {
  if (isset($_SERVER[$key])) {
    echo $_SERVER[$key].PHP_EOL.$key;
    break;
  }
}

