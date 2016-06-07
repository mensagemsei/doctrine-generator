<?php

namespace Generator;

/**
 * Autoloading Classes to generator package
 *
 * @author José Carlos <josecarlos@globtec.com.br>
 */
class Autoloader
{
    /**
     * The namespace prefix
     *
     * @var string
     */
    private $prefix = 'Generator\\';

    /**
     * The base directory for class files in the namespace
     *
     * @var string
     */
    private $directory = __DIR__ . '/';

    /**
     * Register loader with SPL autoloader stack
     */
    public function register()
    {
        spl_autoload_register(array($this, 'loadClass'));
    }

    /**
     * Get filename
     *
     * @param string $class            
     * @return string
     */
    private function getFilename($class)
    {
        return $this->directory . str_replace('\\', '/', $class) . '.php';
    }

    /**
     * Loads the class file for a given class name
     *
     * @param string $class            
     * @return void
     */
    public function loadClass($name)
    {
        $length = strlen($this->prefix);
        
        if (0 != strncmp($this->prefix, $name, $length)) {
            return;
        }
        
        $filename = $this->getFilename(substr($name, $length));
        
        if (file_exists($filename)) {
            require_once $filename;
        }
    }
}