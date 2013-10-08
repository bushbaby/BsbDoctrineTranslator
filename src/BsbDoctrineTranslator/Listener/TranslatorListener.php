<?php

namespace BsbDoctrineTranslator\Listener;

use BsbDoctrineTranslator\Entity\Message;
use BsbDoctrineTranslator\Scanner\SourceScanner;
use Doctrine\ORM\EntityManager;
use Zend\EventManager\Event;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\I18n\Translator\Translator;
use Zend\Mvc\MvcEvent;

class TranslatorListener implements ListenerAggregateInterface {

    use ListenerAggregateTrait;

    /**
     * @var EntityManager $entityManager
     */
    protected $entityManager;

    /**
     * @var SourceScanner
     */
    protected $functionScannerService;
    /**
     * @var array
     */
    protected $entries = array();

    public function __construct(EntityManager $entityManager, SourceScanner $functionScannerService)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Attach one or more listeners
     *
     * @return void
     */
    public function attach(EventManagerInterface $events)
    {
//        $this->listeners[] = $events->attach(Translator::EVENT_MISSING_TRANSLATION, array($this, 'translationMissing'));
//        $this->listeners[] = $events->getSharedManager()->attach('*', MvcEvent::EVENT_FINISH, array($this, 'finished'), 10000);
    }

    /**
     * Not yet implemented
     *
     * @param Event $event
     */
    public function translationMissing(Event $event)
    {
        return;

        $key    = $event->getParam('message');
        $locale = $event->getParam('locale');
        $domain = $event->getParam('text_domain');

        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

        $files = array();
        foreach($backtrace as $trace) {
            if (isset($trace['file'])) {
                $files[] = $trace['file'];
            }
        }

        $this->entries[] = $event->getParams();
    }

    /**
     * Not yet implemented
     *
     * @param MvcEvent $event
     */
    public function finished(MvcEvent $event)
    {
        if (!count($this->entries)) {
            return;
        }

        return;

        $repo1 = $this->entityManager->getRepository('BsbDoctrineTranslator\Entity\Locale');
        $repo2 = $this->entityManager->getRepository('BsbDoctrineTranslator\Entity\Message');

        $Locales = $repo1->findAll();

        foreach($this->entries as $message) {
            $Locale = $repo1->findOneBy(array('locale'=>$message['locale']));

            if (!$Locale) {
                continue;
            }

            foreach($Locales as $Locale) {
                $Message = $repo2->findOneBy(array('locale'=>$Locale, 'message'=>$message['message'], 'domain'=>$message['text_domain']));
                if (!$Message) {
                    $Message = new Message();
                    $Message->setLocale($Locale);
                    $Message->setDomain($message['text_domain']);
                    $Message->setMessage($message['message']);
                }

                if (isset($message['origin'])) {
                    $origins = $Message->getOrigin() ?: array();
                    $hashes = array();
                    foreach($origins as $value) {
                        $hashes[] = sha1(serialize($value));
                    }

                    if (!in_array(sha1(serialize($message['origin'])), $hashes)) {
                        $origins[] = $message['origin'];
                    }
                    $Message->setOrigin($origins);
                }

                $this->entityManager->persist($Message);
                $this->entityManager->flush();
            }
        }
    }
}