<?php
namespace Xi\Zend\Application\Resource\Assetic;
use Assetic\Filter,
    Assetic\FilterManager;

class FilterManagerFactory extends AbstractConfigurable
{
    /**
     * @return FilterManager
     */
    public function createFilterManager()
    {
        $filterManager = new FilterManager();
        foreach ($this->getFilters() as $name => $filter) {
            $filterManager->set($name, $filter);
        }
        return $filterManager;
    }
    
    /**
     * @return array<string => Filter\FilterInterface>
     */
    protected function getFilters()
    {
        $filters = array();
        foreach ($this->getEnabledFilters() as $filterName) {
            $filters[] = $this->createFilterByName($filterName);
        }
        return $filters;
    }
    
    /**
     * @param string $filterName
     * @return Filter\FilterInterface
     */
    protected function createFilterByName($filterName)
    {
        // FIXME: Hardcoded list of acceptable filters
        switch ($filterName) {
            case 'less': return $this->createLessFilter();
            case 'jpegoptim': return $this->createJpegOptimFilter();
            case 'optipng': return $this->createOptiPngFilter();
        }
        throw new \InvalidArgumentException("Unknown filter name '$filterName'");
    }
    
    protected function createLessFilter()
    {
        $lessFilter = new Filter\LessFilter($this->getPathOption('node'), $this->getPathOption('nodePaths', array()));
        $lessFilter->setCompress(true);
        return $lessFilter;
    }
    
    protected function createJpegOptimFilter()
    {
        $jpegOptimFilter = new Filter\JpegoptimFilter($this->getPathOption('optipng'));
        $jpegOptimFilter->setStripAll(true);
        return $jpegOptimFilter;
    }
    
    protected function createOptiPngFilter()
    {
        $optiPngFilter = new Filter\OptiPngFilter($this->getPathOption('optipng'));
        $optiPngFilter->setLevel(2);
        return $optiPngFilter;
    }
    
    /**
     * @param string $name
     * @param mixed $default optional
     * @return string
     * @throws Exception
     */
    protected function getPathOption($name, $default = null)
    {
        $paths = $this->getOption('paths', array());
        if (isset($paths[$name])) {
            return $paths[$name];
        }
        return $default;
    }
    
    /**
     * @return array
     */
    protected function getEnabledFilters()
    {
        return $this->getOption('enabled', array());
    }
}