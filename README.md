# YMX-Cache

一个菜鸡写的Socket缓存器

## 注意事项

1.此程序效率较差(2000次写数据需7秒,单次0.009秒左右)

2.此程序仅供本地读写,暂不支持外网使用(AF_UNIX域)

3.客户端和服务端共用`class.YMXcache.php`文件

## 目录结构

```
 ├─ class.YMXcache.php    //核心文件
 ├─ config.php            //配置项
 ├─ client.php            //Socket缓存客户端测试
 └─ server.php            //Socket缓存服务端文件
```

## 配置相关

$config:

> (int)buffer: 最大缓存字节值(默认值:8192) 注: json结构也计算

> (string)token: 授权密匙(默认值:123456)

> (string)auth: 鉴权函数(默认值:sha1)

> (int)console: (服务端)控制台输出信息

    0: 全部信息

    1: 只输出(缓存)系统信息

    2: 不输出任何信息

## 安装

### 1.下载ZIP

### 2.Git

```
git clone https://github.com/yimo6/YMX-Cache.git
```

### 返回说明

#### 结构:
```
{"code":200,"msg":"Success","data":"true"}
```

## 使用许可

[MIT](LICENSE) © Richard Littauer