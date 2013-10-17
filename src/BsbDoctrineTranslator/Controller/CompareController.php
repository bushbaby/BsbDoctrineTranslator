<?php

namespace BsbDoctrineTranslator\Controller;

use BsbDoctrineTranslator\Entity\Locale;
use BsbDoctrineTranslator\Manager\DoctrineTranslatorService;
use BsbDoctrineTranslator\Model\Filter;
use BsbDoctrineTranslator\Model\FilterConstant;
use BsbDoctrineTranslator\Entity\Message;
use BsbDoctrineTranslator\Model\Message as SourceMessage;
use BsbDoctrineTranslator\Scanner\SourceScanner;
use BsbDoctrineTranslator\View\SourceScannerModel;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Zend\Console\Prompt\Confirm;
use Zend\Console\Prompt\Number;
use Zend\Console\Prompt\Select;
use Zend\Console\Request;
use Zend\I18n\Translator\Translator;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ConsoleModel;

class CompareController extends AbstractActionController
{
    /**
     * @var $entityManager EntityManager
     */
    protected $entityManager;

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager()
    {
        if (!$this->entityManager) {
            $this->entityManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        }
        return $this->entityManager;
    }

    public function compareSourceToDatabaseAction()
    {
        /** @var DoctrineTranslatorService $manager */
        $manager = $this->getServiceLocator()->get('BsbDoctrineTranslator\Manager');

        /** @var $request Request */
        $request = $this->getRequest();

        $kind       = 0;
        $kind       += $request->getParam('kind') == 'singular' ? SourceMessage::KIND_SINGULAR : 0;
        $kind       += $request->getParam('kind') == 'plural' ? SourceMessage::KIND_PLURAL : 0;

        if ($kind == 0) {
            $kind = SourceMessage::KIND_SINGULAR + SourceMessage::KIND_PLURAL;
        }

        $locale          = $request->getParam('locale', null);
        if (!$locale) {
            return "Specify a locale...\n";
        }
        $domain          = $request->getParam('domain', FilterConstant::NONE);
        $isVerbose       = $request->getParam('verbose', false) || $request->getParam('v', false);

        /** @var SourceScanner $functionScanner */
        $functionScanner = $this->getServiceLocator()->get('BsbDoctrineTranslator\Scanner');

        $queryBuilder       = $this->getEntityManager()->createQueryBuilder();
        $query              = $queryBuilder->select('locale')
            ->from('BsbDoctrineTranslator\Entity\Locale', 'locale')
            ->where('locale.locale = :locale')
            ->setParameters(array(':locale' => $locale))
            ->getQuery();

        /** @var Locale $Locale */
        $Locale  = $query->getOneOrNullResult();

        if (!$Locale) {
            return sprintf("Locale '%s' is not defined. Use the command line tool to add this locale...\n", $locale);
        }

        // Scans the source for occurances of singular and/or plurals definitions
        $SourceMessages = $functionScanner->scan($kind);
        $SourceMessages = $functionScanner->compact($SourceMessages);

        /** @var $SourceMessage SourceMessage */
        $queryBuilder       = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder->select('message')
            ->from('BsbDoctrineTranslator\Entity\Message', 'message')
            //->join('message.locale', 'locale')
            ->where('message.locale = :locale AND message.plural_index IS NULL AND message.domain = :domain AND message.message = :message');

        $sourcesNotInDatabase = array();
        $moreThenOneEntities = array();


        foreach($SourceMessages as $SourceMessage) {
            if ($SourceMessage->getKey() == false) {
                continue;
            }
            if ($SourceMessage->getTextDomain() == false) {
                continue;
            }

            if ($SourceMessage->getKind() == SourceMessage::KIND_SINGULAR) {
                $query = $queryBuilder
                    ->setParameters(array(
                        ':locale' => $Locale->getId(),
                        ':domain' => $SourceMessage->getTextDomain() ?: 'default',
                        ':message' => $SourceMessage->getKey(),
                    ))
                    ->getQuery();

                /** @var Locale $Locale */
                $Message = $query->getResult();

                if (count($Message) == 0) { // compare origin
                    $sourcesNotInDatabase[] = $SourceMessage;
                } elseif (count($Message) > 1) {
                    $moreThenOneEntities[] = $Message;
                }
            }
        }

        if (count($moreThenOneEntities)) {
            $select = Select::prompt(sprintf("Detected %s duplicate entities(s) within the database. What do you want to do?\n",
                    count($moreThenOneEntities)),
                array('n'=>'Nothing', 'v'=>'View', 'm'=>'Merge into one (not yet implemented)', 'i'=>'Merge interactively (not yet implemented)'));

            switch($select) {
                case 'm':
                case 'i':
                case 'v':
                    $SourceMessages = array();
                    foreach($moreThenOneEntities as $Messages) {
                        /** @var $Message \BsbDoctrineTranslator\Entity\Message */
                        foreach ($Messages as $Message) {
                            $sourceMessage = new SourceMessage(SourceMessage::KIND_SINGULAR);
                            $sourceMessage->setKey($Message->getMessage());
                            $sourceMessage->setLocale($Message->getLocale()->getLocale());
                            $sourceMessage->setTextDomain($Message->getDomain());
                            $sourceMessage->setTranslation($Message->getTranslation());
                            $sourceMessage->addOrigin($Message->getOrigin());
                            $SourceMessages[] = $sourceMessage;
                        }
                    }

                    return new SourceScannerModel(array('verbose' => true, 'show-translation' => true, 'messages'=>$SourceMessages));

                    break;
                    ;
                case 'n':

            }
        }

        if (count($sourcesNotInDatabase)) {
            $select = Select::prompt(sprintf("Detected %s suitable message(s) within your source do not have a entry in the database. What do you want to do?\n",
                    count($sourcesNotInDatabase)),
                array('n'=>'Nothing', 'v'=>'View', 'a'=>'Add them all', 'i'=>'Add them interactively (not yet implemented)'));

            switch($select) {
                case 'v':
                    return new SourceScannerModel(array('verbose' => true, 'messages'=>$sourcesNotInDatabase));

                    break;
                case 'a':
                    array_map(function(SourceMessage $message) use ($Locale) {
                        $entity = new Message();
                        $entity->setLocale($Locale);
                        $entity->setMessage($message->getKey());
                        $entity->setDomain($message->getTextDomain());
                        $entity->setOrigin($message->getOrigin());
                        $entity->setPluralIndex(null);

                        $this->entityManager->persist($entity);
                    }, $sourcesNotInDatabase);

                    $this->entityManager->flush();

                    break;
                case 'i':
                    break;
                case 'n':

            }
        }
    }

