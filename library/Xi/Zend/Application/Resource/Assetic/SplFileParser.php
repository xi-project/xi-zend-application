<?php
namespace Xi\Zend\Application\Resource\Assetic;

use SplFileInfo as File;

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
     * @param FileAssetFactory $assetFactory
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
        $matcher = "%" . str_replace("%", "\\%", $this->getOption('match')) . "%";
        return preg_match($matcher, $filename);
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
            'options' =>    (array) $this->getAssetOptions($root, $file),
            'filters' =>    (array) $this->getOption('filters', array()),
            'output' =>     (string) $this->getOption('output'),
            'cache' =>      (bool) $this->getOption('cache'),
        ));
    }
    
    /**
     * @param string $root
     * @param File $file 
     * @return array
     */
    protected function getAssetOptions($root, $file)
    {
        return array(
            'root' => $root,
            'name' => $this->formatAssetName($root, $file)
        );
    }
    
    /**
     * Return's the described file's name in relation to the root while omitting
     * the file extension
     *  
     * @param string $root
     * @param File $file
     * @return string
     */
    protected function formatAssetName($root, $file)
    {
        $pathinfo = pathinfo($file->getPathname());
        $directory = str_replace($root, '', $pathinfo['dirname']);
        $baseName = str_replace('.' . $pathinfo['extension'], '', $pathinfo['basename']);
        $assetName = ltrim($directory, '/') . '/' . $baseName;
        return $assetName;
    }
    
    /**
     * @return FileAssetFactory
     */
    protected function getAssetFactory()
    {
        return $this->assetFactory;
    }
}