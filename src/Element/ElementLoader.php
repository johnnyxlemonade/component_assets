<?php declare(strict_types = 1);

namespace Lemonade\Assets\Element;

use Lemonade\Assets\Compiler;

abstract class ElementLoader {
    
    /**
     * Compiler
     * @var Compiler
     */
    private $compiler;
    
    /**
     * Tmp
     * @var string
     */
    private $tempPath;
    
    
    /**
     * Constructor
     * @param Compiler $compiler
     * @param string $tempPath
     */
    public function __construct(Compiler $compiler, string $tempPath) {
        
        $this->compiler = $compiler;
        $this->tempPath = $tempPath;
    }
        
    /**
     * Nastavi compiler
     * @param Compiler $compiler
     */
    public function setCompiler(Compiler $compiler) {
        
        $this->compiler = $compiler;
    }
    
    /**
     * Vraci compiler
     * @return \Lemonade\Assets\Compiler
     */
    public function getCompiler() {
        
        return $this->compiler;
    }
    
    /**
     * Nastaci tmp
     * @param string $tempPath
     */
    public function setTempPath(string $tempPath) {
        
        $this->tempPath = $tempPath;
    }
    
    /**
     * Vraci tmp
     * @return string
     */
    public function getTempPath() {
        
        return $this->tempPath;
    }
    

    /**
     * Html element
     * @param string $source
     * @return \Nette\Utils\Html
     */
    abstract public function getElement($source);
    
    /**
     * Generovani
     * @return string
     */
    public function render() {
        
        foreach ($this->compiler->generate() as $file) {
            
            return $this->getElement($this->getGeneratedFilePath($file)) . PHP_EOL;
        }
    }
        
    /**
     * Vraci cestu
     * @param string $file
     * @return string
     */
    protected function getGeneratedFilePath($file) {
        
        return \ltrim($this->tempPath, ".") . "/" . $file->file;
    }
}