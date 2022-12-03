<?php declare(strict_types = 1);

namespace Lemonade\Assets\Element;

use Nette\Utils\Html;

final class CssLoader extends ElementLoader {
    
    /**
     *
     * @var string
     */
    private $media;
    
    /**
     * 
     * @var string
     */
    private $title;
    
    /**
     *
     * @var string
     */
    private $type = "text/css";
    
    /**
     *
     * @var bool
     */
    private $alternate = FALSE;
    
    /**
     * Media
     * @return string
     */
    public function getMedia() {
        
        return $this->media;
    }
    
    /**
     * Typ
     * @return string
     */
    public function getType() {
        
        return $this->type;
    }
    
    /**
     * Title
     * @return string
     */
    public function getTitle() {
        
        return $this->title;
    }
    
    /**
     * Alternate
     * @return bool
     */
    public function isAlternate() {
        
        return $this->alternate;
    }
    
    /**
     * Set media
     * @param string $media
     * @return CssLoader
     */
    public function setMedia($media) {
        
        $this->media = $media;
        
        return $this;
    }
    
    /**
     * Set type
     * @param string $type
     * @return CssLoader
     */
    public function setType($type) {
        
        $this->type = $type;
        
        return $this;
    }
    
    /**
     * Set title
     * @param string $title
     * @return CssLoader
     */
    public function setTitle($title) {
        
        $this->title = $title;
        
        return $this;
    }
    
    /**
     * Set alternate
     * @param bool $alternate
     * @return CssLoader
     */
    public function setAlternate($alternate) {
        
        $this->alternate = $alternate;
        
        return $this;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \Lemonade\Assets\Element\ElementLoader::getElement()
     */
    public function getElement($source) {
        
        $alternate = ($this->alternate ? " alternate" : "");
        
        return Html::el("link")->rel("stylesheet".$alternate)->media($this->media)->title($this->title)->href($source);
    }
    
}