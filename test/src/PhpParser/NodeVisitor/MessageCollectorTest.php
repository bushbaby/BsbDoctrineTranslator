<?php

namespace BsbDoctrineTranslatorTest\PhpParser\NodeVisitor;

use BsbDoctrineTranslator\Model\Message;
use BsbDoctrineTranslator\PhpParser\NodeVisitor\MessageCollector;
use BsbDoctrineTranslatorTest\Framework\TestCase;
use PhpParser\Node;

class MessageCollectorTest extends TestCase
{
    /**
     * EnterNode should collect *translate* *method* calls by setting protected property message
     */
    public function testEnterNodeCollectsTranslateMethodNode()
    {
        $reflection = new \ReflectionClass('BsbDoctrineTranslator\PhpParser\NodeVisitor\MessageCollector');
        $property   = $reflection->getProperty('message');
        $property->setAccessible(true);

        $collector = new MessageCollector('test/src/Asset/code.php');
        $exp       = $this->getMock('PhpParser\Node\Expr');
        $node      = new \PhpParser\Node\Expr\MethodCall($exp, 'translate');

        $collector->enterNode($node);

        $this->assertNotNull($property->getValue($collector));
    }

    /**
     * EnterNode should collect *translate* *function* calls by setting protected property message
     */
    public function testEnterNodeCollectsTranslateFunctionNode()
    {
        $reflection = new \ReflectionClass('BsbDoctrineTranslator\PhpParser\NodeVisitor\MessageCollector');
        $property   = $reflection->getProperty('message');
        $property->setAccessible(true);

        $collector = new MessageCollector('test/src/Asset/code.php');
        $node      = new \PhpParser\Node\Expr\FuncCall('translate');

        $collector->enterNode($node);

        $this->assertNotNull($property->getValue($collector));
    }

    /**
     * EnterNode should collect *translatePlural* *method* calls by setting protected property message
     */
    public function testEnterNodeCollectsTranslatePluralMethodNode()
    {
        $reflection = new \ReflectionClass('BsbDoctrineTranslator\PhpParser\NodeVisitor\MessageCollector');
        $property   = $reflection->getProperty('message');
        $property->setAccessible(true);

        $collector = new MessageCollector('test/src/Asset/code.php');
        $exp       = $this->getMock('PhpParser\Node\Expr');
        $node      = new \PhpParser\Node\Expr\MethodCall($exp, 'translatePlural');

        $collector->enterNode($node);

        $this->assertNotNull($property->getValue($collector));
    }

    /**
     * EnterNode should collect *translatPlurale* *function* calls by setting protected property message
     */
    public function testEnterNodeCollectsTranslatePluralFunctionNode()
    {
        $reflection = new \ReflectionClass('BsbDoctrineTranslator\PhpParser\NodeVisitor\MessageCollector');
        $property   = $reflection->getProperty('message');
        $property->setAccessible(true);

        $collector = new MessageCollector('test/src/Asset/code.php');
        $node      = new \PhpParser\Node\Expr\FuncCall('translatePlural');

        $collector->enterNode($node);

        $this->assertNotNull($property->getValue($collector));
    }

    /**
     * LeaveNode should reset protected property message
     */
    public function testLeaveNodeResetsPropertyMessageToNull()
    {
        $reflection = new \ReflectionClass('BsbDoctrineTranslator\PhpParser\NodeVisitor\MessageCollector');
        $property   = $reflection->getProperty('message');
        $property->setAccessible(true);

        $collector = new MessageCollector('test/src/Asset/code.php');

        // set an Message
        $property->setValue($collector, new Message());

        $node = $this->getMock('PhpParser\NodeAbstract');
        $collector->leaveNode($node);

        $this->assertNull($property->getValue($collector));
    }

    /**
     * When a Message is collected its origin should be set
     */
    public function testLeaveNodeWillSetOriginOnMessage()
    {
        $reflection = new \ReflectionClass('BsbDoctrineTranslator\PhpParser\NodeVisitor\MessageCollector');
        $property   = $reflection->getProperty('message');
        $property->setAccessible(true);

        $node      = $this->getMock('PhpParser\NodeAbstract');
        $collector = new MessageCollector('test/src/Asset/code.php');
        $message   = new Message();
        $property->setValue($collector, $message);

        $collector->leaveNode($node);

        $this->assertNotNull($message->getOrigin());
    }

    /**
     * When collectLocale has been set only messages of such locales may be collected
     */
    public function testLeaveNodeDoesNothingWhenNoMessageHasBeenSetByEnterNode()
    {
        $node      = $this->getMock('PhpParser\NodeAbstract');
        $collector = new MessageCollector('test/BsbDoctrineTranslatorTest/Asset/code.php');

        $collector->leaveNode($node);
    }

