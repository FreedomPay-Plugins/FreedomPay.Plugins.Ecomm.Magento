<?php
namespace Freedompay\Common\Model\Config\Backend\Logos;

use Exception;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value as ConfigValue;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\SerializerInterface;
use Freedompay\Common\Model\ImageHandler;

/**
 * Config Model for multiple logos upload Field
 */
class MultipleImageUpload extends ConfigValue
{
    /**
     * Json Serializer
     *
     * @var SerializerInterface
     */
    protected SerializerInterface $serializer;

    /**
     * @var ImageHandler
     */
    protected ImageHandler $imageHandler;

    /**
     * ShippingMethods constructor
     *
     * @param SerializerInterface $serializer
     * @param ImageHandler $imageHandler
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array<mixed> $data
     */
    public function __construct(
        SerializerInterface $serializer,
        ImageHandler $imageHandler,
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->serializer = $serializer;
        $this->imageHandler = $imageHandler;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Prepare data before save
     *
     * @return MultipleImageUpload
     * @throws FileSystemException
     * @throws Exception
     */
    public function beforeSave(): MultipleImageUpload
    {
        /** @var array<mixed> $value */
        $value = $this->getValue();
        unset($value['__empty']);

        $encodedOldValue = [];
        $oldValue = $this->getOldValue();
        if ($oldValue) {
            $encodedOldValue = $this->serializer->unserialize($oldValue);
        }

        $deletedImages = [];
        if (isset($value['delete']) && $value['delete']) {
            foreach ($value['delete'] as $deleteImage) {
                $this->imageHandler->deleteImageFromMediaFolder($deleteImage);
                $deletedImages[] = $deleteImage;
            }
        }

        $images = [];
        if (isset($value['images']) && $value['images']) {
            foreach ($value['images'] as $image) {
                if ($image['name']) {
                    $uploadLogo = $this->imageHandler->saveImageToMediaFolder($image);
                    if (isset($uploadLogo['error']) && $uploadLogo['error']) {
                        throw new LocalizedException(
                            __('Invalid Logo file size/type.')
                        );
                    }
                    if (isset($uploadLogo['file']) && $uploadLogo['file']) {
                        $images[] = $uploadLogo['file'];
                    }
                }
            }
        }

        $encodedOldValue = array_values(array_diff((array)$encodedOldValue, $deletedImages));
        $imagesForInsert = array_merge($encodedOldValue, $images);
        $encodedValue = $this->serializer->serialize($imagesForInsert);
        $this->setValue((string)$encodedValue);

        return $this;
    }
}
