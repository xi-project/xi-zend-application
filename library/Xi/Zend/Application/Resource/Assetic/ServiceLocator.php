<?php
namespace Xi\Zend\Application\Resource\Assetic;

class ServiceLocator extends AbstractConfigurable
{
    /**
     * @var AssetManager
     */
    private $assetManager;
    
    /**
     * @var FilterManager
     */
    private $filterManager;
    
    /**
     * @var AssetFactory
     */
    private $assetFactory;
    
    /**
     * @return AssetManager
     */
    public function getAssetManager()
    {
        if (null ===$this->assetManager) {
            $this->assetManager = $this->createAssetManager();
        }
        return $this->assetManager;
    }

    /**
     * @return FilterManager
     */
    public function getFilterManager()
    {
        if (null === $this->filterManager) {
            $this->filterManager = $this->createFilterManager();
        }
        return $this->filterManager;
    }
    
    /**
     * @return AssetFactory
     */
    public function getAssetFactory()
    {
        if (null === $this->assetFactory) {
            $this->assetFactory = $this->createAssetFactory();
        }
        return $this->assetFactory;
    }
    
    /**
     * @return Assetic\SplFileParserFactory
     */
    protected function getParserFactory()
    {
        if (null === $this->parserFactory) {
            $this->parserFactory = $this->createParserFactory();
        }
        return $this->parserFactory;
    }
    
    /**
     * @return AssetManager
     */
    protected function createAssetManager()
    {
        return new AssetManager();
    }
    
    /**
     * @return FilterManager
     */
    protected function createFilterManager()
    {
        return $this->createFilterManagerFactory()->createFilterManager();
    }
    
    /**
     * @return Assetic\FilterManagerFactory
     */
    protected function createFilterManagerFactory()
    {
        return new Assetic\FilterManagerFactory($this->getEnabledFilters());
    }
    
    /**
     * @return AssetFactory
     */
    protected function createAssetFactory()
    {
        // No root given; assets will need to be identified with absolute path
        $assetFactory = new AssetFactory($root = null);
        $assetFactory->setAssetManager($this->getAssetManager());
        $assetFactory->setFilterManager($this->getFilterManager());
        $assetFactory->setDebug($this->getDebug());
        return $assetFactory;
    }
    
    /**
     * @return Assetic\SplFileParserFactory
     */
    protected function createParserFactory()
    {
        return new Assetic\SplFileParserFactory($this->getAssetFactory());
    }
    
    /**
     * @return AssetWriter 
     */
    public function createAssetWriter()
    {
        return new AssetWriter();
    }
    
    /**
     * @return Assetic\DirectoryParsingFileAssetFactory 
     */
    public function createDirectoryParser()
    {
        return new Assetic\DirectoryParsingFileAssetFactory($this->getParserFactory(), $this->createParsers());
    }
    
    /**
     * @return Assetic\FileAssetFactory 
     */
    public function createFileAssetFactory()
    {
        return new Assetic\FileAssetFactory($this->getAssetFactory(), $this->createAssetCache());
    }
    
    /**
     * @return Cache\FilesystemCache 
     */
    public function createAssetCache()
    {
        return new Cache\FilesystemCache($this->getCachePath());
    }
    
    /**
     * @return string
     */
    public function getPublicPath()
    {
        return $this->getRelativeApplicationPathOption('publicPath');
    }
    
    /**
     * @return string
     */
    public function getCachePath()
    {
        return $this->getRelativeApplicationPathOption('cachePath');
    }
    
    /**
     * @return bool
     */
    public function getDebug()
    {
        return (bool) $this->getOption('debug');
    }
    
    /**
     * @return array<string>
     */
    public function getEnabledFilters()
    {
        return $this->getOption('filters', array());
    }
}