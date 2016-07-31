<?php
/*
 * For Simpla CMS
 * @link		http://forum.simplacms.ru/topic/5064-davinci/
 * @link		http://forum.simplacms.ru/user/12518-davinci/
 * @link		http://www.SimplaDev.ru
 * @author		DaVinci
*/

if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest')
    exit();

session_start();
require_once('../api/Simpla.php');
$simpladev = new Simpla();

$i    = $simpladev->request->get('i',   'integer');  // информер
$key  = $simpladev->request->get('key', 'string');  // ключ сессии
$id   = $simpladev->request->get('id',  'integer'); // id товара

if(empty($key) && empty($id)) // если не указали ключ или id товара
    exit();
    
if(!empty($i) && !$simpladev->design->smarty->templateExists('products_session_'.$key.'_informer.tpl')) // проверяем существует ли шаблон
    exit();

$product = $simpladev->products->get_product($id);
if(empty($product)) // проверяем наличие товара
    exit();

$_SESSION[$key][$product->id] = $product->id; // записываем значение в сессию

$result = new stdClass();
$result->key   = $key; // ключ
$result->ids   = $_SESSION[$key]; // сессия
$result->count = count($_SESSION[$key]); // количество

if(!empty($i)){
    $simpladev->design->assign('session', $result);
    $result->informer = $simpladev->design->fetch('products_session_'.$key.'_informer.tpl'); // информер если указали
}

header("Content-type: application/json; charset=UTF-8");
header("Cache-Control: must-revalidate");
header("Pragma: no-cache");
header("Expires: -1");
print json_encode($result);