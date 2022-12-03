<?php declare(strict_types = 1);

namespace Lemonade\Assets;

use Lemonade\Assets\Interfaces\FileCollectionInterface;
use Lemonade\Assets\Exception\FileNotFoundException;

final class FileCollection implements FileCollectionInterface {
    
    /**
     * Root
     * @var string
     */
    private $root;
    
    /**
     * Soubory
     * @var array
     */
    private $files = [];
    
    /**
     * Sledovane soubory
     * @var array
     */
    private $watchFiles = [];
    
    /**
     * Constructor
     * @param string $root
     */
    public function __construct(string $root = null) {
        
        $this->root = $root;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \Lemonade\Assets\Interfaces\FileCollectionInterface::getRoot()
     */
    public function getRoot() {
        
        return $this->root;
    }
    

    
    /**
     * 
     * {@inheritDoc}
     * @see \Lemonade\Assets\Interfaces\FileCollectionInterface::addWatchFiles()
     */
    public function addWatchFiles($files) {
        
        foreach ($files as $file) {
            
            $this->addWatchFile($file);
        }
    }
    
    /**
     *
     * {@inheritDoc}
     * @see \Lemonade\Assets\Interfaces\FileCollectionInterface::addWatchFile()
     */
    public function addWatchFile($file) {
        
        $file = $this->cannonicalizePath((string) $file);
        
        if($file) {
            
            if (!in_array($file, $this->watchFiles, true)) {
                $this->watchFiles[] = $file;
            }
            
        }
        
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \Lemonade\Assets\Interfaces\FileCollectionInterface::getWatchFiles()
     */
    public function getWatchFiles() {
        
        return array_values($this->watchFiles);
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \Lemonade\Assets\Interfaces\FileCollectionInterface::getFiles()
     */
    public function getFiles() {
        
        return array_values($this->files);
    }
           
    
    /**
     *
     * {@inheritDoc}
     * @see \Lemonade\Assets\Interfaces\FileCollectionInterface::addFiles()
     */
    public function addFiles(array $files = []) {
        
        foreach ($files as $file) {
            
            $this->addFile($file);
        }
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \Lemonade\Assets\Interfaces\FileCollectionInterface::addFile()
     */
    public function addFile(string $file) {
        
        $file = $this->cannonicalizePath((string) $file);
        
        if($file) {

            if (!in_array($file, $this->files, TRUE)) {
                $this->files[] = $file;
            }

        }        
        
    }
    

    /**
     * 
     * {@inheritDoc}
     * @see \Lemonade\Assets\Interfaces\FileCollectionInterface::clear()
     */
    public function clear() {
        
        $this->files = [];
        $this->watchFiles = [];
       
    }
    
    
    /**
     * 
     * @param string $path
     * @throws FileNotFoundException
     * @return string
     */
    public function cannonicalizePath(string $path = null) {
        
        $rel = Path::normalize($this->root . "/" . $path);
        
        if (file_exists($rel)) {
            
            return $rel;
        }
        
        $abs = Path::normalize($path);
        
        if (file_exists($abs)) {
            
            return $abs;
        }
        
        return false;
    }
}