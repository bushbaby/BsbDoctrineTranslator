<?php

namespace BsbDoctrineTranslator\PhpParser\Lexer;

use PhpParser;

class FileOffsetLexer extends PhpParser\Lexer
{

    /**
     * @var int Position of the cursor within the file
     */
    private $filePos;

    /**
     * @inheritdoc
     */
    public function startLexing($code)
    {
        parent::startLexing($code);

        $this->filePos = 0;
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
                $startAttributes['startPos'] = $this->filePos;
                $this->filePos += strlen($token);
                $endAttributes['endPos'] = $this->filePos;

                break;
            } else {
                $startAttributes['startPos'] = $this->filePos;
                $this->filePos += strlen($token[1]);

                if (T_COMMENT !== $token[0] && T_DOC_COMMENT !== $token[0] && !isset($this->dropTokens[$token[0]])) {
                    $endAttributes['endPos'] = $this->filePos;

                    break;
                }
            }
        }

        return $result;
    }
}
