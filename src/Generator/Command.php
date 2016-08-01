<?php

namespace Generator;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Command\Command as SymfonyCmd;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class of the command line interface to generator package
 *
 * @author José Carlos <josecarlos@globtec.com.br>
 */
class Command extends SymfonyCmd
{
    /**
     * Instance of the EntityManager
     * 
     * @var EntityManager
     */
    private $em;

    /**
     * Set em
     *
     * @param EntityManager $em            
     * @return Command
     */
    public function setEntityManager(EntityManager $em)
    {
        $this->em = $em;
        
        return $this;
    }

    /**
     * Get em
     *
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->em;
    }

    /**
     * Configures the current command
     */
    protected function configure()
    {
        $this
            ->setName('doctrine:generate')
            ->setDescription('Generate entity and repository classes')
            ->addArgument(
                'filter', 
                InputArgument::IS_ARRAY, 
                'Table name selected to generate the files <comment>(separate multiple names with a space)</comment>'
            )
            ->addOption(
                'namespace-entity', 
                null, 
                InputOption::VALUE_OPTIONAL, 
                'The namespace to entity classes', 
                'Models\Entities'
            )
            ->addOption(
                'namespace-repository', 
                null, 
                InputOption::VALUE_OPTIONAL, 
                'The namespace to repository classes', 
                'Models\Repositories'
            )
            ->addOption(
                'superclass', 
                null, 
                InputOption::VALUE_OPTIONAL, 
                'The superclass name to entity classes'
            )
            ->addOption(
                'sequence-prefix', 
                null, 
                InputOption::VALUE_OPTIONAL, 
                'The sequence prefix, the sequence name follows the pattern: <comment>SEQUENCEPREFIX_TABLENAME</comment>'
            )
            ->addOption(
                'directory', 
                null, 
                InputOption::VALUE_OPTIONAL, 
                'The path to generate your entity and repository classes.', 
                'generated'
            );
    }

    /**
     * Execute the command
     *
     * @param InputInterface $input            
     * @param OutputInterface $output            
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $generator = new Generator($this->getEntityManager());
        
        $generator
            ->setNsEntity($input->getOption('namespace-entity'))
            ->setSuperclass($input->getOption('superclass'))
            ->setSequencePrefix($input->getOption('sequence-prefix'))
            ->setNsRepository($input->getOption('namespace-repository'))
            ->setFilter($input->getArgument('filter'));
        
        $message = $generator->generate($input->getOption('directory'));
        
        $output->writeln($message);
    }
}
