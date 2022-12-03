<?php declare(strict_types = 1);

namespace Lemonade\Assets\Interfaces;

interface FileCollectionInterface {
    
    /**
     * Vraci root
     * @return string
     */
    public function getRoot();    
    
    /**
     * Vycistit vse
     */
    public function clear();
    
    /**
     * Vraci soubory
     * @return array
     */
    public function getFiles();
    
    /**
     * Pridat sledovany soubor
     * @param string $file
     */
    public function addWatchFile($file);
    
    /**
     * Pridat sledovane soubory
     * @param array|\Traversable $files
     */
    public function addWatchFiles($files);    
    
    /**
     * Vraci sledovene soubory
     * @return array
     */
    public function getWatchFiles();
    
    /**
     * Pridat soubor 
     * @param string $file
     */
    public function addFile(string $file);
    
    /**
     * Pridat soubory
     * @param array $files
     */
    public function addFiles(array $files = []);

}