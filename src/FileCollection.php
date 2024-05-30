<?php declare(strict_types=1);

namespace Lemonade\Assets;

use Lemonade\Assets\Exception\FileNotFoundException;
use Lemonade\Assets\Interfaces\FileCollectionInterface;
use SplFileInfo;

final class FileCollection implements FileCollectionInterface
{

    /**
     * Soubory
     * @var array
     */
    protected array $files = [];

    /**
     * Sledovane soubory
     * @var array
     */
    protected array $watchFiles = [];

    /**
     * @param string $root
     */
    public function __construct(protected readonly string $root)
    {
    }

    /**
     * @return string
     */
    public function getRoot(): string
    {

        return $this->root;
    }

    /**
     * @param iterable<SplFileInfo> $files
     * @return void
     */
    public function addWatchFiles(iterable $files): void
    {

        foreach ($files as $file) {

            $this->addWatchFile($file);
        }
    }

    /**
     * @param SplFileInfo $file
     * @return void
     */
    public function addWatchFile(SplFileInfo $file): void
    {

        if (!empty($file = $this->cannonicalizePath((string) $file))) {

            if (!in_array($file, $this->watchFiles, true)) {

                $this->watchFiles[] = $file;
            }
        }

    }

    /**
     * @return array
     */
    public function getWatchFiles(): array
    {

        return array_values($this->watchFiles);
    }

    /**
     * @return array
     */
    public function getFiles(): array
    {

        return array_values($this->files);
    }

    /**
     * @param array $files
     * @return void
     */
    public function addFiles(array $files = []): void
    {

        foreach ($files as $file) {

            $this->addFile($file);
        }
    }

    /**
     * @param string $file
     * @return void
     */
    public function addFile(string $file): void
    {

        if (!empty($file = $this->cannonicalizePath($file))) {

            if (!in_array($file, $this->files, true)) {

                $this->files[] = $file;
            }
        }
    }

    /**
     * @return void
     */
    public function clear(): void
    {

        $this->files = [];
        $this->watchFiles = [];

    }

    /**
     * @param string $path
     * @return string
     */
    public function cannonicalizePath(string $path): string
    {

        $rel = Path::normalize($this->root . "/" . $path);

        if (file_exists($rel)) {

            return $rel;
        }

        $abs = Path::normalize($path);

        if (file_exists($abs)) {

            return $abs;
        }

        return "";
    }
}