    public function exportAction()
    {
        /** @var $request Request */
        $request = $this->getRequest();

        $filename       = $request->getParam('file', false);

        if (file_exists($filename)) {
            if (!Confirm::prompt('File exists! Continue? (y/n) ')) {
                return;
            }
        }

        $excel = new \PHPExcel();
        $sheet = $excel->getActiveSheet();
        $sheet->setTitle('messages');

        $messageRepo = $this->getEntityManager()->getRepository('BsbDoctrineTranslator\Entity\Message');
        $localeRepo = $this->getEntityManager()->getRepository('BsbDoctrineTranslator\Entity\Locale');

        $rows = array(array('id', 'locale_id', 'domain', 'message', 'translation', 'text_domain', 'origin'));

        /** @var $Entity \BsbDoctrineTranslator\Entity\Message */
        foreach($messageRepo->findBy(array(), array('message' => 'asc', 'locale' => 'asc', 'domain'=>'asc')) as $Entity) {
            $rows[] = array($Entity->getId(), $Entity->getLocale()->getLocale(), $Entity->getDomain(), $Entity->getMessage(), $Entity->getTranslation(), $Entity->getDomain(), implode("\n", $Entity->getOrigin()));
        }

        $sheet->fromArray($rows);

        $sheet->getColumnDimension('A')->setVisible(false);
        $sheet->getColumnDimension('B')->setWidth(12);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(50);
        $sheet->getColumnDimension('E')->setWidth(50);
        $sheet->getColumnDimension('F')->setVisible(false);
        $sheet->getColumnDimension('G')->setVisible(false);

        $sheet = $excel->createSheet();
        $sheet->setTitle('locales');

        $rows = array(array('id', 'locale', 'plural_forms'));

        /** @var $Entity \BsbDoctrineTranslator\Entity\Locale */
        foreach($localeRepo->findAll() as $Entity) {
            $rows[] = array($Entity->getId(), $Entity->getLocale(), $Entity->getPluralForms());
        }

        $sheet->fromArray($rows);

        $objPHPExcel = \PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
        $objPHPExcel->save($filename);
    }
    public function importAction()
    {
        /** @var $request Request */
        $request = $this->getRequest();

        $filename       = $request->getParam('file', false);

        $objPHPExcel = \PHPExcel_IOFactory::load($filename);

        $sheet = $objPHPExcel->getSheet(0);
        $data = $objPHPExcel->getActiveSheet()->toArray(null,true,true,false);

        $messageRepo = $this->getEntityManager()->getRepository('BsbDoctrineTranslator\Entity\Message');
        $localeRepo = $this->getEntityManager()->getRepository('BsbDoctrineTranslator\Entity\Locale');

        /** @var $Entity \BsbDoctrineTranslator\Entity\Message */
        foreach($messageRepo->findAll() as $Entity) {
            $this->getEntityManager()->remove($Entity);
        }
        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();

        foreach($data as $entry) {
            if ($entry[0] == 'id') {
                continue;
            }

            /** @var $Entity \BsbDoctrineTranslator\Entity\Message */
            $Entity = is_numeric($entry[0]) ? $messageRepo->find($entry[0]) : new Message();


            if ($Entity == null) {
                $Entity = new Message();
            }
            if ($Entity) {
                if (is_numeric($entry[1])) {
                    $Entity->setLocale($localeRepo->findOneBy(array('id'=>$entry[1])));
                } else {
                    $Entity->setLocale($localeRepo->findOneBy(array('locale'=>$entry[1])));
                }
                if (isset($entry[2])) {
                    $Entity->setDomain($entry[2]);
                }
                if (isset($entry[3])) {
                    $Entity->setMessage($entry[3]);
                }
                if (isset($entry[4]) && strlen($entry[4])) {
                    $Entity->setTranslation($entry[4]);
                }
                if (isset($entry[6]) && strlen($entry[6])) {
                    $Entity->setOrigin(explode("\n", $entry[6]) );
                } else {
                    $Entity->setOrigin(null);
                }
                $this->getEntityManager()->persist($Entity);
            }
        }

        $this->getEntityManager()->flush();
    }
}