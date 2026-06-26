<?php

namespace Freedompay\HPP\Controller\Adminhtml\DownloadLogs;

use Freedompay\Common\Logger\Handler;
use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Freedompay\Common\Model\Adminhtml\DownloadLogs\DownloadLogsBase;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Filesystem\DirectoryList;

/**
 *  Controller for downloading Freedompay logs
 */
class Download extends Action
{
    /**
     * @var DownloadLogsBase
     */
    private DownloadLogsBase $downloadLogBase;

    /**
     * @var Handler
     */
    private Handler $handler;

    /**
     * @var DirectoryList
     */
    private DirectoryList $dirList;

    /**
     * @param Handler $handler
     * @param DownloadLogsBase $downloadLogBase
     * @param Context $context
     * @param DirectoryList $dirList
     */
    public function __construct(
        Handler  $handler,
        DownloadLogsBase $downloadLogBase,
        Context          $context,
        DirectoryList $dirList
    ) {
        $this->downloadLogBase = $downloadLogBase;
        parent::__construct($context);
        $this->handler = $handler;
        $this->dirList = $dirList;
    }

    /**
     * Downloads the zipped log file
     *
     * @return ResponseInterface|ResultInterface
     * @throws Exception
     */
    public function execute(): ResultInterface|ResponseInterface
    {
        try {
            $location = $this->handler->getLogLocation();
            $destination = $this->dirList->getPath('log').'/freedompay_log.zip';
            $zipFileLocation = $this->downloadLogBase->getZip($location, $destination);
            if ($zipFileLocation) {
                return $this->downloadLogBase->downloadFile($zipFileLocation);
            }
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('Something went wrong while downloading the log .
                Log files might not be generated yet. ') .
                ' ' .
                $e->getMessage()
            );
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setRefererOrBaseUrl();
        return $resultRedirect;
    }
}
