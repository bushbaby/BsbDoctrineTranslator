<?php

namespace BsbDoctrineTranslator\Model;

class Message
{

    const KIND_SINGULAR = 1;
    const KIND_PLURAL   = 2;

    /**
     * @var int $kind
     */
    private $kind;

    /**
     * @var false|null|string $key
     */
    private $key;

    /**
     * @var string|null
     */
    private $keyInfo;

    /**
     * @var false|null|string $locale
     */
    private $locale;

    /**
     * @var string|null $translation
     */
    private $translation;

    /**
     * @var string|null
     */
    private $localeInfo;

    /**
     * @var false|null|string $textDomain
     */
    private $textDomain;

    /**
     * @var string|null
     */
    private $textDomainInfo;

    /**
     * @var array $origin
     */
    private $origin = array();

    public function __construct($kind = null)
    {
        $this->kind = $kind;
    }

    /**
     * @return int
     */
    public function getKind()
    {
        return $this->kind;
    }

    /**
     * @param false|null|string $locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * @param false|null|string $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * @return false|null|string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param null|string $keyInfo
     */
    public function setKeyInfo($keyInfo)
    {
        $this->keyInfo = $keyInfo;
    }

    /**
     * @return null|string
     */
    public function getKeyInfo()
    {
        return $this->keyInfo;
    }

    /**
     * @param int $kind
     */
    public function setKind($kind)
    {
        $this->kind = $kind;
    }

    /**
     * @return false|null|string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param null|string $localeInfo
     */
    public function setLocaleInfo($localeInfo)
    {
        $this->localeInfo = $localeInfo;
    }

    /**
     * @return null|string
     */
    public function getLocaleInfo()
    {
        return $this->localeInfo;
    }

    /**
     * @param false|null|string $textDomain
     */
    public function setTextDomain($textDomain)
    {
        $this->textDomain = $textDomain;
    }

    /**
     * @return false|null|string
     */
    public function getTextDomain()
    {
        return $this->textDomain;
    }

    /**
     * @param null|string $textDomainInfo
     */
    public function setTextDomainInfo($textDomainInfo)
    {
        $this->textDomainInfo = $textDomainInfo;
    }

    /**
     * @return null|string
     */
    public function getTextDomainInfo()
    {
        return $this->textDomainInfo;
    }

    /**
     * @param string|array $origin
     */
    public function setOrigin($origin)
    {
        $this->origin = is_array($origin) ? $origin : array($origin);
    }

    /**
     * @param string|array $origin
     */
    public function addOrigin($origin)
    {
        if (is_array($origin)) {
            foreach ($origin as $_origin) {
                $this->addOrigin($_origin);
            }

            return;
        }

        if (in_array($origin, $this->origin)) {
            return;
        }

        $this->origin[] = $origin;
    }

    /**
     * @return array
     */
    public function getOrigin()
    {
        if (count($this->origin) == 1) {
            return $this->origin[0];
        } elseif (count($this->origin) == 0) {
            return null;
        } else {
            return $this->origin;
        }
    }

    /**
     * @param null|string $translation
     */
    public function setTranslation($translation)
    {
        $this->translation = $translation;
    }

    /**
     * @return null|string
     */
    public function getTranslation()
    {
        return $this->translation;
    }
}
