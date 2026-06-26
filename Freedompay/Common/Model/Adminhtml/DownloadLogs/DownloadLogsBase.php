<?php

namespace Freedompay\Common\Model\Adminhtml\DownloadLogs;

use Exception;
use \Magento\Framework\Filesystem\Io\File;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class for handling zipping and downloading log file
 */
class DownloadLogsBase
{
    /**
     * @var FileFactory
     */
    private FileFactory $fileFactory;

    /**
     * @var File
     */
    private File $file;

    /**
     * @param File $file
     * @param FileFactory $fileFactory
     */
    public function __construct(
        File $file,
        FileFactory $fileFactory
    ) {
        $this->file = $file;
        $this->fileFactory = $fileFactory;
    }

    /**
     * Download corresponding log file as a zip file
     *
     * @param string $location
     * @return ResponseInterface
     * @throws LocalizedException
     */
    public function downloadFile(string $location): ResponseInterface
    {
        try {
            $content = [];
            $downloadedFileName = 'logfile.zip';
            $content['type'] = 'filename';
            $content['value'] = $location;
            $content['rm'] = 1;
            return $this->fileFactory->create($downloadedFileName, $content, DirectoryList::VAR_DIR);
        } catch (Exception $e) {
            $customExceptionMessage = 'Error while downloading file: ' . $e->getMessage();
                throw new LocalizedException(__($customExceptionMessage));
        }
    }

    /**
     * Create zip file
     *
     * @param string $location
     * @param string $destination
     * @return string
     */
    public function getZip(string $location, string $destination): string
    {
        $filename = $this->file->getPathInfo($location);
        $zip = new \ZipArchive();
        $zip->open($destination, \ZipArchive::CREATE);
        $zip->addFile(BP.$location, $filename['basename']);/* @phpstan-ignore-line */
        $zip->close();
        return $destination;
    }
}
