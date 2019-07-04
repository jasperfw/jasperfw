<?php
/**
 * Created by IntelliJ IDEA.
 * User: inter
 * Date: 7/6/2017
 * Time: 5:51 AM
 */

namespace WigeDev\JasperCoreTests\ServiceManager;

use PHPUnit\Framework\TestCase;
use WigeDev\JasperCore\Exception\ServiceManagerNotFoundException;
use WigeDev\JasperCore\ServiceManager\ServiceManagerManager;

class ServiceManagerManagerTest extends TestCase
{
    public function setUp()
    {
        //TODO: This
    }

    /**
     * @throws ServiceManagerNotFoundException
     */
    public function testConstruct()
    {
        $manager = new ServiceManagerManager();
        $this->assertTrue($manager->exists('input'));
        $service = $manager->get('input');
        $this->assertTrue(is_a($service, '\Core\ServiceManager\InputManager'));
    }

    public function testCanGetServiceManagerByName()
    {
        //TODO: This
    }

    public function testRequestingInvalidServiceManagerThrowsServiceManagerNotFoundException()
    {
        //TODO: This
    }

    public function testCheckExistsReturnsTrueIfServiceManagerRegistered()
    {
        //TODO: This
    }

    public function testCheckExistsReturnsFalseIfServiceManagerNotRegistered()
    {
        //TODO: This
    }
}
