<?php

class Cfe_Token_Aes {
	private $iv;
	private $key;


	function __construct() {

		$this->iv = '8H4hNSxeju8972EL';// ancien jusqu'� 2.5 (Smart&Soft) => '62uucu58p59u7k5h';#Same as in JAVA
		$this->key = 'eVcv7v5C3N5X9W5e';// ancien jusqu'� 2.5 (Smart&Soft) => 'gc8h5r2w83x74j7e'; #Same as in JAVA
	}


	public function encrypt($str) {

		//$key = $this->hex2bin($key);
		$iv = $this->iv;

		$td = mcrypt_module_open('rijndael-128', '', 'cbc', $iv);

		mcrypt_generic_init($td, $this->key, $iv);
		$encrypted = mcrypt_generic($td, $str);

		mcrypt_generic_deinit($td);
		mcrypt_module_close($td);

		return bin2hex($encrypted);
	}

	public function decrypt($code) {
		//$key = $this->hex2bin($key);
		$code = $this->hex2bin($code);
		$iv = $this->iv;

		$td = mcrypt_module_open('rijndael-128', '', 'cbc', $iv);

		mcrypt_generic_init($td, $this->key, $iv);
		$decrypted = mdecrypt_generic($td, $code);

		mcrypt_generic_deinit($td);
		mcrypt_module_close($td);

		return utf8_encode(trim($decrypted));
	}

	protected function hex2bin($hexdata) {
		$bindata = '';

		for ($i = 0; $i < strlen($hexdata); $i += 2) {
			$bindata .= chr(hexdec(substr($hexdata, $i, 2)));
		}

		return $bindata;
	}
}