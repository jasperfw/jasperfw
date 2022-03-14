<?php

namespace JasperFW\JasperFWTests;

use JasperFW\JasperFW\Jasper;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the the Jasper class.
 */
class JasperTest extends TestCase
{
    /**
     * Basic test for initializing the framework.
     *
     * @return void
     */
    public function testInit()
    {
        Jasper::_init();
        Jasper::i()->run();
    }
}
