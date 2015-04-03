<?php

namespace BsbDoctrineTranslator\Manager;

use BsbDoctrineTranslationLoader\I18n\Translator\Loader\DoctrineLoader;
use BsbDoctrineTranslator\Exception\NotManagedException;
use Doctrine\ORM\EntityManager;
use Zend\Stdlib\AbstractOptions;

class DoctrineTranslator extends AbstractOptions
{

    /**
     * @var $entityManager EntityManager
     */
    protected $entityManager;

    /**
     * @var $loader DoctrineLoader
     */
    protected $loader;
    /**
     * Text domain managed
     *
     * @var array
     */
    protected $text_domains;

    /**
     * Source paths managed
     *
     * @var array
     */
    protected $source_paths;

    /**
     * Source extensions managed
     *
     * @var array
     */
    protected $source_file_extensions;

    public function __construct(DoctrineLoader $loader, EntityManager $entityManager, $options = array())
    {
        $this->loader        = $loader;
        $this->entityManager = $entityManager;

        parent::__construct($options);
    }

    /**
     * @param array $source_file_extensions
     */
    public function setSourceFileExtensions($source_file_extensions)
    {
        $this->source_file_extensions = $source_file_extensions;
    }

    /**
     * @return array
     */
    public function getSourceFileExtensions()
    {
        return $this->source_file_extensions;
    }

    /**
     * @param array $source_paths
     */
    public function setSourcePaths($source_paths)
    {
        $this->source_paths = $source_paths;
    }

    /**
     * @return array
     */
    public function getSourcePaths()
    {
        return $this->source_paths;
    }

    /**
     * @param array $text_domains
     */
    public function setTextDomains($text_domains)
    {
        $this->text_domains = $text_domains;
    }

    /**
     * @return array
     */
    public function getTextDomains()
    {
        return $this->text_domains;
    }

    protected $messages = array();

    /**
     * Is the message defined in plural form?
     *
     * @param $domain
     * @param $locale
     * @param $message
     * @return bool|null when no message is defined
     */
    public function isPlural($domain, $locale, $message)
    {
        if (!isset($this->messages[$domain][$locale])) {
            $this->load($domain, $locale);
        }

        if (!$this->messageDefined($domain, $locale, $message)) {
            throw new NotManagedException(sprintf("The message '%s' is not managed by %s", $message, get_class($this)));
        }

        return is_array($this->messages[$domain][$locale][$message]);
    }

    public function messageDefined($domain, $locale, $message)
    {
        if (!isset($this->messages[$domain][$locale])) {
            $this->load($domain, $locale);
        }

        return isset($this->messages[$domain][$locale][$message]);
    }

    public function getUnmanagedMessages()
    {
        // scan source, see if they are in db
    }

    public function getUntranslatedMessages()
    {
        // scan source, see if they are in db and are translated
    }

    /**
     * Uses the translation loader to load the messages for one or all managed text domains in
     * the default or specified locale.
     *
     * @param null $domain
     * @param null $locale
     * @throws \BsbDoctrineTranslator\Exception\NotManagedException
     * @return void
     */
    protected function load($domain = null, $locale = null)
    {
        if ($domain == null) {
            foreach ($this->getTextDomains() as $domain) {
                $this->load($domain, $locale);
            }

            return;
        }

        if (!in_array($domain, $this->getTextDomains())) {
            throw new NotManagedException(sprintf("The domain '%s' is not managed by %s", $domain, get_class($this)));
        }

        if ($locale == null) {
            $locale = \Locale::getDefault();
        }

        if (!isset($this->messages[$domain][$locale])) {
            $this->loadTextDomain($domain, $locale);
        }
    }

    protected function loadTextDomain($domain, $locale)
    {
        $textDomain = $this->loader->load($locale, $domain);

        $this->messages[$domain][$locale] = $textDomain;
    }
}
