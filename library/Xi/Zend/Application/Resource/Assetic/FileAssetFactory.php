<?php
namespace Xi\Zend\Application\Resource\Assetic;

use Assetic\Asset,
    Assetic\Cache,
    Assetic\Factory\AssetFactory;

/**
 * A straightforward encapsulation of the creation of file assets with an input
 * configuration parameter convention and the option for caching
 */
class FileAssetFactory
{
    /**
     * @var AssetFactory
     */
    private $assetFactory;
    
    /**
     * @var Cache\CacheInterface
     */
    private $assetCache;
    
    /**
     * @param AssetFactory $assetFactory
     * @param Cache\CacheInterface $assetCache
     */
    public function __construct($assetFactory, $assetCache)
    {
        $this->assetFactory = $assetFactory;
        $this->assetCache = $assetCache;
    }
    
    /**
     * @param array(inputs, filters, options, output, cache) $fileOptions
     * @return Assetic\Asset\AssetInterface
     */
    public function createFileAsset($fileOptions)
    {
        $asset = $this->getAssetFactory()->createAsset(
            $fileOptions['inputs'],
            $fileOptions['filters'],
            array('output' => $fileOptions['output']) + $fileOptions['options']
        );
        if ($fileOptions['cache']) {
            $asset = $this->asCachedAsset($asset);
        }
        return $asset;
    }
    
    /**
     * @param Assetic\Asset\FileAsset $asset
     * @return Asset\AssetCache 
     */
    protected function asCachedAsset($asset)
    {
        return new Asset\AssetCache($asset, $this->getAssetCache());
    }
    
    /**
     * @return AssetFactory
     */
    protected function getAssetFactory()
    {
        return $this->assetFactory;
    }
    
    /**
     * @return Cache\CacheInterface
     */
    protected function getAssetCache()
    {
        return $this->assetCache;
    }
}