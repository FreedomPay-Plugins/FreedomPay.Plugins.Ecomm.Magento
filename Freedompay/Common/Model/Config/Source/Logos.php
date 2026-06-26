<?php
namespace Freedompay\Common\Model\Config\Source;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Module\Dir;

/**
 * Used in creating options for getting logos
 *
 */
class Logos
{
    /**
     * @var File
     */
    private File $filesystemDriver;

    /**
     * @var Dir
     */
    private Dir $moduleDir;

    /**
     * @param File $filesystemDriver
     * @param Dir $moduleDir
     */
    public function __construct(
        File $filesystemDriver,
        Dir $moduleDir
    ) {
        $this->filesystemDriver = $filesystemDriver;
        $this->moduleDir = $moduleDir;
    }

    /**
     * Options getter
     *
     * @return array<mixed>
     * @throws FileSystemException
     */
    public function toOptionArray():array
    {
        $data = [];
        $folderPath = $this->getFolderPath();
        $filesList = $this->filesystemDriver->readDirectory($folderPath);
        foreach ($filesList as $filePath) {
            //phpcs:ignore
            $filename = basename($filePath);
            $data[] = ['value' => $filename, 'label'=>__('')];
        }
        return $data;
    }

    /**
     * Get folder path of logos
     *
     * @return string
     */
    public function getFolderPath(): string
    {
        $moduleViewPath = $this->moduleDir->getDir('Freedompay_Common', Dir::MODULE_VIEW_DIR);
        return $moduleViewPath . '/base/web/images/logos';
    }
}
