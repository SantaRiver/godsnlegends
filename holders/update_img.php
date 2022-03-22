<?php

ini_set('memory_limit', '1024M');
ini_set('set_time_limit', 0);
$mysqli = new mysqli("host", "login", "pass", "db");
$query = "SELECT * FROM `cards_point`";
$result = [];
if ($sqlResult = $mysqli->query($query)) {
    while ($obj = $sqlResult->fetch_object()) {
        $result[] = $obj;
    }
}
$cardsPointBD = $result;
$cardsPoint = [];
foreach ($cardsPointBD as $item) {
    $cardsPoint[$item->templateID] = $item->point;
}

function get($url): string
{
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    $resp = curl_exec($curl);
    curl_close($curl);
    return $resp;
}

$params = [
    'collection_name' => 'godsnlegends',
    'limit' => 200,
    'page' => 1,
    'ids' => implode(',', array_keys($cardsPoint)),
    'order' => 'desc',
    'sort' => 'asset_id',
];
$assetsUrl = 'https://wax.api.atomicassets.io/atomicassets/v1/templates?'.http_build_query($params);
$assetsResponse = json_decode(get($assetsUrl));
if ($assetsResponse->success) {
    foreach ($assetsResponse->data as $asset) {
        $img = $asset->immutable_data->{'Card Front'} ?? $asset->immutable_data->front;
        if (!file_exists("/resources/cards/$img.png")) {
            $image = file_get_contents("https://gateway.pinata.cloud/ipfs/$img");
            file_put_contents("/resources/cards/$img.png", $image);
        }
    }
}

header('location: https://godsnlegends.ru/add_template');