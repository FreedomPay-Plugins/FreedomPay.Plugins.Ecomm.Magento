<?php
namespace Freedompay\Common\Model;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Handles image upload functionality
 */
class ImageHandler
{
    public const MAX_FILE_SIZE = 100000;

    /**
     * @var UploaderFactory
     */
    private UploaderFactory $uploaderFactory;

    /**
     * @var Filesystem
     */
    private Filesystem $filesystem;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var string
     */
    public const FILE_DIR = 'freedompay/logo';

    /**
     * @var int
     */
    protected int $imageFileSize = 0;

    /**
     * @var array|string[]
     */
    protected array $allowedImageExtensions = ['jpg', 'jpeg', 'png'];

    /**
     * @param UploaderFactory $uploaderFactory
     * @param Filesystem $filesystem
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        UploaderFactory $uploaderFactory,
        Filesystem $filesystem,
        StoreManagerInterface $storeManager
    ) {
        $this->uploaderFactory = $uploaderFactory;
        $this->storeManager = $storeManager;
        $this->filesystem = $filesystem;
    }

    /**
     * Save file to temp media directory
     *
     * @param array<mixed> $fileId
     * @return array<mixed>
     */
    public function saveImageToMediaFolder(array $fileId): array
    {
        try {
            $result = ['file' => '', 'size' => ''];
            $mediaDirectory = $this->filesystem
                ->getDirectoryRead(DirectoryList::MEDIA)
                ->getAbsolutePath(self::FILE_DIR);
            $uploader = $this->uploaderFactory->create(['fileId' => $fileId]);
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(false);
            $uploader->setAllowedExtensions($this->getAllowedExtensions());
            $this->imageFileSize = $fileId['size'];
            $uploader->addValidateCallback('size', $this, 'validateMaxSize');
            $result = array_intersect_key($uploader->save($mediaDirectory), $result);
        } catch (\Exception $e) {
                $result = ['error' => $e->getMessage(), 'errorCode' => $e->getCode()];
        }
        return $result;
    }

    /**
     * Delete image
     *
     * @param string $file
     * @return bool
     * @throws FileSystemException
     */
    public function deleteImageFromMediaFolder(string $file): bool
    {
        $mediaDirectory = $this->filesystem
            ->getDirectoryWrite(DirectoryList::MEDIA);
        return $mediaDirectory->delete(self::FILE_DIR.'/'. $file);
    }

    /**
     * Get file url
     *
     * @param string $file
     * @return string
     * @throws NoSuchEntityException
     */
    public function getMediaUrl(string $file): string
    {
        $file = ltrim(str_replace('\\', '/', $file), '/');

        /** @var Store $storeManager */
        $storeManager = $this->storeManager->getStore();
        return $storeManager->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . self::FILE_DIR . '/' . $file;
    }

    /**
     * Getter for allowed extensions of uploaded files.
     *
     * @return string[]
     */
    protected function getAllowedExtensions(): array
    {
        return $this->allowedImageExtensions;
    }

    /**
     * Validation callback for checking max file size
     *
     * @param string $filePath Path to temporary uploaded file
     * @return void
     * @throws LocalizedException
     */
    public function validateMaxSize(string $filePath): void
    {
        $directory = $this->filesystem->getDirectoryRead(Filesystem\DirectoryList::SYS_TMP);
        if ($directory->stat($directory->getRelativePath($filePath))['size'] > self::MAX_FILE_SIZE) {
            throw new LocalizedException(
                __('Invalid Logo file size/type.')
            );
        }
    }
}
