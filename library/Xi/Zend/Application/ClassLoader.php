<?php
namespace Xi\Zend\Application;

/**
 * Like Doctrine's autoloader but doesn't fail when the class isn't found.
 * 
 * This allows you to put multiple class loaders with the same namespace
 * on the autoloader stack.
 */
class ClassLoader extends \Doctrine\Common\ClassLoader
{
    public function loadClass($className)
    {
        $path = ($this->getIncludePath() !== null ? $this->getIncludePath() . DIRECTORY_SEPARATOR : '')
               . str_replace($this->getNamespaceSeparator(), DIRECTORY_SEPARATOR, $className)
               . $this->getFileExtension();
        
        if (!file_exists($path)) {
            return false;
        }
        
        return parent::loadClass($className);
    }
}