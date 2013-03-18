<?php

namespace Hackzilla\BarcodeBundle\Tests\Utility;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Hackzilla\BarcodeBundle\Utility\Barcode;

class BarcodeTest extends WebTestCase
{
    private $_object;

    public function setup()
    {
        $this->_object = new Barcode();
    }

    public function testChecksum()
    {
        $this->assertEquals('5000159415392', $this->_object->generateEanChecksum('5000159415392'));
        $this->assertEquals('9780859340632', $this->_object->generateEanChecksum('9780859340632'));

        $this->assertNotEqual('50001594159', $this->_object->generateEanChecksum('50001594159'));
    }
}