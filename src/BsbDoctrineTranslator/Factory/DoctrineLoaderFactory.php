<?php

namespace BsbDoctrineTranslator\Factory;

use BsbDoctrineTranslator\I18n\Translator\Loader\DoctrineTranslationLoader;
use BsbDoctrineTranslator\Listener\TranslatorListener;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class DoctrineLoaderFactory implements FactoryInterface
{
    /**
     * {@inheritdocs}
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /**
         * $serviceLocator is the translator plugin manager, to get into the
         * root service locator we need the getServiceLocator() call
         *
         * @see http://juriansluiman.nl/en/article/120
         */
        $sm = $serviceLocator->getServiceLocator();
        $em = $sm->get('Doctrine\ORM\EntityManager');

        $fs = $sm->get('BsbDoctrineTranslator\Scanner');

        $service = new DoctrineTranslationLoader($em);

        $translator = $sm->get('translator');
        $translator->getEventManager()->attachAggregate(new TranslatorListener($em, $fs));

        $translator->enableEventManager();

        return $service;
    }
}