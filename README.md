
php版Aes加密字符串
====

php版的aes加密类，加密后没有特殊符号，只有数字和字母

### 如何安装  
composer require zhuyanxun/aes  

### 如何使用  

```
$aes_key = "J0^P^)0-ZriSt93g";
$aes_iv  = "B8jNhk5J49(@mM++";
$method  = "AES-128-CBC";
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

```
