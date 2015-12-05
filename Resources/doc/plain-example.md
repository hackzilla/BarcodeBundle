# Basic Example

```php
<?php
	$barcode = new Barcode();
	$barcode->setMode(Barcode::MODE_PNG);

	$headers = array(
		'Content-Type' => 'image/png',
		'Content-Disposition' => 'inline; filename="'.$code.'.png"'
	);

	echo $barcode->outputImage($code);
```
