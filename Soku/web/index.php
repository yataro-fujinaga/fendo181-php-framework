<?php

// ユーザーのrequestを最初に受け取るファイル

// 起動処理を行う
require_once '../bootstrap.php';

// 
require_once '../PostApplication.php';

echo 'This Soku FW';

$debug = true;

//　エラー表示を行う
// PostApplicationクラスをInstance化
$app = new PostApplication($debug);

// routingに対応する処理を実行してresponseを返す
$app->run();


