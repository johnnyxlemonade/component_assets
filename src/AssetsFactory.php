<?php declare(strict_types = 1);

namespace Lemonade\Assets;

use Lemonade\Assets\Element\CssLoader;
use Lemonade\Assets\Element\JSLoader;

final class AssetsFactory {    
        
    /**
     * Input
     * @var string
     */
    private $appDirectory;

    /**
     * FileCollection
     * @var FileCollection
     */
    private $appCollection;
    
    /**
     * Css directory
     * @var string
     */
    private $cssDir = "/css";
    
    /**
     * Js directory
     * @var string
     */
    private $jsDir = "/js";
    
    /**
     * Compiled dir
     * @var string
     */
    private $compiled = "/compiled";
    


    /**
     * 
     * @param string $directory
     * @param array $files
     * @return \Lemonade\Assets\AssetsFactory
     */
    protected function _setCollection(string $directory, array $files) {
        
        $this->appDirectory  = $directory;
        $this->appCollection = new FileCollection($directory);
        $this->appCollection->addFiles($files);
        
        return $this;
    }
    
    /**
     * Css watch files
     * @return \Lemonade\Assets\AssetsFactory
     */
    protected function _addCssWatchFiles() {
        
        $this->appCollection->addWatchFiles(\Nette\Utils\Finder::findFiles("*.css", "*.sass", ".map")->in($this->_getCssDirectory()));
        
        return $this;
    }
    
    /**
     * Js watch files
     * @return \Lemonade\Assets\AssetsFactory
     */
    protected function _addJsWatchFiles() {
        
        $this->appCollection->addWatchFiles(\Nette\Utils\Finder::findFiles("*.js")->in($this->_getJsDirectory()));
        
        return $this;
    }
    
    /**
     *
     * @return string
     */
    protected function _cssMinifier() {
        
        $compiler = Compiler::createCssCompiler($this->appCollection, $this->_getCompiledDirectory());          
        $compiler->addFilter(function ($code) {
            
            return Minifier::css($code);
            
        });
            
        return (new CssLoader($compiler, $this->_getCompiledDirectory()))->setMedia("screen")->render();
    }
    
    /**
     *
     * @return string
     */
    protected function _jsMinifier() {
        
        $compiler = Compiler::createJsCompiler($this->appCollection, $this->_getCompiledDirectory());
        $compiler->addFilter(function ($code) {
            
            return Minifier::js($code);
            
        });
        
        return (new JSLoader($compiler, $this->_getCompiledDirectory()))->render();
    }
    
    
    /**
     * Css directory
     * @return string
     */
    private function _getCssDirectory(): string {
        
        return \sprintf("%s%s", $this->appDirectory, $this->cssDir);
        
    }
    
    /**
     * Js directory
     * @return string
     */
    private function _getJsDirectory(): string {
        
        return \sprintf("%s%s", $this->appDirectory, $this->jsDir);
    }
    
    /**
     * Compiled directory
     * @return string
     */
    private function _getCompiledDirectory(): string {
        
        return \sprintf("%s%s", $this->appDirectory, $this->compiled);
    }
    
    
    /**
     * Vykresleni css
     * @param string $directory
     * @param array $files
     * @return string
     */
    public static function createCss(string $directory, array $files = []) {
        
        return (new static)->_setCollection($directory, $files)->_addCssWatchFiles()->_cssMinifier();        
    }
    
    
    /**
     * Vykresleni Js
     * @param string $directory
     * @param array $files
     * @return string
     */
    public static function createJs(string $directory, array $files = []) {
        
        return (new static)->_setCollection($directory, $files)->_addJsWatchFiles()->_jsMinifier();
    }
    
}