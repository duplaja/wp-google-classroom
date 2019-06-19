# php-qr-code
PHP Qr Code Generator compatible with php 7.0
Ported from [http://phpqrcode.sourceforge.net/](http://phpqrcode.sourceforge.net/)

## Installation
The recommended method of installing this library is via [Composer](https://getcomposer.org/).

Run the following command from your project root:

```bash
$ composer require ziplr/php-qr-code
```

## Usage 
```php
require __DIR__ . "/vendor/autoload.php";
QRcode::png('https://github.com/ziplr/php-qr-code', false, QR_ECLEVEL_H, 10, 0);
```

[examples] (http://phpqrcode.sourceforge.net/examples/index.php)