<?php
namespace Freedompay\Common\Block\Adminhtml\Form\Field\Logos;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Math\Random;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use Magento\Framework\Serialize\SerializerInterface;
use Freedompay\Common\Model\ImageHandler;

/**
 * Admin template block for uploading multiple logos
 */
class Upload extends AbstractElement
{
    /**
     * @var SerializerInterface
     */
    protected SerializerInterface $serializer;

    /**
     * @var ImageHandler
     */
    protected ImageHandler $imageHandler;

    /**
     * @param SerializerInterface $serializer
     * @param ImageHandler $imageHandler
     * @param Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param array<mixed> $data
     * @param SecureHtmlRenderer|null $secureRenderer
     * @param Random|null $random
     */
    public function __construct(
        SerializerInterface $serializer,
        ImageHandler $imageHandler,
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        $data = [],
        ?SecureHtmlRenderer $secureRenderer = null,
        ?Random $random = null
    ) {
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data, $secureRenderer, $random);
        $this->serializer = $serializer;
        $this->imageHandler = $imageHandler;
    }

    /**
     * Custom template for logo image field
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getElementHtml(): string
    {
        $cardLogos = $this->getCardLogos();

        /** @phpstan-ignore-next-line */
        $existingData = $this->getValue();
        $blockData = [];

        if ($existingData) {
            $existingData = (array)$this->serializer->unserialize($existingData);
            foreach ($existingData as $key => $data) {
                $blockData[$key]['image_full'] = $this->imageHandler->getMediaUrl($data);
                $blockData[$key]['image'] = $data;
            }
        }

        $data = [
            'images' => $this->serializer->serialize($blockData)
        ];
        return $cardLogos->setData($data)->toHtml();
    }

    /**
     * Class to override logo block class
     *
     * @return mixed
     */
    public function getCardLogos(): mixed
    {
        return true;
    }
}
