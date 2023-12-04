<?php declare(strict_types = 1);

namespace Lemonade\Assets\Element;

use Nette\Utils\Html;

final class CssLoader extends ElementLoader {
    
    /**
     *
     * @var string
     */
    private string $media = "";
    
    /**
     *
     * @var string
     */
    private string $type = "text/css";
    
    /**
     *
     * @var bool
     */
    private bool $alternate = false;
    
    /**
     * Media
     * @return string
     */
    public function getMedia(): string
    {
        
        return $this->media;
    }
    
    /**
     * Typ
     * @return string
     */
    public function getType(): string
    {
        
        return $this->type;
    }

    /**
     * Alternate
     * @return bool
     */
    public function isAlternate(): bool
    {
        
        return $this->alternate;
    }
    
    /**
     * Set media
     * @param string $media
     * @return CssLoader
     */
    public function setMedia($media): self
    {
        
        $this->media = $media;
        
        return $this;
    }
    
    /**
     * Set type
     * @param string $type
     * @return CssLoader
     */
    public function setType($type): self
    {
        
        $this->type = $type;
        
        return $this;
    }

    /**
     * Set alternate
     * @param bool $alternate
     * @return CssLoader
     */
    public function setAlternate($alternate): self
    {
        
        $this->alternate = $alternate;
        
        return $this;
    }

    /**
     * @param $source
     * @return Html
     */
    public function getElement($source): Html
    {
        
        $alternate = ($this->alternate ? " alternate" : "");
        
        return Html::el(name: "link")->rel("stylesheet" . $alternate)->media($this->media)->href($source);
    }
    
}