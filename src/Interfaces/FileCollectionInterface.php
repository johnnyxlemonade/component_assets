<?php declare(strict_types=1);

namespace Lemonade\Assets\Interfaces;

use SplFileInfo;
use Traversable;

interface FileCollectionInterface
{

    /**
     * @return string
     */
    public function getRoot(): string;

    /**
     * @return void
     */
    public function clear(): void;

    /**
     * @return mixed
     */
    public function getFiles(): array;

    /**
     * @param iterable<SplFileInfo> $files
     * @return void
     */
    public function addWatchFiles(iterable $files): void;

    /**
     * @param SplFileInfo $file
     * @return void
     */
    public function addWatchFile(SplFileInfo $file): void;

    /**
     * @return array
     */
    public function getWatchFiles(): array;

    /**
     * @param string $file
     * @return void
     */
    public function addFile(string $file): void;

    /**
     * @param array $files
     * @return void
     */
    public function addFiles(array $files = []): void;

}