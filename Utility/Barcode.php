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
    private $bgColour;
    private $textColour;
    private $fontLocation;
    private $genbarcodeLocation;

    private $encoding;
    private $mode;
    private $scale;
    private $height;
    private $space;

    const mode_png = 'png';
    const mode_jpeg = 'jpg';
    const mode_gif = 'gif';

    const encoding_any = 'ANY'; // choose best-fit (default)
    const encoding_ean = 'EAN'; // 8 or 13 EAN-Code
    const encoding_upc = 'UPC'; // 12-digit EAN
    const encoding_isbn = 'ISBN'; // isbn numbers (still EAN-13)
    const encoding_39 = '39'; // code39
    const encoding_128 = '128'; // (a,b,c: autoselection)
    const encoding_128c = '128C'; // (compact form for digits)
    const encoding_128b = '128B'; // full printable ascii
    const encoding_i25 = 'I25'; // interleaved 2 of 5 (only digits)
    const encoding_128raw = '128RAW'; // Raw code 128 (by Leonid A. Broukhis)
    const encoding_cbr = 'CBR'; // Codabar (by Leonid A. Broukhis)
    const encoding_msi = 'MSI'; // MSI (by Leonid A. Broukhis)
    const encoding_pls = 'PLS'; // Plessey (by Leonid A. Broukhis)

    public function __construct($container = null)
    {
        if ($container) {
            $this->twig = $container->get('templating');
        }

        $this->setBarColor(array(0, 0, 0));
        $this->setBgColor(array(255, 255, 255));
        $this->setTextColor(array(0, 0, 0));

        $this->setFontLocation(dirname(__FILE__) . "/../Resources/font/FreeSansBold.ttf");

        //$this->setGenbarcodeLocation("c:\winnt\genbarcode.exe");
        $this->setGenbarcodeLocation("/usr/local/bin/genbarcode");

        $this->setHeight(120);

        $this->setEncoding(self::encoding_any);
        $this->setMode(self::mode_png);
        
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

        if($space === false || is_array($space)) {
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
        if($height < 1) {
            $height = 1;
        }

        $this->height = $height;

        return $this;
    }

    /**
     * Outputs an image using libgd
     * 
     * @param string $code
     */
    public function outputImage($code)
    {
        $im = $this->build($code);

        /* output the image */
        if ($this->mode() == self::mode_jpeg) {
            \header("Content-Type: image/jpeg; name=\"barcode.jpg\"");
            \imagejpeg($im);
        } else if ($this->mode() == self::mode_gif) {
            \header("Content-Type: image/gif; name=\"barcode.gif\"");
            \imagegif($im);
        } else {
            \header("Content-Type: image/png; name=\"barcode.png\"");
            \imagepng($im);
        }
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
        $bars = $this->encode($code);
        $bars = $bars['bars'];
        
        $width = true;
        $xpos = $heigh2 = 0;
        $bar_line = "";

        for ($i = 0; $i < \strlen($bars); $i++) {
            $val = \strtolower($bars[$i]);
            if ($width) {
                $xpos+=$val;
                $width = false;
                for ($a = 0; $a < $val; $a++) {
                    $bar_line.="-";
                }
                continue;
            }
            if (\preg_match("#[a-z]#", $val)) {
                $val = \ord($val) - \ord('a') + 1;
                $h = $heigh2;
                for ($a = 0; $a < $val; $a++) {
                    $bar_line.="I";
                }
            } else {
                for ($a = 0; $a < $val; $a++) {
                    $bar_line.="#";
                }
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
        $bars = $this->encode($code);
        $bars = $bars['bars'];
        $outBars = array();
     
        $total_y = $this->height();
        $scale = $this->scale();
        $space = $this->space();

        /* generate html-code */
        $height = round($total_y - ($scale * 10));
        $height2 = round($total_y) - $space['bottom'];

        $width = true;
        for ($i = 0; $i < strlen($bars); $i++) {
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

        if(\is_object($this->twig)) {
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
        /* delete EAN-13 checksum */
        if (\preg_match("#^ean$#i", $encoding) && strlen($code) == 13) {
            $code = \substr($code, 0, 12);
        }
        $encoding = \preg_replace("#[|\\\\]#", "_", $this->encoding());
        $code = \preg_replace("#[|\\\\]#", "_", $code);
        $cmd = $this->genbarcodeLocation() . " "
                . \escapeshellarg($code) . " "
                . \escapeshellarg($encoding) . "";
//print "'$cmd'<BR>\n";
        $fp = \popen($cmd, "r");
        if ($fp) {
            $bars = \fgets($fp, 1024);
            $text = \fgets($fp, 1024);
            $encoding = \fgets($fp, 1024);
            \pclose($fp);
        } else {
            return false;
        }
        $ret = array(
            "encoding" => \trim($encoding),
            "bars" => \trim($bars),
            "text" => \trim($text)
        );
        if (!$ret['encoding']) {
            return false;
        }
        if (!$ret['bars']) {
            return false;
        }
        if (!$ret['text']) {
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

    public function build($code, $filename = false)
    {
        $bars = $this->encode($code);

        if (!$bars) {
            return;
        }

        if (!$filename) {
            $filename = $code . '.' . $this->mode();
        }

        $text = $bars['text'];
        $bars = $bars['bars'];
        $total_y = $this->height();
        $scale = $this->scale();
        $space = $this->space();

        /* count total width */
        $xpos = 0;
        $width = true;
        for ($i = 0; $i < \strlen($bars); $i++) {
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
        $col_bg = \ImageColorAllocate($im, $this->bgColor(0), $this->bgColor(1), $this->bgColor(2));
        $col_bar = \ImageColorAllocate($im, $this->barColor(0), $this->barColor(1), $this->barColor(2));
        $col_text = \ImageColorAllocate($im, $this->textColor(0), $this->textColor(1), $this->textColor(2));
        $height = \round($total_y - ($scale * 10));
        $height2 = \round($total_y - $space['bottom']);


        /* paint the bars */
        $width = true;
        for ($i = 0; $i < \strlen($bars); $i++) {
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
        while (list($n, $v) = each($chars)) {
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
        if(!$filename) {
            $filename = tmpfile();
        }

        $im = $this->build($code, $encoding, $scale, $mode, $filename);
        $mode = $this->mode();

        /* output the image */
        if ($mode == self::jpeg) {
            imagejpeg($im, $filename);
        } else if ($mode == self::gif) {
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
        }
        if (\preg_match("#^978#", $ean)) {
            $encoding = "ISBN";
        }
        if (\strlen($ean) < 12 || \strlen($ean) > 13) {
            return array("text" => "Invalid {$encoding} Code (must have 12/13 numbers)");
        }

        $ean = \substr($ean, 0, 12);
        $eansum = $this->generateEanChecksum($ean);
        $ean .= $eansum;
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

        /* create text */
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

        return array(
            "encoding" => $encoding,
            "bars" => $line,
            "text" => $text
        );
    }

}
