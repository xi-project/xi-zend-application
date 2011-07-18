<?php

/**
 * Xi
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled with this
 * package in the file LICENSE.
 *
 * @category   Xi
 * @package    Application
 * @subpackage Resource
 * @license    http://www.opensource.org/licenses/BSD-3-Clause New BSD License
 */

namespace Xi\Application\Resource;

use Zend_Application_Resource_ResourceAbstract as ResourceAbstract;

/**
 * Assetic application resource
 *
 * @category   Xi
 * @package    Application
 * @subpackage Resource
 * @author     pekkis
 */
class Assetic extends ResourceAbstract
{

    private $assetManager;
    
    private $filterManager;
    
    private $assetFactory;
    
    private $assetCache;
        
    public function getAssetCache()
    {
        if(!$this->assetCache) {
            $this->assetCache = new \Assetic\Cache\FilesystemCache(realpath(APPLICATION_PATH . '/../data/cache'));
        }
        return $this->assetCache;
    }
    
    
    /**
     * Returns asset manager
     * 
     * @return \Assetic\AssetManager
     */
    public function getAssetManager()
    {
        if(!$this->assetManager) {
            $this->assetManager = new \Assetic\AssetManager();
        }
        return $this->assetManager;
    }
    
    /**
     * Returns filter manager
     * 
     * @return \Assetic\FilterManager
     */
    public function getFilterManager()
    {
        if(!$this->filterManager) {
            
            $options = $this->getOptions();
            
            $this->filterManager = new \Assetic\FilterManager();
            
            $lessFilter = new \Assetic\Filter\LessFilter(null, $options['nodePath']);
            $lessFilter->setCompress(true);
            
            $this->filterManager->set('less', $lessFilter);
            
            $this->filterManager->set('closure', new \Assetic\Filter\GoogleClosure\CompilerJarFilter($options['closureCompilerPath'], $options['javaPath']));
                        
            $jpegOptimFilter = new \Assetic\Filter\JpegoptimFilter($options['jpegOptimPath']);
            $jpegOptimFilter->setStripAll(true);

            $this->filterManager->set('jpegoptim', $jpegOptimFilter);
            
            $optiPngFilter = new \Assetic\Filter\OptiPngFilter($options['optiPngPath']);
            $optiPngFilter->setLevel(2);
            
            $this->filterManager->set('optipng', $optiPngFilter);
            
            // $this->filterManager->set('closure', new \Assetic\Filter\UglifyJsFilter($options['uglifyPath']));
        }
        
        
        
        
        return $this->filterManager;
    }
    
    /**
     * Returns asset factory
     * 
     * @return \Assetic\Factory\AssetFactory
     */
    public function getAssetFactory()
    {
        if(!$this->assetFactory) {
            $this->assetFactory = new \Assetic\Factory\AssetFactory(realpath(APPLICATION_PATH . '/../public'));
            $this->assetFactory->setAssetManager($this->getAssetManager());
            $this->assetFactory->setFilterManager($this->getFilterManager());
        }
        return $this->assetFactory;
    }
    
    
    public function init()
    {
        $options = $this->getOptions();
        
        if($options['skip']) {
            return $this;
        }
        $this->initAssets($options);
        return $this;
    }
    
    
    
