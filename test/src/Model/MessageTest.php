<?php

namespace BsbDoctrineTranslatorTest\Model;

use BsbDoctrineTranslator\Model\Message;
use BsbDoctrineTranslatorTest\Framework\TestCase;

class MessageTest extends TestCase
{
    /**
     * @var Message
     */
    private $message;

    public function setup()
    {
        $this->message = new Message();
    }

    public function dataProviderAccessor()
    {
        return array(
            array('Kind', 10, 10),
            array('Key', false, false),
            array('Key', 'foo', 'foo'),
            array('Key', null, null),
            array('KeyInfo', 'foo', 'foo'),
            array('KeyInfo', null, null),
            array('Locale', false, false),
            array('Locale', 'foo', 'foo'),
            array('Locale', null, null),
            array('LocaleInfo', 'foo', 'foo'),
            array('LocaleInfo', null, null),
            array('Translation', 'foo', 'foo'),
            array('Translation', null, null),
            array('TextDomain', false, false),
            array('TextDomain', 'foo', 'foo'),
            array('TextDomain', null, null),
            array('TextDomainInfo', 'foo', 'foo'),
            array('TextDomainInfo', null, null),
            array('Origin', array(), null),
            array('Origin', 'foo', 'foo'),
            array('Origin', array('foo', 'bar'), array('foo', 'bar')),
        );
    }

    public function testConstructWithKindArgument()
    {
        $expected = 'foo';
        $message  = new Message($expected);

        $this->assertEquals($expected, $message->getKind());
    }

    public function testConstructWithNoKindArgument()
    {
        $message = new Message();

        $this->assertNull($message->getKind());
    }

    /**
     * @dataProvider dataProviderAccessor
     */
    public function testAccessors($accessor, $input, $expected)
    {
        $setter = 'set' . $accessor;
        $getter = 'get' . $accessor;
        $this->assertNull($this->message->$getter());

        $this->message->$setter($input);
        $this->assertEquals($expected, $this->message->$getter());
    }

    public function testAddOrigin()
    {
        $this->message->addOrigin('foo');
        $this->assertEquals('foo', $this->message->getOrigin());

        $this->message->addOrigin('bar');
        $this->assertEquals(array('foo', 'bar'), $this->message->getOrigin());

        $this->message->addOrigin('bar');
        $this->assertEquals(array('foo', 'bar'), $this->message->getOrigin());

        $this->message->addOrigin(array('xxx', 'yyy', 'xxx'));
        $this->assertEquals(array('foo', 'bar', 'xxx', 'yyy'), $this->message->getOrigin());
    }
}
