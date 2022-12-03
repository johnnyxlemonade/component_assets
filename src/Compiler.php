<?php declare(strict_types = 1);

namespace Lemonade\Assets;

use Lemonade\Assets\Interfaces\FileCollectionInterface;
use Lemonade\Assets\Interfaces\NamingConventionInterface;
use Lemonade\Assets\Exception\InvalidArgumentException;

final class Compiler {
    
    /**
     * Vystupni adresar
     * @var string
     */
    private $outputDir;
    
    /**
     * Spojit soubory
     * @var boolean
     */
    private $joinFiles = true;   
   
    /**
     * Filtry
     * @var array
     */
    private $filters = [];
    
    /**
     * Filtry na souborech
     * @var array
     */
    private $fileFilters = [];
    
    /**
     * FileCollectionInterface
     * @var FileCollectionInterface
     */
    private $collection;
    
    /**
     * NamingConventionInterface
     * @var NamingConventionInterface
     */
    private $namingConvention;
    
    /**
     * Kontrola
     * @var boolean
     */
    private $checkLastModified = TRUE;
    
    
    /**
     * 
     * @param FileCollectionInterface $files
     * @param NamingConventionInterface $convention
     * @param string $outputDir
     */
    public function __construct(FileCollectionInterface $files, NamingConventionInterface $convention, $outputDir) {
        
        $this->collection = $files;
        $this->namingConvention = $convention;
        
        $this->setOutputDir($outputDir);
    }
    
    /**
     * Create compiler with predefined css output naming convention
     * @param FileCollectionInterface $files
     * @param string $outputDir
     * @return Compiler
     */
    public static function createCssCompiler(FileCollectionInterface $files, $outputDir) {
        
        return new static($files, NameConvention::createCssConvention(), $outputDir);
    }
    
    /**
     * Create compiler with predefined javascript output naming convention
     * 
     * @param FileCollectionInterface $files
     * @param string $outputDir
     * @return Compiler
     */
    public static function createJsCompiler(FileCollectionInterface $files, $outputDir) {
        
        return new static($files, NameConvention::createJsConvention(), $outputDir);
    }
    
    /**
     * Get temp path
     * @return string
     */
    public function getOutputDir() {
        
        return $this->outputDir;
    }
    
    /**
     * Set temp path
     * @param string $tempPath
     */
    public function setOutputDir($tempPath) {
        
        $tempPath = Path::normalize($tempPath);
        
        if (!is_dir($tempPath) && !@mkdir($tempPath, 0777, true) && !is_dir($tempPath)) {
            
            throw new InvalidArgumentException("Cant create directory '$tempPath'");
        }
        
        if (!is_writable($tempPath)) {
            
            throw new InvalidArgumentException("Directory '$tempPath' is not writeable.");
        }
        
        $this->outputDir = $tempPath;
    }
    
    /**
     * Get join files
     * @return bool
     */
    public function getJoinFiles() {
        
        return $this->joinFiles;
    }
    
    /**
     * Set join files
     * @param bool $joinFiles
     */
    public function setJoinFiles($joinFiles) {
        
        $this->joinFiles = (bool) $joinFiles;
    }
    
    /**
     * Set check last modified
     * @param bool $checkLastModified
     */
    public function setCheckLastModified($checkLastModified) {
        
        $this->checkLastModified = (bool) $checkLastModified;
    }
    
    /**
     * Casove razitko nejnovejsiho souboru
     * @param array $files
     * @return int
     */
    public function getLastModified(array $files = null) {
        
        if ($files === null) {
            
            $files = $this->collection->getFiles();
        }
        
        $modified = 0;
        
        foreach ($files as $file) {
            
            $modified = max($modified, filemtime($file));
        }
        
        return $modified;
    }
    
