<?php
session_start();
require_once('../api/Simpla.php');
include __DIR__ . '/../core/inc/functions.php';
$simpla = new Simpla();

$variant_id = $simpla->request->post('variant', 'integer');
$amount = $simpla->request->post('amount', 'integer');

$order = new stdClass;
$order->name = sanitize($simpla->request->post('name', 'string'));
$order->phone = sanitize($simpla->request->post('phone', 'string'));
$order->ip = ip();

// добавляем заказ
$order_id = $simpla->orders->add_order($order);

// добавляем товар в заказ
$simpla->orders->add_purchase([
    'order_id' => $order_id,
    'variant_id' => (int)$variant_id,
    'amount' => (int)$amount
]);

// отправляем письмо администратору
$simpla->notify->email_order_admin($order_id);