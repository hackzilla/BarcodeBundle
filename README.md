Barcode Bundle
==============

Generate barcodes as html, image or text.

Fork of Folke Ashberg's PHP Barcode v0.4 [http://www.ashberg.de/php-barcode/]


For usage and examples see: Resources/doc/index.rst


Requirements
------------

PHP 5.3.2


Optional
--------

Twig
GD Lib
genbarcode

If you want to generate not only EAN-12/EAN-13/ISBN-Codes you have to install
genbarcode, a small unix-commandline tool which uses GNU-Barcode.
genbarcode is available http://www.ashberg.de/php-barcode , read genbarcodes
README for installation.
If you have installed genbarcode not to /usr/bin call set $barcode->setGenbarcodeLocation('/usr/local/bin/genbarcode')


Installation
------------

Add HackzillaBarcodeBundle in your composer.json:

```js
{
    "require": {
        "hackzilla/barcode-bundle": "*"
    }
}
```

Now tell composer to download the bundle by running the command:

``` bash
$ php composer.phar update hackzilla/barcode-bundle
```

Composer will install the bundle to your project's `vendor/hackzilla` directory.

### Step 2: Enable the bundle

Enable the bundle in the kernel:

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new Hackzilla\BarcodeBundle\HackzillaBarcodeBundle(),
    );
}
```
