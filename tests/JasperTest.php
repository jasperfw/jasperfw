<?php

namespace JasperFW\JasperFWTests;

use JasperFW\JasperFW\Jasper;
use PHPUnit\Framework\TestCase;

class JasperTest extends TestCase
{
    public function testInit()
    {
        Jasper::_init();
        Jasper::i()->run();
    }
}
