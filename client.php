<?php
include 'config.php';
include 'class.YMXcache.php';
$YMcache = new YMXcache($config);
//实例化类

$result1 = $YMcache -> connect(sha1('123456'),'write','test','123');
//(写数据)测试
//参数1为Token
//参数2为方法(method),目前只支持: write(写)/read(读)/memory(取内存信息)/clean(清理所有数据)
//参数3为 缓存ID(名称)
//参数4为 缓存值

$result2 = $YMcache -> connect(sha1('123456'),'read','test');
//(读数据)测试
//参数1为Token
//参数2为方法(method),目前只支持: write(写)/read(读)/memory(取内存信息)/clean(清理所有数据)
//参数3为 缓存ID(名称)

$result3 = $YMcache -> connect(sha1('123456'),'clean');
//(清理)测试
//参数1为Token
//参数2为方法(method),目前只支持: write(写)/read(读)/memory(取内存信息)/clean(清理所有数据)

$result4 = $YMcache -> connect(sha1('123456'),'memory');
//(内存信息)测试
//参数1为Token
//参数2为方法(method),目前只支持: write(写)/read(读)/memory(取内存信息)/clean(清理所有数据)



echo $result1;
echo "\n";
echo $result2;
echo "\n";
echo $result3;
echo "\n";
echo $result4;
?>