    /**
     * Vrati obsah vsech souboru v kolekci
     * @param array $files
     * @return string
     */
    public function getContent(array $files = null) {
        
        if ($files === null) {
            $files = $this->collection->getFiles();
        }
        
        // obsah
        $content = "";
        foreach ($files as $file) {
            
            $content .= PHP_EOL . $this->loadFile($file);
        }
        
        // filtry
        foreach ($this->filters as $filter) {
            
            $content = call_user_func($filter, $content, $this);
        }
        
        return $content;
    }
    
    /**
     * Nacist obsah a ulozit soubor
     * @param bool $ifModified
     * @return array
     */
    public function generate(bool $ifModified = true) {
        
        $files = $this->collection->getFiles();
        
        if (!count($files)) {
            
            return [];
        }
        
        if ($this->joinFiles) {
            
            $watch = ($this->checkLastModified ? array_unique(array_merge($files, $this->collection->getWatchFiles())) : []);
            
            return [
                
                $this->generateFiles($files, $ifModified, $watch)
            ];
            
        } else {
            
            $arr = [];
            
            foreach ($files as $file) {
                
                $watch = ($this->checkLastModified ? array_unique(array_merge([$file], $this->collection->getWatchFiles())) : []);               
                $arr[] = $this->generateFiles(array($file), $ifModified, $watch);
            }
            
            return $arr;
        }
    }
    
    /**
     * Generovani souboru
     * @param array $files
     * @param bool $ifModified
     * @param array $watchFiles
     * @return \StdClass
     */
    protected function generateFiles(array $files, bool $ifModified, array $watchFiles = array()) {
        
        $name = $this->namingConvention->getFilename($files, $this);
        $path = $this->outputDir . "/" . $name;
        $lastModified = $this->checkLastModified ? $this->getLastModified($watchFiles) : 0;
        
        if (!$ifModified || !file_exists($path) || $lastModified > filemtime($path)) {
            
            $outPath = in_array("safe", stream_get_wrappers()) ? "safe://" . $path : $path;
            
            file_put_contents($outPath, $this->getContent($files));
        }
        
        return (object) [
            "file" => $name,
            "path" => $this->outputDir,
            "time" => $lastModified,
            "source" => $files,
        ];
    }
    
    /**
     * Nacte soubor
     * @param string $file
     * @return string
     */
    protected function loadFile($file) {
        
        $content = file_get_contents($file);
        
        foreach ($this->fileFilters as $filter) {
            $content = call_user_func($filter, $content, $this, $file);
        }
        
        return $content;
    }
    
    /**
     * Soubory
     * @return \Lemonade\Assets\Interfaces\FileCollectionInterface
     */
    public function getFileCollection() {
        
        return $this->collection;
    }
    
    /**
     * Nazev
     * @return \Lemonade\Assets\Interfaces\NamingConventionInterface
     */
    public function getOutputNamingConvention() {
        
        return $this->namingConvention;
    }
    
    /**
     * 
     * @param FileCollectionInterface $collection
     */
    public function setFileCollection(FileCollectionInterface $collection) {
        
        $this->collection = $collection;
    }
    
    /**
     * 
     * @param NamingConventionInterface $namingConvention
     */
    public function setOutputNamingConvention(NamingConventionInterface $namingConvention) {
        
        $this->namingConvention = $namingConvention;
    }
    
    /**
     * Callbacky
     * @param callable $filter
     * @throws InvalidArgumentException
     */
    public function addFilter($filter) {
        
        if (!is_callable($filter)) {
            
            throw new InvalidArgumentException('Filter is not callable.');
        }
        
        $this->filters[] = $filter;
    }
        
    /**
     * Pridat filtr
     * @param callable $filter
     * @throws InvalidArgumentException
     */
    public function addFileFilter($filter) {
        
        if (!is_callable($filter)) {
            throw new InvalidArgumentException('Filter is not callable.');
        }
        
        $this->fileFilters[] = $filter;
    }
    
    /**
     * Vraci filtry
     * @return array
     */
    public function getFilters() {
        
        return $this->filters;
    }
    
    /**
     * @return array
     */
    public function getFileFilters() {
        
        return $this->fileFilters;
    }
    
}