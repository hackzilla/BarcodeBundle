Basic Example
=============

```php

// src/Acme/DemoBundle/Controller/Default.php

namespace Acme\DemoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Hackzilla\BarcodeBundle\Utility\Barcode;

class Default extends Controller
{
    /**
     * Display code as a png image
     */
    public function barcodeImageAction($code)
    {
        $barcode = new Barcode();
        $barcode->setMode(Barcode::mode_png);

        $headers = array(
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'inline; filename="'.$code.'.png"'
        );

        return new Response($barcode->outputImage($code), 200, $headers);
    }

    /**
     * Display code using html
     */
    public function barcodeHtmlAction($code)
    {
        $barcode = new Barcode($this->container);

        $headers = array(
        );

        return new Response($barcode->outputHtml($code), 200, $headers);
    }

    /**
     * Display code as a textual representation
     */
    public function barcodeTextAction($code)
    {
        $barcode = new Barcode();

        $headers = array(
        );

        return new Response($barcode->outputText($code), 200, $headers);
    }

    /**
     * Return image in a zip
     */
    public function barcodeZipAction($code)
    {
        $isbns = array(
            '978085934063',
            '500015941539',
        );
  
        $zipDir = sys_get_temp_dir();

        if (substr($zipDir, -1) !== '/') {
            $zipDir .= '/';
        }

        $zip = new \ZipArchive();
        $zipName = 'barcodes-' . time() . ".zip";
        $zip->open($zipDir . $zipName, \ZipArchive::CREATE);

        foreach ($isbns as $i => $isbn) {
            $barcode = \trim($isbn);
            $zip->addFromString($barcode . '.png', $this->getImage($barcode));
        }

        $zip->close();

        $response = new Response();
        $response->setContent(readfile($zipDir . $zipName));
        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set('Content-disposition', 'attachment; filename="' . $zipName . '"');
        $response->headers->set('Content-Length', filesize($zipDir . $zipName));

        return $response;
    }

    private function getImage($ean)
    {
        ob_start();

        $barcodeGenerator = new Barcode();
        $barcodeGenerator->setMode(Barcode::mode_png);
        $barcodeGenerator->outputImage($ean);

        $contents = ob_get_clean();

        return $contents;
    }
}
```
