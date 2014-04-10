<?php

namespace PatrickLaxton\AnnuaireBundle\Service;

use PatrickLaxton\AnnuaireBundle\Entity\Personne;

/**
 * Translates a CSV file into Entities, then flushes them to database,
 * using a background task.
 * Should be split into 2 separate classes when another background task is needed.
 */
class Importer {

    /**
     * @var string
     */
    const UPLOAD_DIR = '/tmp';

    /**
     * @var Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Symfony\Bridge\Monolog\Logger
     */
    private $logger;

    /**
     * @param \Doctrine\ORM\EntityManager $em
     */
    public function __construct(\Doctrine\ORM\EntityManager $em, \Symfony\Bridge\Monolog\Logger $logger) {
        $this->em = $em;
        $this->logger = $logger;
    }

    /**
     * @return string
     */
    public function getFilename() {
        return time() . '.csv';
    }

    /**
     * Runs the background task
     * 
     * @param string $filename
     * @return boolean
     */
    public function import($filename) {
        $rootDirname = dirname(__DIR__ . '/../../../../');
        $cmd = "/usr/bin/php $rootDirname/app/console $filename";

        $output = array();
        $return_var = 0;

        //Non blocking process :
        /* //For Windows
         *   if (substr(php_uname(), 0, 7) == "Windows"){ 
          pclose(popen("start /B ". $cmd, "r"));
          }
          else { */ // For Linux/BSD :
        exec($cmd . " > /dev/null &", $output, $return_var);
        //}

        $this->logger->addDebug (
            "Command \"$cmd"
            . "\"\n ran at " . date('Y-m-d h:i:s') 
            . "\n, return code : $return_var"
            . "\n, output : " . var_export($output, 1) . '.'
        );

        return ( $return_var == 0 );
    }

    /**
     * Translates a CSV file into Entities, then flushes them to database.
     * 
     * @param string $filename
     * @return boolean
     */
    public function import_background($filename) {
        $start = ceil(microtime(true)*100)/100;
        
        $handle = fopen($filename, 'r');
        while ($line = fgetcsv($handle)) {
            $personne = new Personne();
            $personne->setNom($line[0])
                    ->setPrenom($line[1])
                    ->setTelephone($line[2]);
            $this->em->persist($personne);
            $this->em->flush();
        }
        fclose($handle);
        
        $end = ceil(microtime(true)*100)/100;
        
        $delta = $end - $start;
        
        return true;
    }

}
