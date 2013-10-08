<?php

namespace BsbDoctrineTranslator\PHPParser\NodeVisitor;

use BsbDoctrineTranslator\Model\Message;
use PHPParser_NodeVisitorAbstract;
use PHPParser_Node;
use PHPParser_Node_Expr_FuncCall;
use PHPParser_Node_Expr_MethodCall;
use PHPParser_Node_Scalar_String;
use PHPParser_Node_Arg;

class TranslateCollector extends PHPParser_NodeVisitorAbstract
{
    /**
     * @var null|PHPParser_Node_Name $collection
     */
    protected $collection = array();

    /**
     * @var Message The message this node creates - if any
     */
    protected $message;

    /**
     * The being of which the nodes we are visiting
     *
     * @var string $parsedFile;
     */
    protected $parsedFile;

    /**
     * @var array
     */
    protected $collectLocale = array();

    /**
     * @var array
     */
    protected $collectTextDomain = array();

    /**
     *
     */
    public function __construct($parsedFile) {
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
     * @return array
     */
    public function getCollection()
    {
        return $this->collection;
    }

    public function enterNode(PHPParser_Node $node) {
        if ($node instanceof PHPParser_Node_Expr_MethodCall || $node instanceof PHPParser_Node_Expr_FuncCall) {
            if ($node->name == 'translate') {
                $Message = new Message(Message::KIND_SINGULAR);

                $value = $this->parseArgument(isset($node->args[0]) ? $node->args[0] : null);
                $Message->setKey($value[0]);
                $Message->setKeyInfo($value[1]);

                $value = $this->parseArgument(isset($node->args[2]) ? $node->args[2] : null);
                $Message->setLocale($value[0]);
                $Message->setLocaleInfo($value[1]);

                $value = $this->parseArgument(isset($node->args[1]) ? $node->args[1] : null);
                $Message->setTextDomain($value[0]);
                $Message->setTextDomainInfo($value[1]);

                $this->message = $Message;
            }

            if ($node->name == 'translatePlural') {
                $Message = new Message(Message::KIND_PLURAL);

                $value = $this->parseArgument(isset($node->args[0]) ? $node->args[0] : null);
                $Message->setKey($value[0]);
                $Message->setKeyInfo($value[1]);

                $value = $this->parseArgument(isset($node->args[4]) ? $node->args[4] : null);
                $Message->setLocale($value[0]);
                $Message->setLocaleInfo($value[1]);

                $value = $this->parseArgument(isset($node->args[3]) ? $node->args[3] : null);
                $Message->setTextDomain($value[0]);
                $Message->setTextDomainInfo($value[1]);

                $this->message = $Message;
            }
        }
    }

    public function leaveNode(PHPParser_Node $node)
    {
        if (($message = $this->message) && $message instanceof Message) {
            $this->message = null;

            if ($this->collectLocale && !in_array($message->getLocale(), $this->collectLocale)) {
                return;
            }

            if ($this->collectTextDomain && !in_array($message->getTextDomain(), $this->collectTextDomain)) {
                return;
            }

            $message->addOrigin(sprintf("%s:%s", $this->parsedFile, $node->getLine()));

            $this->collection[] = $message;
        }
    }

    /**
     * @param $arg array PHPParser_Node_Expr
     * @return array
     */
    protected function parseArgument(PHPParser_Node_Arg $arg = null)
    {
        if ($arg == null) {
            $value = null;
            $info = null;
        } elseif ($arg->value instanceof PHPParser_Node_Scalar_String) {
            $value = (string) $arg->value->value;
            $info = null;
        } else {
            $value = false;
            $info = $arg->value->getType();
        }

        return array($value, $info);
    }
}