<?php

namespace ride\library\event;

use \PHPUnit_Framework_TestCase;

class EventTest extends PHPUnit_Framework_TestCase {

    public function testConstruct() {
        $name = 'event';
        $arguments = array('name' => 'value');

        $event = new Event($name, $arguments);

        $this->assertEquals($name, $event->getName());
        $this->assertEquals($name, (string) $event);
        $this->assertEquals($arguments, $event->getArguments());
    }

    /**
     * @dataProvider providerConstructWithInvalidNameThrowsException
     * @expectedException ride\library\event\exception\EventException
     */
    public function testConstructWithInvalidNameThrowsException($name) {
        new Event($name);
    }

    public function providerConstructWithInvalidNameThrowsException() {
        return array(
            array(array()),
            array($this),
        );
    }

    public function testArguments() {
        $event = new Event('event');
        $event->setArgument('name', 'value');

        $this->assertEquals('value', $event->getArgument('name'));
        $this->assertEquals('default', $event->getArgument('name2', 'default'));

        $event->setArgument('var', 'value2');

        $this->assertEquals('value2', $event->getArgument('var'));

        $event->setArgument('name');

        $this->assertNull($event->getArgument('name'));

        $event->setArgument('var');

        $this->assertEquals(array(), $event->getArguments());
    }

}