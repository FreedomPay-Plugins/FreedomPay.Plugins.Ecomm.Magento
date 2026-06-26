<?php
namespace Freedompay\Common\Controller\Process;

use Freedompay\Common\Model\MandatoryFieldValidation;
use Freedompay\Common\Model\Order\OrderManager;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;

/**
 * Class AddMandatoryFieldValidationComment - Add mandatory field missing comment after validation
 */
class AddMandatoryFieldValidationComment implements HttpPostActionInterface
{
    /**
     * @var OrderManager
     */
    private OrderManager $orderManager;

    /**
     * @var MandatoryFieldValidation
     */
    protected MandatoryFieldValidation $mandatoryFieldValidation;

    /**
     * @var JsonFactory
     */
    private JsonFactory $jsonFactory;

    /**
     * @param OrderManager $orderManager
     * @param MandatoryFieldValidation $mandatoryFieldValidation
     * @param JsonFactory $jsonFactory
     */
    public function __construct(
        OrderManager      $orderManager,
        MandatoryFieldValidation $mandatoryFieldValidation,
        JsonFactory $jsonFactory
    ) {
        $this->orderManager = $orderManager;
        $this->mandatoryFieldValidation = $mandatoryFieldValidation;
        $this->jsonFactory = $jsonFactory;
    }

    /**
     * Add order comment is validation error is present
     *
     * @return Json
     */
    public function execute(): Json
    {
        $lastOrderId = $this->orderManager->getLastOrderId();
        $missingFieldValidationData = $this->mandatoryFieldValidation->getMissingFieldDataFromSession();
        if (!empty($missingFieldValidationData)) {
            $missingFieldValidationComment = sprintf(
                'PayloadError.  Missing fields: %s',
                implode(', ', $missingFieldValidationData)
            );
            $this->orderManager->addOrderComment($lastOrderId, $missingFieldValidationComment);
            $this->mandatoryFieldValidation->clearMissingFieldDataFromSession();
        }

        $result = $this->jsonFactory->create();
        return $result->setData(['success' => true]);
    }
}
