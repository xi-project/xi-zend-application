<?php
namespace Xi\Zend\Application\Resource\Assetic;

use Assetic\Factory\AssetFactory;

/**
 * Creates SplFileParsers using a set of options given for creation and an
 * AssetFactory provided at construction.
 */
class SplFileParserFactory
{   
    /**
     * @var AssetFactory
     */
    private $assetFactory;
    
    /**
     * @param AssetFactory $assetFactory
     * @param FilterManager $filterManager
     */
    public function __construct($assetFactory)
    {
        $this->assetFactory = $assetFactory;
    }
    
    /**
     * @param array $options
     * @return SplFileParser
     */
    public function createFileParser($options)
    {
        return new SplFileParser(
            $options,
            $this->assetFactory
        );
    }
}