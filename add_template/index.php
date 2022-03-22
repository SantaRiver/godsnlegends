<?php

$mysqli = new mysqli("host", "login", "pass", "db");
$query = "SELECT * FROM `cards_point` ORDER BY `cards_point`.`id` DESC";
$templates = [];

if ($sqlResult = $mysqli->query($query)) {
    while ($obj = $sqlResult->fetch_object()) {
        $templates[] = $obj;
    }
}
$sqlResult->close();
unset($obj);
unset($query);

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
    <title>Gods & Legends | Add Template</title>
</head>
<body>
<div class="wrapper" style="min-height: 100vh">
    <div class="container pt-5">
        <div class="row d-flex justify-content-center">
            <div class="col-5 bg-light p-3 rounded">
                <h3 class="text-center">Add template</h3>
                <form class="pt-3" action="update.php">
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label for="templateInput">Template ID</label>
                                <input type="text" name="templateID" class="form-control" id="templateInput"
                                       placeholder="123456" required>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label for="pointInput">Point</label>
                                <input type="number" name="point" class="form-control" id="pointInput" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 d-flex justify-content-between">
                            <a href="https://godsnlegends.ru/holders/update_img.php"
                               class="btn btn-primary px-4">Update Image</a>
                            <button class="btn btn-primary px-4">Save</button>
                        </div>
                    </div>
                </form>
                <div class="row pt-5">
                    <div class="col-12">
                        <table class="table">
                            <thead>
                            <tr>
                                <th scope="col">Template ID</th>
                                <th scope="col">Point</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            foreach ($templates as $template) { ?>
                                <tr>
                                    <td>
                                        <a href="https://wax.atomichub.io/explorer/template/godsnlegends/<?= $template->templateID ?>"
                                           target="_blank">
                                            <?= $template->templateID ?>
                                        </a>
                                    </td>
                                    <td><?= $template->point ?></td>
                                </tr>
                                <?php
                            } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