    /**
     * When collectLocale has been set only messages of such locales may be collected
     */
    public function testLeaveNodeCollectsOnlyMessagesFromCollectLocaleProperty()
    {
        $reflection = new \ReflectionClass('BsbDoctrineTranslator\PhpParser\NodeVisitor\MessageCollector');
        $property   = $reflection->getProperty('message');
        $property->setAccessible(true);

        $node      = $this->getMock('PhpParser\NodeAbstract');
        $collector = new MessageCollector('test/src/Asset/code.php');

        $collector->setCollectLocale(array('xx_XX'));
        $message = new Message();
        $message->setLocale('xx_XX');

        $property->setValue($collector, $message);

        $collector->leaveNode($node);

        $message = new Message();
        $message->setLocale('yy_YY');

        $property->setValue($collector, $message);
        $collector->leaveNode($node);

        $this->assertCount(1, $collector->getCollection());
    }

    /**
     * When collectTextDomain has been set only messages of such textDomains may be collected
     */
    public function testLeaveNodeCollectsOnlyMessagesFromCollectTextDomainProperty()
    {
        $reflection = new \ReflectionClass('BsbDoctrineTranslator\PhpParser\NodeVisitor\MessageCollector');
        $property   = $reflection->getProperty('message');
        $property->setAccessible(true);

        $node      = $this->getMock('PhpParser\NodeAbstract');
        $collector = new MessageCollector('test/src/Asset/code.php');

        $collector->setCollectTextDomain(array('foo'));
        $message = new Message();
        $message->setTextDomain('foo');

        $property->setValue($collector, $message);

        $collector->leaveNode($node);

        $message = new Message();
        $message->setTextDomain('bar');

        $property->setValue($collector, $message);
        $collector->leaveNode($node);

        $this->assertCount(1, $collector->getCollection());
    }

    public function dataProviderConvertFileCharToLineCharOffsetMethod()
    {
        return array(
            array(0, 0),
            array(233, 25),
            array(375, 52),
            array(494, 0),
            array(515, 14),
        );
    }

    /**
     * @dataProvider dataProviderConvertFileCharToLineCharOffsetMethod
     */
    public function testConvertFileCharToLineCharOffsetMethod($fileOffset, $expectedLineOffset)
    {
        $reflection = new \ReflectionClass('BsbDoctrineTranslator\PhpParser\NodeVisitor\MessageCollector');
        $method     = $reflection->getMethod('convertFileCharToLineCharOffset');
        $method->setAccessible(true);

        $collector = new MessageCollector('test/src/Asset/charOffset.php');

        $this->assertEquals($expectedLineOffset, $method->invoke($collector, $fileOffset));
    }

    public function testGetCollection()
    {
    }

    /**
     * Parses node argument
     */
    public function testParseArgumentWithNullWouldParseAsNullAndNull()
    {
        $reflection = new \ReflectionClass('BsbDoctrineTranslator\PhpParser\NodeVisitor\MessageCollector');
        $method     = $reflection->getMethod('parseArgument');
        $method->setAccessible(true);

        $collector = new MessageCollector('test/src/Asset/code.php');

        $this->assertEquals(array(null, null), $method->invoke($collector, null));
    }

    public function testParseArgumentWithStringNodeWouldParseAsStringAndNull()
    {
        $reflection = new \ReflectionClass('BsbDoctrineTranslator\PhpParser\NodeVisitor\MessageCollector');
        $method     = $reflection->getMethod('parseArgument');
        $method->setAccessible(true);

        $collector = new MessageCollector('test/src/Asset/code.php');

        $node       = $this->getMockBuilder('PhpParser\Node\Arg')->disableOriginalConstructor()->getMock();
        $stringNode = new Node\Scalar\String_('foo');
        $argNode    = new Node\Arg($stringNode);

        $this->assertEquals(array('foo', null), $method->invoke($collector, $argNode));
    }

    public function testParseArgumentWithNonStringNodeWouldParseAsFalseAndType()
    {
        $reflection = new \ReflectionClass('BsbDoctrineTranslator\PhpParser\NodeVisitor\MessageCollector');
        $method     = $reflection->getMethod('parseArgument');
        $method->setAccessible(true);

        $collector = new MessageCollector('test/src/Asset/code.php');

        $nonStringNode = $this->getMockBuilder('PhpParser\Node\Expr')->disableOriginalConstructor()->getMock();
        $nonStringNode->expects($this->once())->method('getType')->willReturn('foo');
        $argNode = new Node\Arg($nonStringNode);

        $this->assertEquals(array(false, 'foo'), $method->invoke($collector, $argNode));
    }
}
