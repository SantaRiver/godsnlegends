<?php

$mysqli = new mysqli("host", "login", "pass", "db");
$query = "INSERT INTO `godsnlegends`.`cards_point` (`templateID`, `point`) VALUES ('".$_GET['templateID']."', '".$_GET['point']."');";
$mysqli->query($query);



header('location: https://godsnlegends.ru/add_template');