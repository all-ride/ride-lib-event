<?php

namespace ride\library\event\loader;

use ride\library\event\GenericEventManager;

use \PHPUnit_Framework_TestCase;
use \ReflectionProperty;

class ChainedEventLoaderTest extends PHPUnit_Framework_TestCase {

    /**
     * @var ride\libarary\event\loader\ChainedEventLoader
     */
    protected $eventLoader;

    protected function setUp() {
        $this->eventLoader = new ChainedEventLoader();
    }

    protected function getProperty($instance, $property) {
        $reflectionProperty = new ReflectionProperty(get_class($instance), $property);
        $reflectionProperty->setAccessible(true);

        return $reflectionProperty->getValue($instance);
    }

    public function testAddAndRemove() {
        $loader1 = $this->getMock('ride\\library\\event\\loader\\EventLoader');
        $loader2 = $this->getMock('ride\\library\\event\\loader\\EventLoader');
        $loader3 = $this->getMock('ride\\library\\event\\loader\\EventLoader');

        $this->eventLoader->addEventLoader($loader1);
        $this->eventLoader->addEventLoader($loader2);
        $this->eventLoader->addEventLoader($loader3, true);

        $this->assertEquals(array($loader3, $loader1, $loader2), $this->getProperty($this->eventLoader, 'eventLoaders'));

        $result = $this->eventLoader->removeEventLoader($loader1);

        $this->assertTrue($result);
        $this->assertEquals(array($loader3, 2 => $loader2), $this->getProperty($this->eventLoader, 'eventLoaders'));

        $result = $this->eventLoader->removeEventLoader($loader1);
        $this->assertFalse($result);
    }

    public function testLoadEventListeners() {
        $loader1 = $this->getMock('ride\\library\\event\\loader\\EventLoader');
        $loader1->expects($this->once())->method('loadEventListeners');

        $loader2 = $this->getMock('ride\\library\\event\\loader\\EventLoader');
        $loader2->expects($this->once())->method('loadEventListeners');

        $this->eventLoader->addEventLoader($loader1);
        $this->eventLoader->addEventLoader($loader2);

        $event = 'event';
        $eventManager = new GenericEventManager();

        $this->eventLoader->loadEventListeners($event, $eventManager);
    }

}