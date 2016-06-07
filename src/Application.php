<?php

namespace Generator;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Application as SymfonyApp;

/**
 * An application to help generate entity and repository classes
 *
 * @author José Carlos <josecarlos@globtec.com.br>
 */
class Application
{
    /**
     * Runs the current application
     *
     * @param EntityManager $em            
     */
    public static function run(EntityManager $em)
    {
        $command = new Command();
        $command->setEntityManager($em);
        
        $app = new SymfonyApp('Generator to Doctrine', '1.0');
        $app->add($command);
        $app->run();
    }
}