<?php

namespace Freedompay\HPP\Gateway\Validator;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;

/**
 * Class ResponseValidator
 * Freedompay Xml Response Validator
 */
class CreateVerificationResponseValidator extends AbstractValidator
{

    /**
     * Validates CreateVerificationResponse transaction response
     *
     * @param array<mixed> $validationSubject
     * @return ResultInterface
     */
    public function validate(array $validationSubject): ResultInterface
    {
        $response = SubjectReader::readResponse($validationSubject);
        if (!isset($response['CheckoutUrl'])) {
            return $this->createResult(
                false,
                [
                    __(
                        'Something went wrong while processing the CreateVerification API request.'
                    )
                ]
            );
        }
        return $this->createResult(true);
    }
}
