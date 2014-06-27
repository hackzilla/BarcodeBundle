<?php

namespace Hackzilla\BarcodeBundle\Utility;

/*
 * (C) 2001,2002,2003,2004,2011 by Folke Ashberg <folke@ashberg.de>
 * (c) 2013 by Daniel Platt <github@ofdan.co.uk>

 * The previous version can be found at http://www.ashberg.de/php-barcode
 * The newest version can be found at http://github.com/hackzilla/barcode-bundle

 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.

 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
 */

/**
 * Barcode
 *
 */
class Barcode
{

    private $twig;
    private $barColor;
    private $bgColor;
    private $textColor;
    private $fontLocation;
    private $genbarcodeLocation;
    private $encoding;
    private $mode;
    private $scale;
    private $height;
    private $space;

    const MODE_PNG = 'png';
    const MODE_JPEG = 'jpg';
    const MODE_GIF = 'gif';
    const ENCODING_ANY = 'ANY'; // choose best-fit (default)
    const ENCODING_EAN = 'EAN'; // 8 or 13 EAN-Code
    const ENCODING_UPC = 'UPC'; // 12-digit EAN
    const ENCODING_ISBN = 'ISBN'; // isbn numbers (still EAN-13)
    const ENCODING_39 = '39'; // code39
    const ENCODING_128 = '128'; // (a,b,c: autoselection)
    const ENCODING_128C = '128C'; // (compact form for digits)
    const ENCODING_128B = '128B'; // full printable ascii
    const ENCODING_I25 = 'I25'; // interleaved 2 of 5 (only digits)
    const ENCODING_128RAW = '128RAW'; // Raw code 128 (by Leonid A. Broukhis)
    const ENCODING_CBR = 'CBR'; // Codabar (by Leonid A. Broukhis)
    const ENCODING_MSI = 'MSI'; // MSI (by Leonid A. Broukhis)
    const ENCODING_PLS = 'PLS'; // Plessey (by Leonid A. Broukhis)

    public function __construct($container = null)
    {
        if ($container) {
            $this->twig = $container->get('templating');
        }

        $this->setBarColor(array(0, 0, 0));
        $this->setBgColor(array(255, 255, 255));
        $this->setTextColor(array(0, 0, 0));

        $reflClass = new \ReflectionClass(get_class($this));
        $this->setFontLocation(dirname($reflClass->getFileName()) . '/../Resources/font/FreeSansBold.ttf');

        $this->setHeight(120);

        $this->setEncoding(self::ENCODING_ANY);
        $this->setMode(self::MODE_PNG);

        $this->setScale(2);
        $this->setSpace();
    }

    public function barColor($id)
    {
        return $this->barColor[$id];
    }

    public function bgColor($id)
    {
        return $this->bgColor[$id];
    }

    public function textColor($id)
    {
        return $this->textColor[$id];
    }

    public function fontLocation()
    {
        return $this->fontLocation;
    }

    public function genbarcodeLocation()
    {
        return $this->genbarcodeLocation;
    }

    public function height()
    {
        return $this->height;
    }

    public function encoding()
    {
        return $this->encoding;
    }

    public function mode()
    {
        return $this->mode;
    }

    public function scale()
    {
        return $this->scale;
    }

    public function space()
    {
        return $this->space;
    }

    public function setBarColor($color)
    {
        if (\is_array($color) && \count($color) == 3) {
            $this->barColor = $color;
        }

        return $this;
    }

    public function setBgColor($color)
    {
        if (\is_array($color) && \count($color) == 3) {
            $this->bgColor = $color;
        }

        return $this;
    }

    public function setTextColor($color)
    {
        if (\is_array($color) && \count($color) == 3) {
            $this->textColor = $color;
        }

        return $this;
    }

    public function setFontLocation($location)
    {
        if (\is_string($location) && \strlen($location) > 0) {
            $this->fontLocation = $location;
        }

        return $this;
    }

    public function setGenbarcodeLocation($location)
    {
        if (\is_string($location) && \strlen($location) > 0) {
            $this->genbarcodeLocation = $location;
        } else {
            $this->genbarcodeLocation = null;
        }

        return $this;
    }

    public function setEncoding($encoding)
    {
        $this->encoding = \strtoupper($encoding);

        return $this;
    }

    public function setMode($mode)
    {
        $this->mode = $mode;

        return $this;
    }

    public function setScale($scale)
    {
        if ($scale < 1) {
            $scale = 2;
        }

        $this->scale = $scale;

        return $this;
    }

