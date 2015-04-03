<?php

namespace BsbDoctrineTranslator\Entity;

use BsbDoctrineTranslationLoader\Entity\Message as ExtendedMessage;

class Message extends ExtendedMessage
{
    /**
     * @var array $origin
     */
    protected $origin;

    /**
     * @param array $origin
     */
    public function setOrigin($origin)
    {
        $this->origin = is_null($origin) ? null : (array) $origin;
    }

    /**
     * @return array
     */
    public function getOrigin()
    {
        return is_null($this->origin) ? array() : $this->origin;
    }
}
