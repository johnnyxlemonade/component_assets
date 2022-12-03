<?php declare(strict_types = 1);

namespace Lemonade\Assets;

use Lemonade\Assets\Interfaces\NamingConventionInterface;

final class NameConvention implements NamingConventionInterface {
    
    /**
     * Prefix
     * @var string
     */
    private $prefix = "loader-";
    
    /**
     * Suffix
     * @var string
     */
    private $suffix = "";
    
    /**
     * Css 
     * @return \Lemonade\Assets\NameConvention
     */
    public static function createCssConvention() {
        
        $convention = new static();
        $convention->setPrefix("loader-");
        $convention->setSuffix(".css");
        
        return $convention;
    }
    
    /**
     * Js
     * @return \Lemonade\Assets\NameConvention
     */
    public static function createJsConvention() {
     
        $convention = new static();
        $convention->setPrefix("loader-");
        $convention->setSuffix(".js");
        
        return $convention;
    }
    
    
    /**
     * Vraci prefix
     * @return string
     */
    public function getPrefix() {
        
        return $this->prefix;
    }
    
    /**
     * Nastavi prefix
     * @param string $prefix
     */
    public function setPrefix(string $prefix = null) {
        
        $this->prefix = (string) $prefix;
    }
    
    
    /**
     * Vraci suffix
     * @return string
     */
    public function getSuffix() {
        return $this->suffix;
    }
    
    /**
     * Nastavi suffix
     * @param string $suffix
     */
    public function setSuffix(string $suffix = null) {
        
        $this->suffix = (string) $suffix;
    }
    
    
    /**
     * 
     * {@inheritDoc}
     * @see \Lemonade\Assets\Interfaces\NamingConventionInterface::getFilename()
     */
    public function getFilename(array $files, Compiler $compiler) {
        
        $name = $this->createHash($files, $compiler);
        
        if (\count($files) === 1) {
            
            $name .= "-" . \pathinfo($files[0], PATHINFO_FILENAME);
        }
        
        return $this->prefix . $name . $this->suffix;
    }
    
    /**
     * Vytvoreni hash souboru
     * @param array $files
     * @param Compiler $compiler
     * @return string
     */
    protected function createHash(array $files, Compiler $compiler) {
        
        return \substr(\md5(\implode("|", $files)), 0, 12) . "-" . \substr(\md5((string)$compiler->getLastModified()), 0, 12);
    }
}
    