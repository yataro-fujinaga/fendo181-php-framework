<?php

// Classファイルの自動読み込み
require 'core/ClassLoader.php';

// autoloadをするClassをInstance化
$loader = new ClassLoader();

// core、models、exceptionsディレクトリをautoload先のディレクトリとして指定する
$loader->registerDir(dirname(__FILE__).'/core');
$loader->registerDir(dirname(__FILE__).'/models');
$loader->registerDir(dirname(__FILE__).'/exceptions');

// autoloadを実行する
$loader->register();