    public function setSpace($space = false)
    {
        $size = 2 * $this->scale();

        if ($space === false || is_array($space)) {
            $this->space = array(
                'top' => $size,
                'left' => $size,
                'bottom' => $size,
                'right' => $size,
            );
        }

        if (is_array($space)) {
            if (isset($space['top']) && is_numeric($space['top'])) {
                $this->space['top'] = $space['top'];
            }
            if (isset($space['left']) && is_numeric($space['left'])) {
                $this->space['left'] = $space['left'];
            }
            if (isset($space['bottom']) && is_numeric($space['bottom'])) {
                $this->space['bottom'] = $space['bottom'];
            }
            if (isset($space['right']) && is_numeric($space['right'])) {
                $this->space['right'] = $space['right'];
            }
        }

        return $this;
    }

    public function setHeight($height)
    {
        if ($height < 1) {
            $height = 1;
        }

        $this->height = $height;

        return $this;
    }

    /**
     * Return an image using libgd
     *
     * @param string $code
     * @return resource
     */
    public function returnImage($code)
    {
        return $this->build($code);
    }

    /**
     * Outputs an image using libgd
     *
     * @param string $code
     */
    public function outputImage($code)
    {
        /* output the image */
        if ($this->mode() == self::MODE_JPEG) {
            $this->outputJpeg($code);
        } else if ($this->mode() == self::MODE_GIF) {
            $this->outputGif($code);
        } else {
            $this->outputPng($code);
        }
    }

    /**
     * Outputs png using libgd
     *
     * @param string $code
     */
    public function outputPng($code)
    {
        $im = $this->returnImage($code);
        \imagepng($im);
    }

    /**
     * Outputs jpeg using libgd
     *
     * @param string $code
     */
    public function outputJpeg($code)
    {
        $im = $this->returnImage($code);
        \imagejpeg($im);
    }

    /**
     * Outputs gif using libgd
     *
     * @param string $code
     */
    public function outputGif($code)
    {
        $im = $this->returnImage($code);
        \imagegif($im);
    }

    /**
     *  Returns (!) a barcode as plain-text
     *  ATTENTION: this is very silly!
     * 
     * @param string $code
     * 
     * @return string
     */
    public function outputText($code)
    {
        $barContainer = $this->encode($code);
        $bars = $barContainer['bars'];
        $barLength = \strlen($bars);

        $width = true;
        $xpos = 0;
        $bar_line = '';

        for ($i = 0; $i < $barLength; $i++) {
            $val = \strtolower($bars[$i]);
            if ($width) {
                $xpos+=$val;
                $width = false;
                $bar_line .= str_repeat('-', $val);
                continue;
            }
            if (\preg_match("#[a-z]#", $val)) {
                $val = \ord($val) - \ord('a') + 1;
                $bar_line .= str_repeat('I', $val);
            } else {
                $bar_line .= str_repeat('#', $val);
            }
            $xpos+=$val;
            $width = true;
        }

        return $bar_line;
    }

    /**
     * HTML-Code for barcode-image using html-code (using a table and with black.png and white.png)
     * 
     * @param string $code
     * 
     * @return string
     */
    public function outputHtml($code)
    {
        $barContainer = $this->encode($code);
        $bars = $barContainer['bars'];
        $barLength = \strlen($bars);

        $outBars = array();

        $total_y = $this->height();
        $scale = $this->scale();
        $space = $this->space();

        /* generate html-code */
        $height = round($total_y - ($scale * 10));
        $height2 = round($total_y) - $space['bottom'];

        $width = true;

        for ($i = 0; $i < $barLength; $i++) {
            $val = strtolower($bars[$i]);
            if ($width) {
                $w = $val * $scale;
                if ($w > 0) {
                    $outBars[] = array(
                        'type' => 'white',
                        'height' => $total_y,
                        'width' => $w,
                    );
                }
                $width = false;
                continue;
            }
            if (preg_match("#[a-z]#", $val)) {
                //hoher strich
                $val = ord($val) - ord('a') + 1;
                $h = $height2;
            } else {
                $h = $height;
            }
            $w = $val * $scale;
            if ($w > 0) {
                $outBars[] = array(
                    'type' => 'black',
                    'height' => $h,
                    'width' => $w,
                );
            }
            $width = true;
        }

        if (\is_object($this->twig)) {
            $out = $this->twig->render('HackzillaBarcodeBundle:Barcode:layout.html.twig', array(
                'height2' => $height2,
                'space_top' => $space['top'],
                'space_bottom' => $space['bottom'],
                'space_left' => $space['left'],
                'space_right' => $space['right'],
                'bars' => $outBars,
            ));
        } else {
            $out = '<p>Twig not enabled in bundle</p>';
        }

        return $out;
    }

