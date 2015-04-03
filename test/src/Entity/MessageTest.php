<?php

namespace BsbDoctrineTranslatorTest\Entity;

use BsbDoctrineTranslator\Entity\Message;
use BsbDoctrineTranslatorTest\Framework\TestCase;

class MessageTest extends TestCase
{
    public function testAccessorsOrigin()
    {
        $message = new Message();

        $expected = array('foo');
        $message->setOrigin($expected);
        $this->assertEquals($expected, $message->getOrigin());
    }
}
