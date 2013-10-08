<?php

namespace BsbDoctrineTranslator\Scanner;

use BsbDoctrineTranslator\Exception\IOException;
use BsbDoctrineTranslator\Model\FilterConstant;
use BsbDoctrineTranslator\Model\Message;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use SplFileInfol;
use BsbDoctrineTranslator\PHPParser\NodeVisitor\TranslateCollector;
use Zend\Stdlib\ArrayUtils;
use PHPParser_Parser;
use PHPParser_NodeTraverser;
use PHPParser_Lexer;
use PHPParser_Error;

class SourceScanner
{
    /**
     * Results will come from these paths
     *
     * @var string|array|null
     */

    protected $paths;

    /**
     * Results will come only from files with these extensions
     *
     * @var array|null
     */

    protected $extensions;

    /**
     * Only results that match these text domains are returned
     *
     * If null, this is ignored
     *
     * @var array|null
     */
    protected $domains;

    /**
     * @var array|null
     */
    protected $errors;

    const ERROR_NONE = 0;
    const ERROR_PARSE_ERROR = 1;
    const ERROR_KEY_AMBIVALENT_TYPE = 2;

    public function __construct($config) {
        if (isset($config['paths'])) {
            $this->setPaths($config['paths']);
        }

        if (isset($config['extensions'])) {
            $this->setExtensions($config['extensions']);
        }

        if (isset($config['domains'])) {
            $this->setDomains($config['domains']);
        }
    }

    /**
     * @param array|null|string $paths
     */
    public function setPaths($paths)
    {
        $this->paths = $paths;
    }

    /**
     * @return array|null|string
     */
    public function getPaths()
    {
        return $this->paths;
    }

    /**
     * @param array|null $extensions
     */
    public function setExtensions($extensions)
    {
        $this->extensions = $extensions;
    }

    /**
     * @return array|null
     */
    public function getExtensions()
    {
        return $this->extensions;
    }

    /**
     * @param array|null $domains
     */
    public function setDomains($domains)
    {
        $this->domains = $domains;
    }

    /**
     * @return array|null
     */
    public function getDomains()
    {
        return $this->domains;
    }

    /**
     * @return array|null
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @param int $kind
     * @param null $locale
     * @param null $domain
     * @return array
     */
    public function scan($kind = FilterConstant::TYPE_NONE, $locale=FilterConstant::NONE, $domain=FilterConstant::NONE) {
        $messages = array();

        foreach($this->getFileCandidates() as $file) {
            $messages = ArrayUtils::merge($messages, $this->scanFile($file, $kind, $locale, $domain));
        }

        return $messages;
    }

    /**
     * @param $file
     * @param int $kind
     * @param null $locale
     * @param null $domain
     * @return array
     */
    public function scanFile($file, $kind = FilterConstant::TYPE_NONE, $locale=FilterConstant::NONE, $domain=FilterConstant::NONE)
    {
        return $this->parse($file, $kind, $locale, $domain);
    }

    public function compact($messages, $filter = FilterConstant::VARIABLE_NONE)
    {
        $result = array();

        /** @var Message $message */
        foreach($messages as $message ) {
            if ($message->getKey() === false && ($filter & FilterConstant::VARIABLE_KEY)) {
                continue;
            }
            if ($message->getTextDomain() === false && ($filter & FilterConstant::VARIABLE_DOMAIN)) {
                continue;
            }
            if ($message->getLocale() === false && ($filter & FilterConstant::VARIABLE_LOCALE)) {
                continue;
            }

            $hash = md5(
                $message->getKey().serialize($message->getKeyInfo()).
                $message->getTextDomain().serialize($message->getTextDomainInfo()).
                $message->getLocale().serialize($message->getLocaleInfo())
            );
            if (!isset($result[$hash])) {
                $result[$hash] = $message;
            } else {
                $result[$hash]->addOrigin($message->getOrigin());
            }
        }

        return $result;
    }

    /**
     * @param $file
     * @param $kind
     * @param $locale
     * @param $domain
     * @return array
     * @throws \BsbDoctrineTranslator\Exception\IOException
     */
    protected function parse($file, $kind = FilterConstant::TYPE_NONE, $locale=FilterConstant::NONE, $domain=FilterConstant::NONE)
    {
        if (!is_file($file)) {
            throw new IOException(sprintf('The file "%s" does not exist.', $file));
        }

        if (!is_readable($file)) {
            throw new IOException(sprintf('The file "%s" is not readable.', $file));
        }


        $parser        = new PHPParser_Parser(new PHPParser_Lexer());
        $traverser     = new PHPParser_NodeTraverser();
        $collector     = new TranslateCollector($file);

        if ($locale != FilterConstant::NONE) {
            $collector->setCollectLocale(array($locale));
        }

        if ($domain != FilterConstant::NONE) {
            $collector->setCollectTextDomain(array($domain));
        }

        $traverser->addVisitor($collector);

        //$old_error_reporting = error_reporting(0);

        try {
            $stmts = $parser->parse(file_get_contents($file));

            // traverse
            $traverser->traverse($stmts);

            return $collector->getCollection();
        } catch (PHPParser_Error $e) {
            echo sprintf("Parse Error: %s of %s\n", $e->getMessage(), $file);
        }

        //error_reporting($old_error_reporting);

        return $collector->getCollection();
    }

    protected function getFileCandidates() {
        $candidates = array();

        foreach($this->getPaths() as $path) {
            if (!is_dir($path)) {
                continue;
            }

            $itr = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path,
                RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::LEAVES_ONLY);

            /** @var $file SplFileInfo */
            foreach($itr as $file) {
                if (!in_array($file->getExtension(), $this->getExtensions())) {
                    continue;
                }

                $candidates[] = $file->getPathname();
            }

        }

        return array_unique($candidates);
    }

    /**
     * Extracts arguments from token list starting from a specific index
     * @param $tokens
     * @param $currentIndex
     * @param $expected
     * @return array
     */
    protected function extractArgumentsFromTokens($tokens, $currentIndex, $expected)
    {
        $args = array();

        // scan for arguments by looking ahead until token is ')'
        $ahead = $currentIndex + 1;
        while (isset($tokens[$ahead]) && $tokens[$ahead] != ')') {
            if (is_numeric($tokens[$ahead][0])) {
               // $tokens[$ahead][0] = token_name($tokens[$ahead][0]);
            }
            // print_r($tokens[$ahead]);

            // captures "foo" and 'bar' arguments
            if (is_array($tokens[$ahead]) && $tokens[$ahead][0] == T_CONSTANT_ENCAPSED_STRING) {
                $args[] = str_replace(array('\"', "\'"), array('"', "'"), substr($tokens[$ahead][1], 1, -1));
            }
            // captures null arguments
            if (is_array($tokens[$ahead]) && $tokens[$ahead][0] == T_STRING) {
                $args[] = null;
            }
            // captures $variable arguments
            if (is_array($tokens[$ahead]) && $tokens[$ahead][0] == T_VARIABLE) {
                $args[] = false;
            }

            $ahead++;
        }

        // make sure lenght is as $expected
        while(count($args) < $expected) {
            $args[] = null;
        }

        return $args;
    }
}