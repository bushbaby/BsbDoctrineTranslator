<?php

namespace BsbDoctrineTranslator\I18n\Translator\Loader;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Expr\Join;
use Zend\I18n\Translator\Loader\RemoteLoaderInterface;
use Zend\I18n\Translator\Plural\Rule as PluralRule;
use Zend\I18n\Translator\TextDomain;

class DoctrineTranslationLoader implements RemoteLoaderInterface
{

    /**
     * @var EntityManager $entityManager
     */
    protected $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function load($locale, $domain)
    {
        $textDomain         = new TextDomain();
        $queryBuilder       = $this->entityManager->createQueryBuilder();
        $query              = $queryBuilder->select('locale.id, locale.plural_forms')
                                           ->from('BsbDoctrineTranslator\Entity\Locale', 'locale')
                                           ->where('locale.locale = :locale')
                                           ->setParameters(array(':locale' => $locale))
                                           ->getQuery();
        $localeInformation  = $query->getOneOrNullResult(AbstractQuery::HYDRATE_ARRAY);

        if (!count($localeInformation)) {
            return $textDomain;
        }

        if(strlen($localeInformation['plural_forms'])) {
            $textDomain->setPluralRule(
                PluralRule::fromString($localeInformation['plural_forms'])
            );
        }

        $query              = $queryBuilder->select('message.message, message.translation, message.plural_index')
                                           ->from('BsbDoctrineTranslator\Entity\Message', 'message')
                                           ->where('message.domain = :domain AND message.locale = :locale_id')
                                           ->getQuery();
        $messages           = $query->execute(array(':locale_id' => $localeInformation['id'],
                                          ':domain' => $domain), AbstractQuery::HYDRATE_ARRAY);

        foreach ($messages as $message) {
            if (is_null($message['plural_index'])) {
                $textDomain[$message['message']] = $message['translation'];
            } else {
                if (isset($textDomain[$message['message']])) {
                    if (!is_array($textDomain[$message['message']])) {
                        $textDomain[$message['message']] = array(
                            $message['plural_index'] => $textDomain[$message['message']]
                        );
                    }
                    $textDomain[$message['message']][$message['plural_index']] = $message['translation'];
                } else {
                    $textDomain[$message['message']] = $message['translation'];
                }
            }
        }

        return $textDomain;
    }
}