    /**
     * encodes $code with $encoding using genbarcode
     * 
     * @param string $code
     * 
     * @return array
     *    array[encoding] : the encoding which has been used
     *    array[bars]     : the bars
     *    array[text]     : text-positioning info
     */
    public function encodeGenbarcode($code)
    {
        $encoding = \preg_replace("#[|\\\\]#", "_", $this->encoding());
        /* delete EAN-13 checksum */
        if (\preg_match("#^ean$#i", $encoding) && strlen($code) == 13) {
            $code = \substr($code, 0, 12);
        }
        $code = \preg_replace("#[|\\\\]#", "_", $code);
        $cmd = $this->genbarcodeLocation() . " " . \escapeshellarg($code) . " " . \escapeshellarg($encoding) . "";

        $fp = \popen($cmd, 'r');

        if (!$fp) {
            return false;
        }

        $ret = array(
            'bars' => \trim(\fgets($fp, 1024)),
            'text' => \trim(\fgets($fp, 1024)),
            'encoding' => \trim(\fgets($fp, 1024)),
        );

        \pclose($fp);

        if (!$ret['encoding'] || !$ret['bars'] || !$ret['text']) {
            return false;
        }

        return $ret;
    }

    /**
     * 
     * @param string $code
     * 
     * @return boolean|array
     *    array[encoding] : the encoding which has been used
     *    array[bars]     : the bars
     *    array[text]     : text-positioning info
     */
    public function encode($code)
    {
        if (
                ((preg_match("#^ean$#i", $this->encoding()) && ( strlen($code) == 12 || strlen($code) == 13))) || (($this->encoding()) && (preg_match("#^isbn$#i", $this->encoding())) && (( strlen($code) == 9 || strlen($code) == 10) ||
                (((preg_match("#^978#", $code) && strlen($code) == 12) ||
                (strlen($code) == 13))))) || ((!$this->encoding() || (preg_match("#^ANY$#i", $this->encoding()) )) && (preg_match("#^[0-9]{12,13}$#", $code)))
        ) {
            /* use built-in EAN-Encoder */
            $bars = $this->encodeEan($code);
        } else if (file_exists($this->genbarcodeLocation())) {
            /* use genbarcode */
            $bars = $this->encodeGenbarcode($code);
        } else {
            print "BarcodeBundle needs an external programm for encodings other then EAN/ISBN<BR>\n";
            print "<ul>\n";
            print "<li>download gnu-barcode from <a href=\"http://www.gnu.org/software/barcode/\">www.gnu.org/software/barcode/</a></li>\n";
            print "<li>compile and install them</li>\n";
            print "<li>download genbarcode from <a href=\"http://www.ashberg.de/php-barcode/\">www.ashberg.de/php-barcode/</a></li>\n";
            print "<li>compile and install them</li>\n";
            print "<li>specify path to genbarcode in php-barcode.php</li>\n";
            print "</ul>\n";
            print "<br />\n";
            print "<a href=\"http://www.ashberg.de/php-barcode/\">Folke Ashberg's OpenSource PHP-Barcode</a><br />\n";

            return false;
        }

        return $bars;
    }

    /**
     * encodes and prints a barcode
     * 
     * @param string $code
     */
    public function output($code)
    {
        $mode = $this->mode();

        if (\preg_match("#^(text|txt|plain)$#i", $mode)) {
            print $this->outputText($code);
        } else if (\preg_match("#^(html|htm)$#i", $mode)) {
            print $this->outputHtml($code);
        } else {
            $this->outputImage($code);
        }
    }

    /**
     * Build barcode
     *
     * @param string $code
     *
     * @return resource
     */
    public function build($code)
    {
        $bars = $this->encode($code);

        if (!$bars) {
            return;
        }

        $text = $bars['text'];
        $bars = $bars['bars'];
        $barsLength = \strlen($bars);

        $total_y = $this->height();
        $scale = $this->scale();
        $space = $this->space();

        /* count total width */
        $xpos = 0;
        $width = true;
        for ($i = 0; $i < $barsLength; $i++) {
            $val = \strtolower($bars[$i]);
            if ($width) {
                $xpos+=$val * $scale;
                $width = false;
                continue;
            }
            if (\preg_match("#[a-z]#", $val)) {
                /* tall bar */
                $val = \ord($val) - \ord('a') + 1;
            }
            $xpos+=$val * $scale;
            $width = true;
        }

        /* allocate the image */
        $total_x = $xpos + $space['right'] + $space['right'];
        $xpos = $space['left'];

        $im = \imagecreate($total_x, $total_y);

        /* create two images */
        \ImageColorAllocate($im, $this->bgColor(0), $this->bgColor(1), $this->bgColor(2));
        $col_bar = \ImageColorAllocate($im, $this->barColor(0), $this->barColor(1), $this->barColor(2));
        $col_text = \ImageColorAllocate($im, $this->textColor(0), $this->textColor(1), $this->textColor(2));
        $height = \round($total_y - ($scale * 10));
        $height2 = \round($total_y - $space['bottom']);


        /* paint the bars */
        $width = true;
        for ($i = 0; $i < $barsLength; $i++) {
            $val = \strtolower($bars[$i]);
            if ($width) {
                $xpos+=$val * $scale;
                $width = false;
                continue;
            }
            if (\preg_match("#[a-z]#", $val)) {
                /* tall bar */
                $val = \ord($val) - \ord('a') + 1;
                $h = $height2;
            } else {
                $h = $height;
            }
            \imagefilledrectangle($im, $xpos, $space['top'], $xpos + ($val * $scale) - 1, $h, $col_bar);
            $xpos+=$val * $scale;
            $width = true;
        }

        /* write out the text */
        $chars = \explode(" ", $text);
        \reset($chars);
        foreach ($chars as $v) {
            if (trim($v)) {
                $inf = explode(":", $v);
                $fontsize = $scale * ($inf[1] / 1.8);
                $fontheight = $total_y - ($fontsize / 2.7) + 2;
                \imagettftext($im, $fontsize, 0, $space['left'] + ($scale * $inf[0]) + 2, $fontheight, $col_text, $this->fontLocation(), $inf[2]);
            }
        }

        return $im;
    }

