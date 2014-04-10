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
     * File in which progress is logged
     * 
     * @var string
     */
    const PROGRESS_FILENAME = '/tmp/progress';
    /**
     * File in which reports are logged
     * 
     * @var string
     */
    const REPORT_FILENAME = '/tmp/report';

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
        $rootDirname = realpath ( dirname(__DIR__ . '/../../../../../') );
        $cmd = "/usr/bin/php $rootDirname/app/console annuaire:import $filename";

        $output = array();
        $return_var = 0;

        //Non blocking process :
        /* //For Windows
         *   if (substr(php_uname(), 0, 7) == "Windows"){ 
          pclose(popen("start /B ". $cmd, "r"));
          }
          else { */ // For Linux/BSD :
        $return = exec($cmd . " > /dev/null &", $output, $return_var);
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
        /**
         * @var float start time
         */
        $start = microtime(true);
        
        /**
         * @var int percentage
         */
        $progress = 0;
        file_put_contents ( self::PROGRESS_FILENAME, $progress );
        
        $linesCmd = "wc -l $filename";
        $output = array();
        exec ( $linesCmd, $output );
        $output = explode ( ' ', $output[0] );
        /**
         * @var int number of lines in the processed file
         */
        $lines = intval ( $output[0] );
        
        $handle = fopen ( $filename, 'r' );
        $line_number = 0;
        while ( $line = fgetcsv ( $handle ) ) {
            $personne = new Personne();
            $personne->setNom ( $line[0] )
                    ->setPrenom ( $line[1] )
                    ->setTelephone ( $line[2] );
            $this->em->persist ( $personne );
            $this->em->flush();
            $current_progress = floor ( ( $line_number / $lines ) * 100 );
            if ( $current_progress > $progress ) {
                $progress = $current_progress;
                file_put_contents ( self::PROGRESS_FILENAME, $progress );
            }
            $line_number++;
        }
        fclose($handle);
        
        $progress = 100;
        file_put_contents ( self::PROGRESS_FILENAME, $progress );
        
        /**
         * @var float end time
         */
        $end = microtime(true);
        
        /**
         * @var float total time spent
         */
        $delta = sprintf ( '%0.2f', $end - $start );
        
        $report = <<<REPORT
File $filename processed succesfully.
$lines records created in $delta seconds.
REPORT;
        
        file_put_contents ( self::REPORT_FILENAME, $report );
        
        return true;
    }
    
    /**
     * Returns the progress
     * 
     * @return string
     */
    public function getProgress() {
        return file_get_contents ( self::PROGRESS_FILENAME );
    }

    /**
     * Returns the report written in the end of the CSV file provessing
     * 
     * @return string
     */
    public function getReport() {
        return file_get_contents ( self::REPORT_FILENAME );
    }
}
