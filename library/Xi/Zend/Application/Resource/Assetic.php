<?php
namespace Xi\Zend\Application\Resource;

use Assetic\AssetManager,
    Assetic\FilterManager,
    Assetic\Factory\AssetFactory,
    Assetic\AssetWriter,
    Assetic\Filter,
    Assetic\Cache,
    Assetic\Asset;

/**
 * Assetic application resource
 *
 * @category   Xi
 * @package    Application
 * @subpackage Resource
 * @license    http://www.opensource.org/licenses/BSD-3-Clause New BSD License
 */
class Assetic extends AbstractResource
{
    /**
     * @var array default options
     */
    protected $_options = array(
        'enabled' => true,
        'publicPath' => null,
        'cachePath' => null,
        
        'filters' => array(),
        'parsers' => array(),
        'directories' => array(),
        'files' => array(),
    );
    
    /**
     * @var Assetic\ServiceLocator
     */
    private $serviceLocator;
    
    /**
     * @return self 
     */
    public function init()
    {
        $options = $this->getOptions();
        
        // Do not run if not enabled
        if (!$options['enabled']) {
            return $this;
        }
        
        $this->initAssets($options);
        return $this;
    }
    
    /**
     * Collect assets and write them to their targets
     * 
     * @param array $options 
     * @return void
     */
    public function initAssets($options)
    { 
        $assets = $this->getAssets($options);
        $this->writeAssets($assets, $this->getServiceLocator()->createAssetWriter());
    }
    
    /**
     * @param array $options
     * @return array<Asset\AssetInterface>
     */
    protected function getAssets($options)
    {
        $directoryAssets = $this->parseDirectoryAssets(
            $this->getServiceLocator()->createDirectoryParser($this->createParsers()),
            empty($options['directories']) ? array() : $options['directories']
        );
        $fileAssets = $this->parseFileAssets(
            $this->getServiceLocator()->createFileAssetFactory(),
            empty($options['files']) ? array() : $options['files']
        );
        return array_merge($directoryAssets, $fileAssets);
    }
    
    /**
     * @param Assetic\DirectoryParsingFileAssetFactory $parser
     * @param array $directories
     * @return array<Asset\AssetInterface>
     */
    protected function parseDirectoryAssets($parser, $directories)
    {
        $result = array();
        foreach ($directories as $directoryOptions) {
            $assets = $parser->createAssetsFromDirectory($this->extractDirectoryOptions($directoryOptions));
            $result = array_merge($result, $assets);
        }
        return $result;
    }
    
    /**
     * @param Assetic\FileAssetFactory  $factory
     * @param array $files
     * @return array<Asset\AssetInterface>
     */
    protected function parseFileAssets($factory, $files)
    {
        $result = array();
        foreach ($files as $fileOptions) {
            $result[] = $factory->createFileAsset($this->extractFileOptions($fileOptions));
        }
        return $result;
    }
    
    /**
     * Writes the given explicit assets and any implicit assets in the asset
     * manager
     * 
     * @param array<Asset\AssetInterface> $assets
     * @param AssetWriter $writer 
     */
    protected function writeAssets($assets, $writer)
    {
        foreach ($assets as $asset) {
            $writer->writeAsset($asset);
        }
        
        $am = $this->getServiceLocator()->getAssetManager();
        
        foreach ($am->getNames() as $name) {
            $writer->writeAsset($am->get($name));
        }
    }
    
    /**
     * @return array<SplFileParser>
     */
    protected function createParsers()
    {
        $parserFactory = $this->getServiceLocator()->getParserFactory();
        $parsers = array();
        foreach ($this->getOption('parsers', array()) as $name => $definition) {
            $parsers[$name] = $parserFactory($definition);
        }
        return $parsers;
    }
    
    /**
     * @param array $directoryOptions
     * @return array
     */
    protected function extractDirectoryOptions($directoryOptions)
    {
        return array(
            'path' => $directoryOptions['path'],
            'parsers' => empty($directoryOptions['parsers']) ? array() : $directoryOptions['parsers'],
            'blacklist' => empty($directoryOptions['blacklist']) ? array() : $directoryOptions['blacklist'],
        );
    }
    
    /**
     * @param array $fileOptions
     * @return array
     */
    protected function extractFileOptions($fileOptions)
    {
        return array(
            'inputs' =>     $fileOptions['inputs'],
            'filters' =>    isset($fileOptions['filters']) ? $fileOptions['filters'] : array(),
            'options' =>    isset($fileOptions['options']) ? $fileOptions['options'] : array(),
            'output' =>     $fileOptions['output'],
            'cache' =>      !empty($fileOptions['cache'])
        );
    }
    
    /**
     * @param string $name option
     * @return string
     */
    protected function getRelativeApplicationPathOption($name)
    {
        return realpath(APPLICATION_PATH . DIRECTORY_SEPARATOR . $this->getOption($name));
    }
    
    /**
     * @param string $name
     * @param mixed $default optional, defaults to null
     * @return mixed
     */
    public function getOption($name, $default = null)
    {
        return isset($this->_options[$name]) ? $this->_options[$name] : $default;
    }
    
    /**
     * @return Assetic\ServiceLocator
     */
    public function getServiceLocator()
    {
        if (null === $this->serviceLocator) {
            $this->serviceLocator = new Assetic\ServiceLocator($this->_options);
        }
        return $this->serviceLocator;
    }
}
