<?php
set_include_path(implode(PATH_SEPARATOR, array(
realpath(dirname(__FILE__) . '/../library'),
get_include_path(),
)));

require_once 'Zend/Loader/Autoloader.php';
Zend_Loader_Autoloader::getInstance()->setFallbackAutoloader(true)->registerNamespace('Cfe_');


$aes = new Cfe_Token_Aes();


//$image_folder = '/home/appsteur/img/web/';
//$image_folder = '/Users/hamza/';
$param =  $aes->decrypt($_GET['param']);
//var_dump($param);die;
if ($param) {
    if (file_exists($param) && is_readable($param)) {
        $ext = strtolower(substr($param, -3));
        switch ($ext) {
            case 'jpg':
                $mime = 'image/jpeg';
                break;
            case 'gif':
                $mime = 'image/gif';
                break;
            case 'png':
                $mime = 'image/png';
                break;
            default:
                $mime = false;
        }
         
        if ($mime) {
            //header("Cache-Control: private, max-age=10800, pre-check=10800");
            header("Cache-Control: max-age=10800");
            header("Expires: " . date(DATE_RFC1123,strtotime("+2 day")));
            header('Content-type: '.$mime);
            header('Content-length: '.filesize($param));
            $file = @ file_get_contents($param);
            if ($file) {
                print $file;
                exit;
            }
        }
    }
}
