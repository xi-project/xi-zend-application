<?php

namespace Xi\Zend\Application\Resource\Assetic;

class DirectoryParsingFileAssetFactory
{
    /**
     * @var SplFileParserFactory
     */
    private $parserFactory;
    
    /**
     * @var array<SplFileParser>
     */
    private $parsers;
    
    /**
     * @param SplFileParserFactory $parserFactory
     * @param array<SplFileParser> $parsers
     */
    public function __construct($parserFactory, $parsers)
    {
        $this->parserFactory = $parserFactory;
        $this->parsers = $parsers;
    }
    
    /**
     * @param array<path, parsers, blacklist> $directoryOptions
     * @return array<FileAsset>
     */
    public function createAssetsFromDirectory($directoryOptions)
    {
        $parsers = $this->discoverParsers($directoryOptions['parsers']);
        $results = array();
        
        // Recurse through all files in path
        foreach($this->allFilesIn($directoryOptions['path']) as $file) {
            // Skip files matching a blacklist item
            if ($this->listMatchesFile($directoryOptions['blacklist'], $file)) {
                continue;
            }
            
            foreach ($parsers as $parser) {
                if ($parser->acceptsFile($file)) {
                    $results[] = $parser->createFileAsset($directoryOptions['path'], $file);
                }
            }
        }
        
        return $results;
    }
    
    /**
     * @param array $directoryParsers 
     * @return array
     */
    protected function discoverParsers($directoryParsers)
    {
        if (empty($directoryOptions['parsers'])) {
            // apply default parser
            return array($this->createDefaultParser());
        } else {
            $results = array();
            foreach ($directoryOptions['parsers'] as $parser) {
                $results[] = $this->discoverParser($parser);
            }
            return $results;
        }
    }
    
    /**
     * @param string|array $parser
     * @return
     */
    protected function discoverParser($parser)
    {
        if (is_string($parser)) {
            // It's a reference to a parser we already have
            return $this->getParser($parser);
        } elseif (is_array($parser)) {
            // It's a definition for a new parser
            return $this->createParser($parser);
        }
    }
    
    /**
     * @param string $parser
     * @return SplFileParser
     */
    protected function getParser($parser)
    {
        return $this->parsers[$parser];
    }
    
    /**
     * @param array $options
     * @return SplFileParser
     */
    protected function createParser($options)
    {
        return $this->parserFactory->createFileParser($options);
    }
    
    /**
     * @return SplFileParser
     */
    protected function createDefaultParser()
    {
        return $this->createParser(array());
    }
    
    /**
     * @param string $dir
     * @return Iterator<SplFile>
     */
    protected function allFilesIn($dir)
    {
        return new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir), \RecursiveIteratorIterator::SELF_FIRST);
    }
    
    /**
     * @param array<regex> $list
     * @param SplFile $file
     * @return boolean
     */
    protected function listMatchesFile($list, $file)
    {
        foreach($list as $matcher) {
            if (preg_match($matcher, $file->getPathName())) {
                return true;
            }
        }
        return false;
    }
}