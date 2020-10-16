<?php
require_once __DIR__.'/Base62x.class.php';
class Aes {

    private $aes_key; //加密key
    private $aes_iv;  //加密iv
    private $method;  //加密方式

    public function __construct($key="",$iv="",$method="AES-128-CBC") {

        if ( !isset($key) || empty($key) ) {
            $key="1234567890123456";
        }

        if ( !isset($iv) || empty($iv) ) {
            $iv="1234567890123456";
        }

        $this->aes_key = $key;
        $this->aes_iv  = $iv;
        $this->method  = $method;
    }

    //加密数据 (只能加密字符)
    public function encrypt($data="") {
        $data = trim($data);
        $data = @openssl_encrypt($data, $this->method,$this->aes_key, true, $this->aes_iv);
        $data = @Base62x::encode($data);
        return $data;
    }

    //解密数据 (只能解密字符串)
    public function decrypt($data="") {
        $data = trim($data);
        $data = @Base62x::decode($data);
        $data = @openssl_decrypt($data,$this->method,$this->aes_key, true, $this->aes_iv);
        return $data;
    }
}
