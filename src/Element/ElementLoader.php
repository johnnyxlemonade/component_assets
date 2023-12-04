<?php declare(strict_types = 1);

namespace Lemonade\Assets\Element;

use Lemonade\Assets\Compiler;
use Nette\Utils\Html;
use function ltrim;

abstract class ElementLoader
{
    
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
     * @param $source
     * @return Html|string
     */
    abstract public function getElement($source): Html|string;

    /**
     * Generovani
     * @return string
     */
    public function render(): string
    {
        
        foreach ($this->compiler->generate() as $file) {
            
            return $this->getElement(source: $this->getGeneratedFilePath(file: $file)) . PHP_EOL;
        }

        return "";
    }
        
    /**
     * Vraci cestu
     * @param string $file
     * @return string
     */
    protected function getGeneratedFilePath($file): string
    {

        if(function_exists(function: "base_url")) {

            return base_url(uri: ltrim(string: $this->tempPath, characters: ".") . "/" . $file->file);
        }

        return ltrim(string: $this->tempPath, characters: ".") . "/" . $file->file;
    }
}