    public function initAssets()
    {
        $options = $this->getOptions();
        
        $defaultWoptions = array(
            'combined' => true,
            'leaves' => false,
        );
        $woptions = array();
        
        $am = $this->getAssetManager();
        
        $fm = $this->getFilterManager();
        
        $fassets = array();
        
        if (isset($options['parser'])) {
            
            foreach ($options['parser'] as $parser) {
                
                $filters = array();
                foreach ($parser['files'] as $key => $file) {
                    
                    $filters[$key] = array();
                    
                    if(isset($file['filters'])) {
                        foreach($file['filters'] as $f) {
                            
                            if (substr($f, 0, 1) == '?') {
                                $fn = substr($f, 1);
                                $init = (bool) !$parser['debug'];
                            } else {
                               $fn = $f;
                               $init = true;
                            }
                            
                            if($init) {
                                
                                if($fm->has($fn)) {
                                    $filters[$key][] = $fm->get($fn);
                                }
                            }
                        }
                    }
                   
                }
                
                $diterator = new \RecursiveDirectoryIterator($parser['directory']);
                $riterator = new \RecursiveIteratorIterator($diterator, \RecursiveIteratorIterator::SELF_FIRST);
                                                
                foreach ($riterator as $file) {
                    if(isset($parser['blacklist'])) {
                        foreach($parser['blacklist'] as $bl) {
                            if (preg_match($bl, $file->getPathName())) {
                                continue; 
                            }
                        }
                    }
                    
                    foreach($parser['files'] as $key => $fopts) {

                        $ppinfo = pathinfo($fopts['output']);
                        
                        if ($file->isFile() && preg_match($fopts['pattern'], $file->getFilename())) {
                            
                            $pinfo = pathinfo($file);
                        
                            $pinfo['dirname'] = str_ireplace($parser['directory'], $ppinfo['dirname'], $pinfo['dirname']);
                            $pinfo['extension'] = $ppinfo['extension'];
                        
                            $fasset = new \Assetic\Asset\FileAsset($file->getPathName(), $filters[$key]);
                            $fasset->setTargetUrl($pinfo['dirname'] . '/' . $pinfo['filename'] . '.' . $pinfo['extension']);
                        
                            $fassets[] = $fasset;
                                
                        }
                        
                    }
                }
            }
        }
        
        $f = $this->getAssetFactory();
        if(isset($options['collections'])) {
            foreach($options['collections'] as $name => $coll) {
                
                $woptions[$name] = isset($coll['write']) ? $coll['write'] : array();
                $woptions[$name] = array_merge($defaultWoptions, $woptions[$name]);
                
                $coll['options']['name'] = str_ireplace("{APPLICATION_REVISION}", APPLICATION_REVISION, $coll['options']['name']);
                $asset = $f->createAsset($coll['inputs'], $coll['filters'], $coll['options']);
                if($coll['cache']) {
                    $asset = new \Assetic\Asset\AssetCache($asset, $this->getAssetCache());                     
                }
                $am->set($name, $asset);
            }
        }
        
        if(isset($options['fileAssets'])) {
            foreach($options['fileAssets'] as $name => $fileAsset) {
                
                $asset = new \Assetic\Asset\FileAsset($fileAsset['path']);
                $asset->setTargetUrl($fileAsset['targetUrl']);

                if(isset($fileAsset['filters'])) {
                    foreach($fileAsset['filters'] as $filter) {
                        $f = $this->getFilterManager()->get($filter);
                        $asset->ensureFilter($f);                        
                    }
                }
                
                $am->set($name, $asset);
                
            }
            
        }
        
        
        
        $am = $this->getAssetManager();
        
        $writer = new \Assetic\AssetWriter(APPLICATION_PATH . '/../public');

        foreach ($fassets as $fasset) {
            
            if(file_exists(APPLICATION_PATH . '/../public' . '/' . $fasset->getTargetUrl())) {
                
                $amod = filemtime(APPLICATION_PATH . '/../public' . '/' . $fasset->getTargetUrl());

                if ($fasset->getLastModified() <= $amod) {
                    continue;
                }
                
                
                    
            }
            $writer->writeAsset($fasset);
            
        }
                
        foreach ($am->getNames() as $name) {
            
            $asset = $am->get($name);
            if(file_exists(APPLICATION_PATH . '/../public' . '/' . $asset->getTargetUrl())) {
                $amod = filemtime(APPLICATION_PATH . '/../public' . '/' . $asset->getTargetUrl());
                if ($asset->getLastModified() <= $amod) {
                    continue;
                }
            }
                      

            $writeOptions = $woptions[$name];
            
            if($writeOptions['combined']) {
                $writer->writeAsset($asset);
            }
            
            if($writeOptions['leaves']) {
                foreach($asset as $leaf) {
                    $writer->writeAsset($leaf);                    
                }
            }            
        }
        
    }
    
    
    
    
    
    
    
    
    
    
}
