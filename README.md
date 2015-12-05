Barcode Bundle
==============

Generate barcodes as html, image or text.

Fork of Folke Ashberg's PHP Barcode v0.4 [http://www.ashberg.de/php-barcode/]


For usage and examples see: [Examples](Resources/doc/index.md)

[![Build Status](https://travis-ci.org/hackzilla/BarcodeBundle.png?branch=master)](https://travis-ci.org/hackzilla/BarcodeBundle)

### Requirements

* PHP 5.3.2


#### Optional

* GD Lib
* genbarcode

If you want to generate not only EAN-12/EAN-13/ISBN-Codes you have to install
genbarcode, a small unix-commandline tool which uses GNU-Barcode.
genbarcode is available http://www.ashberg.de/php-barcode , read genbarcodes
README for installation.
If you have installed genbarcode not to ```/usr/local/bin``` call ```$barcode->setGenbarcodeLocation('/usr/local/bin/genbarcode')```



### Step 1: Installation

Install Composer

```
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
```

Now tell composer to download the bundle by running the command:

``` bash
$ composer require hackzilla/barcode-bundle ~2.0
```

Composer will add the bundle to your composer.json file and install it into your project's `vendor/hackzilla` directory.


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

### Step 3: Further documentation

* [examples list](Resources/doc/index.md)
