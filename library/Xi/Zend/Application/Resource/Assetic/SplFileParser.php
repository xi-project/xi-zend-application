<?php
namespace Xi\Zend\Application\Resource\Assetic;

use Assetic\Asset\FileAsset,
    SplFileInfo as File;

/**
 * Creates FileAssets from SplFileInfo objects. Decides whether input files are
 * acceptable and carries option parameters from configuration to asset factory.
 */
class SplFileParser extends AbstractConfigurable
{
    /**
     * @var array default options
     */
    protected $options = array(
        'match' => '.*',
        'filters' => array(),
        'output' => '../public/*',
        'cache' => false,
    );
    
    /**
     * @var FileAssetFactory
     */
    private $assetFactory;
    
    /**
     * @param AssetFactory $assetFactory
     * @param array $options
     */
    public function __construct($assetFactory, $options)
    {
        parent::__construct($options);
        $this->assetFactory = $assetFactory;
    }
    
    /**
     * @param File $file
     * @return boolean
     */
    public function acceptsFile($file)
    {
        return $file->isFile()
            && $this->matches($file->getFilename());
    }
    
    /**
     * @param string $filename
     * @return boolean
     */
    protected function matches($filename)
    {
        return preg_match($this->getOption('match'), $filename);
    }
    
    /**
     * @param string $root
     * @param File $file
     * @return FileAsset
     */
    public function createFileAsset($root, $file)
    {
        return $this->getAssetFactory()->createFileAsset(array(
            'inputs' =>     array($file->getPathname()),
            'options' =>    $this->getAssetOptions($root, $file),
            'filters' =>    $this->getOption('filters', array()),
            'output' =>     $this->getOption('output'),
            'cache' =>      (bool) $this->getOption('cache')
        ));
    }
    
    /**
     * @param string $root
     * @param File $file 
     * @return array
     */
    protected function getAssetOptions($root, $file)
    {
        $pathinfo = pathinfo($file->getPathname());
        
        return array(
            'root' => $root,
            'name' => $pathinfo['filename']
        );
    }
    
    /**
     * @return FileAssetFactory
     */
    protected function getAssetFactory()
    {
        return $this->assetFactory;
    }
}