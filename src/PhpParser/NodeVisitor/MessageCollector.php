<?php

namespace BsbDoctrineTranslator\PhpParser\NodeVisitor;

use BsbDoctrineTranslator\Model\Message;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class MessageCollector extends NodeVisitorAbstract
{
    /**
     * @return Message[]
     */
    private $collection = [];

    /**
     * @var Message The message this node creates - if any
     */
    private $message;

    /**
     * The being of which the nodes we are visiting
     *
     * @var string $parsedFile ;
     */
    private $parsedFile;

    /**
     * @var
     */
    private $parsedFileLineCount;

    /**
     * @var array
     */
    private $collectLocale = [];

    /**
     * @var array
     */
    private $collectTextDomain = [];

    /**
     *
     */
    public function __construct($parsedFile)
    {
        $this->parsedFile = $parsedFile;
    }

    /**
     * @param array $collectLocale
     */
    public function setCollectLocale($collectLocale)
    {
        $this->collectLocale = $collectLocale;
    }

    /**
     * @param array $collectTextDomain
     */
    public function setCollectTextDomain($collectTextDomain)
    {
        $this->collectTextDomain = $collectTextDomain;
    }

    /**
     * @return Message[]
     */
    public function getCollection()
    {
        return $this->collection;
    }

    public function enterNode(Node $node)
    {
        if ($node instanceof Node\Expr\MethodCall || $node instanceof Node\Expr\FuncCall) {
            if ($node->name == 'translate') {
                $Message = new Message(Message::KIND_SINGULAR);

                $value = $this->parseArgument(isset($node->args[0]) ? $node->args[0] : null);
                $Message->setKey($value[0]);
                $Message->setKeyInfo($value[1]);

                $value = $this->parseArgument(isset($node->args[1]) ? $node->args[1] : null);
                $Message->setTextDomain($value[0]);
                $Message->setTextDomainInfo($value[1]);

                $value = $this->parseArgument(isset($node->args[2]) ? $node->args[2] : null);
                $Message->setLocale($value[0]);
                $Message->setLocaleInfo($value[1]);

                $this->message = $Message;
            }

            if ($node->name == 'translatePlural') {
                $Message = new Message(Message::KIND_PLURAL);

                $value = $this->parseArgument(isset($node->args[0]) ? $node->args[0] : null);
                $Message->setKey($value[0]);
                $Message->setKeyInfo($value[1]);

                $value = $this->parseArgument(isset($node->args[3]) ? $node->args[3] : null);
                $Message->setTextDomain($value[0]);
                $Message->setTextDomainInfo($value[1]);

                $value = $this->parseArgument(isset($node->args[4]) ? $node->args[4] : null);
                $Message->setLocale($value[0]);
                $Message->setLocaleInfo($value[1]);

                $this->message = $Message;
            }
        }
    }

    /**
     * @param Node $node
     */
    public function leaveNode(Node $node)
    {
        if (!$this->message instanceof Message) {
            return;
        }

        $message       = $this->message;
        $this->message = null;

        if ($this->collectLocale && !in_array($message->getLocale(), $this->collectLocale)) {
            return;
        }

        if ($this->collectTextDomain && !in_array($message->getTextDomain(), $this->collectTextDomain)) {
            return;
        }

        $message->addOrigin(sprintf(
            "%s:%s:%s",
            $this->parsedFile,
            $node->getLine(),
            $this->convertFileCharToLineCharOffset($node->getAttribute('startPos'))
        ));

        $this->collection[] = $message;
    }

    /**
     * Finds the argument value and type of the called method/function
     *
     * @param Node\Arg $arg
     * @return array
     */
    private function parseArgument(Node\Arg $arg = null)
    {
        if ($arg == null) {
            $value = null;
            $info  = null;
        } elseif ($arg->value instanceof Node\Scalar\String_) {
            $value = (string) $arg->value->value;
            $info  = null;
        } else {
            $value = false;
            $info  = $arg->value->getType();
        }

        return [$value, $info];
    }

    /**
     * Utility method to convert the *file* based character offset to *line* based offset
     *
     * @param int $offset
     * @return int mixed
     */
    private function convertFileCharToLineCharOffset($offset)
    {
        if (!$this->parsedFileLineCount) {
            $this->parsedFileLineCount = array_map('strlen', file($this->parsedFile));
        }

        $lines = $this->parsedFileLineCount;

        while (($lineLen = array_shift($lines)) && ($offset - $lineLen) >= 0) {
            $offset -= $lineLen;
        }

        return $offset;
    }
}
