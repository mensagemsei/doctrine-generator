<?php

namespace Generator;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\DatabaseDriver;
use Doctrine\ORM\Tools\DisconnectedClassMetadataFactory;
use Doctrine\ORM\Tools\Console\MetadataFilter;
use Doctrine\ORM\Tools\EntityGenerator;
use Doctrine\ORM\Tools\EntityRepositoryGenerator;

/**
 * Class to generate entity and repository classes of the Doctrine
 *
 * @author José Carlos <josecarlos@globtec.com.br>
 */
class Generator
{
    /**
     * Files path
     *
     * @var string
     */
    private $directory;

    /**
     * The namespace to the generated entities
     *
     * @var string
     */
    private $nsEntity;

    /**
     * The namespace to the generated repositories
     *
     * @var string
     */
    private $nsRepository;

    /**
     * The prefix to the sequences, the default name is SEQUENCEPREFIX_TABLENAME
     *
     * @var string
     */
    private $sequencePrefix;

    /**
     * Superclass name to the entities
     *
     * @var string
     */
    private $superclass;

    /**
     * Array of the table name to filter
     *
     * @var array
     */
    private $filter;

    /**
     * Instance of the EntityManager
     * 
     * @var EntityManager
     */
    private $em;

    /**
     * @param EntityManager
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Set directory
     *
     * @param string
     * @return Generator
     */
    protected function setDirectory($directory)
    {
        $directory = rtrim($directory, '/\\');
        
        if (! is_dir($directory) && ! mkdir($directory, 0755)) {
            throw new \Exception("Cannot create directory '{$directory}': Permission denied");
        }
        
        $this->directory = $directory;
        
        return $this;
    }

    /**
     * Get directory
     *
     * @return string
     */
    public function getDirectory()
    {
        return $this->directory;
    }

    /**
     * Set nsEntity
     *
     * @param string $ns            
     * @return Generator
     */
    public function setNsEntity($ns)
    {
        $this->nsEntity = rtrim($ns, '\\') . '\\';
        
        return $this;
    }

    /**
     * Get nsEntity
     *
     * @return string
     */
    public function getNsEntity()
    {
        return $this->nsEntity;
    }

    /**
     * Set nsRepository
     *
     * @param string $ns            
     * @return Generator
     */
    public function setNsRepository($ns)
    {
        $this->nsRepository = rtrim($ns, '\\') . '\\';
        
        return $this;
    }

    /**
     * Get nsRepository
     *
     * @return string
     */
    public function getNsRepository()
    {
        return $this->nsRepository;
    }

    /**
     * Set sequencePrefix
     *
     * @param string $prefix            
     * @return Generator
     */
    public function setSequencePrefix($prefix)
    {
        $this->sequencePrefix = $prefix;
        
        return $this;
    }

    /**
     * Get sequencePrefix
     *
     * @return string
     */
    public function getSequencePrefix()
    {
        return $this->sequencePrefix;
    }

    /**
     * Set superclass
     *
     * @param string $superclass            
     * @return Generator
     */
    public function setSuperclass($superclass)
    {
        $this->superclass = $superclass;
        
        return $this;
    }

    /**
     * Get superclass
     *
     * @return string
     */
    public function getSuperclass()
    {
        return $this->superclass;
    }

    /**
     * Set filter
     *
     * @param array $filter            
     * @return Generator
     */
    public function setFilter(array $filter)
    {
        $this->filter = array_map(function ($item) {
            return implode(array_map('ucfirst', explode('_', $item)));
        }, $filter);
        
        return $this;
    }

    /**
     * Get filter
     *
     * @return string
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * Get metadata
     * 
     * @return array
     */
    protected function getMetadata()
    {
        $sm = $this->em->getConnection()->getSchemaManager();
        
        $driver = new DatabaseDriver($sm);
        $driver->setNamespace($this->getNsEntity());
        
        $this->em->getConfiguration()->setMetadataDriverImpl($driver);
        
        $cmf = new DisconnectedClassMetadataFactory();
        $cmf->setEntityManager($this->em);
        
        $metadata = $cmf->getAllMetadata();
        
        if (count($this->getFilter()) > 0) {
            $metadata = MetadataFilter::filter($metadata, $this->getFilter());
        }
        
        return $metadata;
    }

    /**
     * Generate entity classes
     *
     * @param array $metadata            
     */
    protected function generateEntities(array $metadata)
    {
        $generator = new EntityGenerator();
        
        $generator->setGenerateAnnotations(true);
        $generator->setGenerateStubMethods(true);
        $generator->setRegenerateEntityIfExists(false);
        $generator->setUpdateEntityIfExists(true);
        $generator->setBackupExisting(false);
        
        if (null !== ($superclass = $this->getSuperclass())) {
            $generator->setClassToExtend($superclass);
        }
        
        /* @var $classMetadata \Doctrine\ORM\Mapping\ClassMetadata */
        foreach ($metadata as $classMetadata) {
            $className = end((explode('\\', $classMetadata->getName())));
            
            $classMetadata->setCustomRepositoryClass($this->getNsRepository() . $className);
            
            if (null !== ($prefix = $this->getSequencePrefix())) {
                $classMetadata->setSequenceGeneratorDefinition(array(
                    'sequenceName' => strtoupper($prefix . '_' . $className),
                ));
            }
        }
        
        $generator->generate($metadata, $this->getDirectory());
    }

    /**
     * Generate repository classes
     *
     * @param array $metadata            
     */
    protected function generateRepositories(array $metadata)
    {
        $generator = new EntityRepositoryGenerator();
        
        /* @var $classMetadata \Doctrine\ORM\Mapping\ClassMetadata */
        foreach ($metadata as $classMetadata) {
            $className = str_replace($this->getNsEntity(), $this->getNsRepository(), $classMetadata->getName());
            
            $generator->writeEntityRepositoryClass($className, $this->getDirectory());
        }
    }

    /**
     * Starts generation of the classes
     *
     * @return void
     */
    public function generate($directory)
    {
        try {
            
            $this->setDirectory($directory);
            
            $metadata = $this->getMetadata();
            
            if (0 == count($metadata)) {
                throw new \Exception('Metadata is empty');
            }
            
            $this->generateEntities($metadata);
            $this->generateRepositories($metadata);
            
            return "\033[32m Generated entity and repository classes \033[0m";
            
        } catch (Exception $e) {
            
            return "\033[31m {$e->getMessage()}. \033[0m";
            
        }
    }
}
