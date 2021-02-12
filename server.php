<?php
include 'config.php';
include 'class.YMXcache.php';
$YMcache = new YMXcache($config);
//实例化类

$YMcache -> start();
//开始运行
?>