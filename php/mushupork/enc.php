<?php
if (!getenv('SECRET')) die("Please set SECRET=XXXX\n");
$iv = substr(openssl_digest(getenv('SECRET'),'sha256',TRUE),0,16);
echo openssl_encrypt(file_get_contents("php://stdin"), "AES-256-CTR", getenv('SECRET'),0,$iv);

