<?php

namespace Hackzilla\BarcodeBundle\Tests\Utility;

use Hackzilla\BarcodeBundle\Utility\Barcode;

class BarcodeTest extends \PHPUnit_Framework_TestCase
{

    private $_object;

    public function setup()
    {
        $this->_object = new Barcode();
    }

    public function testBarColor()
    {
        $this->_object->setBarColor(array(123, 456, 789));
        $this->assertEquals(123, $this->_object->barColor(0));
        $this->assertEquals(456, $this->_object->barColor(1));
        $this->assertEquals(789, $this->_object->barColor(2));

        $this->_object->setBarColor(0);
        $this->assertEquals(123, $this->_object->barColor(0));
        $this->assertEquals(456, $this->_object->barColor(1));
        $this->assertEquals(789, $this->_object->barColor(2));
    }

    public function testBgColor()
    {
        $this->_object->setBgColor(array(123, 456, 789));
        $this->assertEquals(123, $this->_object->bgColor(0));
        $this->assertEquals(456, $this->_object->bgColor(1));
        $this->assertEquals(789, $this->_object->bgColor(2));

        $this->_object->setBgColor(0);
        $this->assertEquals(123, $this->_object->bgColor(0));
        $this->assertEquals(456, $this->_object->bgColor(1));
        $this->assertEquals(789, $this->_object->bgColor(2));
    }

    public function testTextColor()
    {
        $this->_object->setTextColor(array(123, 456, 789));
        $this->assertEquals(123, $this->_object->textColor(0));
        $this->assertEquals(456, $this->_object->textColor(1));
        $this->assertEquals(789, $this->_object->textColor(2));

        $this->_object->setTextColor(0);
        $this->assertEquals(123, $this->_object->textColor(0));
        $this->assertEquals(456, $this->_object->textColor(1));
        $this->assertEquals(789, $this->_object->textColor(2));
    }

    public function testFontLocation()
    {
        $this->_object->setFontLocation('/path/testing');
        $this->assertEquals('/path/testing', $this->_object->fontLocation());
    }

    public function testGenBarcodeLocation()
    {
        $this->_object->setGenBarcodeLocation('/path/testing');
        $this->assertEquals('/path/testing', $this->_object->genBarcodeLocation());
    }

    public function testEncoding()
    {
        $this->_object->setEncoding(Barcode::ENCODING_ANY);
        $this->assertEquals(Barcode::ENCODING_ANY, $this->_object->encoding());
    }

    public function testMode()
    {
        $this->_object->setMode(Barcode::MODE_PNG);
        $this->assertEquals(Barcode::MODE_PNG, $this->_object->mode());
    }

    public function testScale()
    {
        $this->_object->setScale(0);
        $this->assertEquals(2, $this->_object->scale());

        $this->_object->setScale(-100);
        $this->assertEquals(2, $this->_object->scale());

        $this->_object->setScale(100);
        $this->assertEquals(100, $this->_object->scale());
    }

    public function testSpace()
    {
        $this->_object->setSpace(array(
            'top' => 12,
            'left' => 34,
            'bottom' => 56,
            'right' => 78,
        ));

        $space = $this->_object->space();
        $this->assertEquals(12, $space['top']);
        $this->assertEquals(34, $space['left']);
        $this->assertEquals(56, $space['bottom']);
        $this->assertEquals(78, $space['right']);
    }

    public function testHeight()
    {
        $this->_object->setHeight(0);
        $this->assertEquals(1, $this->_object->height());

        $this->_object->setHeight(-100);
        $this->assertEquals(1, $this->_object->height());

        $this->_object->setHeight(100);
        $this->assertEquals(100, $this->_object->height());
    }

    public function testChecksum()
    {
        $this->assertEquals(2, $this->_object->generateEanChecksum('500015941539'));
        $this->assertEquals(2, $this->_object->generateEanChecksum('978085934063'));
        $this->assertEquals(5, $this->_object->generateEanChecksum('978178072167'));

        $this->assertNotEquals(7, $this->_object->generateEanChecksum('50001594159'));
    }

    public function testEncodeEan()
    {
        $bars = $this->_object->encodeEan('9781780721675');
        $this->assertEquals('ISBN', $bars['encoding']);
        $this->assertEquals('9a1a1312312112221312312132111a1a1131221222221111413121231a1a', $bars['bars']);
        $this->assertEquals('0:12:9 12:12:7 19:12:8 26:12:1 33:12:7 40:12:8 47:12:0 59:12:7 66:12:2 73:12:1 80:12:6 87:12:7 94:12:5', $bars['text']);

        $this->setExpectedException('\Hackzilla\BarcodeBundle\Exception\InvalidBarcodeFormatException');
        $bars = $this->_object->encodeEan('fail');
    }

    public function testReturnImage()
    {
        $this->assertTrue(\is_resource($this->_object->returnImage('978085934063')));
    }

}