    /**
     * Save an image of the barcode to $filename or a generated filename
     * 
     * @param string $code
     *
     * @return string $filename
     */
    public function save($code, $filename = false)
    {
        if (!$filename) {
            $filename = tmpfile();
        }

        $im = $this->build($code);
        $mode = $this->mode();

        /* output the image */
        if ($mode == self::MODE_JPEG) {
            imagejpeg($im, $filename);
        } else if ($mode == self::MODE_GIF) {
            imagegif($im, $filename);
        } else {
            imagepng($im, $filename);
        }

        return $filename;
    }

    /**
     * Generate the Ean Checksum
     * Pass in first 12 digits
     *
     * @param string $ean (without checksum)
     *
     * @return int
     */
    public function generateEanChecksum($ean)
    {
        $even = true;
        $esum = 0;
        $osum = 0;

        for ($i = strlen($ean) - 1; $i >= 0; $i--) {
            if ($even) {
                $esum+=$ean[$i];
            } else {
                $osum+=$ean[$i];
            }
            $even = !$even;
        }
        return (10 - ((3 * $esum + $osum) % 10)) % 10;
    }

    /**
     * encodes $ean with EAN-13 using builtin functions
     *
     * @param type $ean
     * 
     * @return array
     *    array[encoding] : the encoding which has been used (EAN-13)
     *    array[bars]     : the bars
     *    array[text]     : text-positioning info
     */
    public function encodeEan($ean)
    {
        $digits = array(3211, 2221, 2122, 1411, 1132, 1231, 1114, 1312, 1213, 3112);
        $mirror = array("000000", "001011", "001101", "001110", "010011", "011001", "011100", "010101", "010110", "011010");
        $guards = array("9a1a", "1a1a1", "a1a");

        $ean = \trim($ean);
        $encoding = $this->encoding();

        if (\preg_match("#[^0-9]#i", $ean)) {
            return array("text" => "Invalid EAN-Code");
        }

        if ($encoding == "ISBN") {
            if (!\preg_match("#^978#", $ean))
                $ean = "978" . $ean;
        } else if (\preg_match("#^978#", $ean)) {
            $encoding = "ISBN";
        }

        if (\strlen($ean) < 12 || \strlen($ean) > 13) {
            return array("text" => "Invalid {$encoding} Code (must have 12/13 numbers)");
        }

        $ean = \substr($ean, 0, 12) . $this->generateEanChecksum($ean);

        return array(
            "encoding" => $encoding,
            "bars" => $this->createLine($guards, $digits, $mirror, $ean),
            "text" => $this->createText($ean)
        );
    }

    /**
     * Create line
     * 
     * @param array $guards
     * @param array $digits
     * @param array $mirror
     * @param string $ean
     * 
     * @return string
     */
    public function createLine($guards, $digits, $mirror, $ean)
    {
        $line = $guards[0];

        for ($i = 1; $i < 13; $i++) {
            $str = $digits[$ean[$i]];
            if ($i < 7 && $mirror[$ean[0]][$i - 1] == 1) {
                $line .= strrev($str);
            } else {
                $line .= $str;
            }
            if ($i == 6) {
                $line .= $guards[1];
            }
        }
        $line .= $guards[2];

        return $line;
    }

    /**
     * Create barcode text
     * @param string $ean
     *
     * @return string
     */
    public function createText($ean)
    {
        $pos = 0;
        $text = "";

        for ($a = 0; $a < 13; $a++) {
            if ($a > 0) {
                $text.=" ";
            }

            $text.="$pos:12:{$ean[$a]}";

            if ($a == 0) {
                $pos+=12;
            } else if ($a == 6) {
                $pos+=12;
            } else {
                $pos+=7;
            }
        }

        return $text;
    }

}
