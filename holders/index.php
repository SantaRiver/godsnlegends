<?php


$start = microtime(true);
$SITE_PATH = '/var/www/eosanione/data/www/194.58.121.94/godsnlegends/holders';
ini_set('memory_limit', '1024M');
ini_set('set_time_limit', 0);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function selectFrom($table, $sort = 'ID', $order = 'ASC', $fields = [])
{
    $mysqli = new mysqli("host", "login", "pass", "db");
    $query = "SELECT * FROM `godsnlegends`.`".$table."` ORDER BY `".$table."`.`".$sort."` ".$order;

    $result = [];
    if ($sqlResult = $mysqli->query($query)) {
        while ($obj = $sqlResult->fetch_object()) {
            $result[] = $obj;
        }
    }
    $sqlResult->close();
    unset($obj);
    unset($query);
    return $result;
}

function insertTo($table, $fields)
{
    $mysqli = new mysqli("host", "login", "pass", "db");
    $keyString = "(";
    $valueString = "(";
    foreach ($fields as $key => $value) {
        if (is_null($value)) {
            $valueString .= "NULL, ";
        } else {
            $valueString .= "'".$value."', ";
        }
        $keyString .= "`".$key."`, ";
    }
    $keyString = substr($keyString, 0, -2);
    $valueString = substr($valueString, 0, -2);
    $keyString .= ")";
    $valueString .= ")";
    $query = "INSERT INTO `godsnlegends`.`".$table."` ".$keyString." VALUES ".$valueString.";";
    try {
        $mysqli->query($query);
    } catch (\mysql_xdevapi\Exception $exception) {
        echo '<pre>';
        print_r($exception);
        echo '</pre>';
    }
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

function customSort($a, $b)
{
    if ($a['point'] == $b['point']) {
        return $a['mint_sum'] - $b['mint_sum'];
    }
    return $b['point'] - $a['point'];
}

function pointSort($a, $b)
{
    if ($a['point'] == $b['point']) {
        if ($a['rarity_rank'] == $b['rarity_rank']) {
            return $a['mint'] - $b['mint'];
        }
        return $b['rarity_rank'] - $a['rarity_rank'];
    }
    return $b['point'] - $a['point'];
}

$cardsPointBD = selectFrom('cards_point');
$rarityRateBD = selectFrom('cards_rarity');
$rarityRate = [];
foreach ($rarityRateBD as $item) {
    $rarityRate[$item->rarity] = $item->rate;
}
$cardsPoint = [];
foreach ($cardsPointBD as $item) {
    $cardsPoint[$item->templateID] = $item->point;
}
$usersInventory = [];
$availableTemplateIds = array_keys($cardsPoint);

$params = [
    'collection_name' => 'godsnlegends',
    'limit' => 1000,
    'page' => 1,
    'burned' => false,
    'template_whitelist' => implode(',', array_keys($cardsPoint)),
    'order' => 'desc',
    'sort' => 'asset_id',
];
$page = 1;

while ($page <= 45) {
    $params['page'] = $page;
    $assetsUrl = 'https://wax.api.atomicassets.io/atomicassets/v1/assets?'.http_build_query($params);
    $assetsResponse = json_decode(get($assetsUrl));

    if ($assetsResponse->success) {
        foreach ($assetsResponse->data as $asset) {
            if (in_array($asset->template->template_id, $availableTemplateIds) && $asset->owner) {
                $cardNameRarity = $asset->template->immutable_data->name;
                $cardNameRarity = explode(' | ', $cardNameRarity);
                $cardName = $cardNameRarity[0];
                $cardRarity = $cardNameRarity[1];

                if (!isset($usersInventory[$asset->owner][$cardName]) ||
                    ($usersInventory[$asset->owner][$cardName]['rarity_rank'] < $rarityRate[$cardRarity] ||
                        ($usersInventory[$asset->owner][$cardName]['rarity_rank'] == $rarityRate[$cardRarity] &&
                            $usersInventory[$asset->owner][$cardName]['mint'] > $asset->template_mint))) {
                    $img = $asset->template->immutable_data->{'Card Front'} ?? $asset->template->immutable_data->front;
                    $usersInventory[$asset->owner][$cardName] = [
                        'name' => $cardName,
                        'rarity' => $cardRarity,
                        'rarity_rank' => $rarityRate[$cardRarity],
                        'template_id' => $asset->template->template_id,
                        'asset_id' => $asset->asset_id,
                        'mint' => $asset->template_mint,
                        'point' => $cardsPoint[$asset->template->template_id],
                        'image' => $img
                    ];
                }
            }
        }
        $page++;
    } else {
        sleep(1);
    }
}

$inventoryTime = microtime(true) - $start;

$limit = 7;
$updatedUsersInventory = [];
foreach ($usersInventory as $user => $inventory) {
    if (count($inventory) >= $limit) {
        uasort($inventory, 'pointSort');
        $inventory = array_slice($inventory, 0, $limit);
        $updatedUsersInventory[$user] = $inventory;
    }
}
$topHolders = [];
foreach ($updatedUsersInventory as $user => $inventory) {
    $point = 0;
    $mint_sum = 0;
    foreach ($inventory as $card) {
        $point += $card['point'];
        $mint_sum += $card['mint'];
    }
    $topHolders[$user]['point'] = $point;
    $topHolders[$user]['mint_sum'] = $mint_sum;
}

uasort($topHolders, 'customSort');
$topHolders = array_slice($topHolders, 0, 51);
$sortTime = microtime(true) - $start;

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css"
          integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm"
          crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
    <title>Gods & Legends | Leaderboard</title>
</head>
<body>
<div class="wrapper">
    <div class="container-fluid" style="min-height: 100vh">
        <?php
        $index = 1;
        $tirImg = 'tir1.png';
        foreach ($topHolders as $holder => $info) {
            if ($index > 40) {
                $tirImg = 'tir5.png';
            } elseif ($index > 30) {
                $tirImg = 'tir4.png';
            } elseif ($index > 20) {
                $tirImg = 'tir3.png';
            } elseif ($index > 10) {
                $tirImg = 'tir2.png';
            } else {
                $tirImg = 'tir1.png';
            }
            ?>
            <div class="row d-flex justify-content-between px-3">
                <div style="width: 2%" class="d-flex align-items-center">
                    <h4><?= $index ?></h4>
                </div>
                <div style="width: 10%">
                    <img class="w-100" src="resources/<?= $tirImg ?>">
                    <h4 class="text-center"><?= $holder ?></h4>
                </div>
                <?php
                foreach ($updatedUsersInventory[$holder] as $card) { ?>
                    <div style="width: 12%" class="text-center">
                        <img class="w-100 pt-3" src="resources/cards/<?= $card['image'] ?>.png" alt="">
                        <small class="text-center w-100"> <?= $card['name'] . ' | ' . $card['rarity'] . ' #' . $card['mint']?></small>
                    </div>
                    <?php
                } ?>
            </div>
            <?php
            $index++;
        }
        ?>
    </div>
</div>
</body>
</html>

