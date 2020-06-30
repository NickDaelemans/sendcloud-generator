<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/env-load.php';

use Picqer\Carriers\SendCloud\Connection;
use Picqer\Carriers\SendCloud\SendCloud;

$connection = new Connection(env('sendcloud_apikey'), env('sendcloud_apisecret'));
$sendCloud = new SendCloud($connection);

$senderAddress = $sendCloud->sender_addresses()->find(env('to_address'));

$parcel = $sendCloud->parcels();

$parcel->name = $senderAddress->__get('contact_name');
$parcel->company_name = $senderAddress->__get('company_name');
$parcel->address = $senderAddress->__get('street') . ' ' . $senderAddress->__get('house_number');
$parcel->address_2 = $senderAddress->__get('postal_box');
$parcel->city = $senderAddress->__get('city');
$parcel->country = $senderAddress->__get('country');
$parcel->postal_code = $senderAddress->__get('postal_code');
$parcel->sender_address = env('sender_addresss');
$parcel->requestShipment = TRUE;
$parcel->shipment = (int) env('shipping_method');

$parcel = $parcel->save();

$parcel_id = (string) $parcel->id;
$label = $sendCloud->labels()->find($parcel_id);

$pdf_content = $label->labelPrinterContent();
$file = fopen('retour.pdf', 'w');
fwrite($file, $pdf_content);
fclose($file);

header("Content-type:application/pdf");
// It will be called downloaded.pdf
header("Content-Disposition:attachment;filename=retour.pdf");
// The PDF source is in original.pdf
readfile("retour.pdf");

echo 'The label was generated. Do not refresh this page, as it will regenerate another label. Just close this tab.';
