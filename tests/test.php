<?php
require_once __DIR__.'/../src/Aes.class.php';

$aes_key = "J0^P^)0-ZriSt93g";  //16位
$aes_iv  = "B8jNhk5J49(@mM++";  //16位
$method  = "AES-128-CBC";       //加密方式
$aes = new Aes($aes_key,$aes_iv,$method);

//默认字符串
$strings = "hello world!";

//加密
$result1 = $aes->encrypt($strings);

//解密
$result2 = $aes->decrypt($result1);

print_r(array(
    'default' => $strings,
    'encrypt' => $result1,
    'decrypt' => $result2,
));

