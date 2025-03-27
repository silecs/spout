<?php

namespace Box\Spout\Common\Helper;

use Box\Spout\Common\Exception\IOException;
use PHPUnit\Framework\TestCase;

class FileSystemHelperTest extends TestCase
{
    /** @var FileSystemHelper */
    protected $fileSystemHelper;

    public function setUp(): void
    {
        $baseFolder = \sys_get_temp_dir();
        $this->fileSystemHelper = new FileSystemHelper($baseFolder);
    }

    public function testCreateFolderShouldThrowExceptionIfOutsideOfBaseFolder(): void
    {
        $this->expectException(IOException::class);
        $this->expectExceptionMessage('Cannot perform I/O operation outside of the base folder');
        $this->fileSystemHelper->createFolder('/tmp/folder_outside_base_folder', 'folder_name');
    }

    public function testCreateFileWithContentsShouldThrowExceptionIfOutsideOfBaseFolder(): void
    {
        $this->expectException(IOException::class);
        $this->expectExceptionMessage('Cannot perform I/O operation outside of the base folder');
        $this->fileSystemHelper->createFileWithContents('/tmp/folder_outside_base_folder', 'file_name', 'contents');
    }

    public function testDeleteFileShouldThrowExceptionIfOutsideOfBaseFolder(): void
    {
        $this->expectException(IOException::class);
        $this->expectExceptionMessage('Cannot perform I/O operation outside of the base folder');
        $this->fileSystemHelper->deleteFile('/tmp/folder_outside_base_folder/file_name');
    }

    public function testDeleteFolderRecursivelyShouldThrowExceptionIfOutsideOfBaseFolder(): void
    {
        $this->expectException(IOException::class);
        $this->expectExceptionMessage('Cannot perform I/O operation outside of the base folder');

        $this->fileSystemHelper->deleteFolderRecursively('/tmp/folder_outside_base_folder');
    }
}
