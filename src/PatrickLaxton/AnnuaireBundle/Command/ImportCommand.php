<?php
namespace PatrickLaxton\AnnuaireBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use PatrickLaxton\AnnuaireBundle\Form\AnnuaireType;

/**
 * Runs the CSV file import service
 *
 * @author andy
 */
class ImportCommand extends ContainerAwareCommand {
    /**
     * @see Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand::configure()
     */
    protected function configure()
    {
        $this
            ->setName ( 'annuaire:import' )
            ->setDescription ( 'Imports a CSV file' )
            ->addArgument ( AnnuaireType::INPUT_NAME, InputArgument::REQUIRED, 'Which CSV file do you want to import? (full path please, like : /tmp/myFile.csv)')
        ;
    }
    
    /**
     * @return \PatrickLaxton\AnnuaireBundle\Service\Importer
     */
    private function getImporter() {
        return $this->getContainer()->get('patrick_laxton_annuaire.importer');
    }

    /**
     * @see Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand::execute()
     */
    protected function execute ( InputInterface $input, OutputInterface $output )
    {
        $filename = $input->getArgument ( AnnuaireType::INPUT_NAME );
        if ( !file_exists ( $filename ) ) {
            $text = 'The file ' . $filename . ' does not exist.';
        } else {
            if ( !$this->getImporter()->import_background ( $filename ) ) {
                $text = 'Error while processing file ' . $filename . '. Check logs for error messages.';
            } else {
                $text = 'Processed file ' . $filename . ' now.';
            }
        }

        $output->writeln($text);
    }
}
