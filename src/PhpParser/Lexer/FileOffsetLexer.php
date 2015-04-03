<?php

namespace BsbDoctrineTranslator\PhpParser\Lexer;

use PhpParser;

class FileOffsetLexer extends PhpParser\Lexer
{

    /**
     * @var int Position of the cursor within the file
     */
    private $fileOffsetPos;

    /**
     * @inheritdoc
     */
    public function startLexing($code)
    {
        $this->fileOffsetPos = 0;

        parent::startLexing($code);
    }

    /**
     * @inheritdoc
     *
     * Adds startPos and endPos of the token as attributes
     */
    public function getNextToken(&$value = null, &$startAttributes = null, &$endAttributes = null)
    {
        $pos    = $this->pos;
        $result = parent::getNextToken($value, $startAttributes, $endAttributes);

        while (isset($this->tokens[++$pos])) {
            $token = $this->tokens[$pos];

            if (is_string($token)) {
                $startAttributes['startPos'] = $this->fileOffsetPos;
                $this->fileOffsetPos += strlen($token);
                $endAttributes['endPos'] = $this->fileOffsetPos;

                break;
            } else {
                $startAttributes['startPos'] = $this->fileOffsetPos;
                $this->fileOffsetPos += strlen($token[1]);

                if (T_COMMENT !== $token[0] && T_DOC_COMMENT !== $token[0] && !isset($this->dropTokens[$token[0]])) {
                    $endAttributes['endPos'] = $this->fileOffsetPos;

                    break;
                }
            }
        }

        return $result;
    }
}
