# php-device
主机硬件信息

目前支持LINUX

##安装
```
composer require hahadu/php-device
```

##使用
```php
require_once "vendor/autoload.php";

    $device = new \Hahadu\DeviceInfo\Device();
    $D = $device->get_list();

```
