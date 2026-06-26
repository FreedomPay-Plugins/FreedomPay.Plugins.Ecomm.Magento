<?php
namespace Freedompay\Common\Gateway\Command;

use Magento\Payment\Gateway\Command\CommandException;
use Magento\Payment\Gateway\Command\ResultInterface as CommandResultInterface;
use Magento\Payment\Gateway\ErrorMapper\ErrorMessageMapperInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Http\ClientException;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\ConverterException;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Payment\Gateway\Validator\ValidatorInterface;
use Freedompay\Common\Gateway\Http\TransferFactoryInterface;
use Freedompay\Common\Logger\Logger;

/**
 * GatewayCommand for payment
 */
class GatewayCommand extends AbstractGatewayCommand
{
    /**
     * @var BuilderInterface
     */
    protected BuilderInterface $requestBuilder;

    /**
     * @var TransferFactoryInterface
     */
    protected TransferFactoryInterface $transferFactory;

    /**
     * @var ClientInterface
     */
    protected ClientInterface $client;

    /**
     * @var HandlerInterface|null
     */
    protected ?HandlerInterface $handler;

    /**
     * @var ValidatorInterface|null
     */
    protected ?ValidatorInterface $validator;

    /**
     * @param BuilderInterface $requestBuilder
     * @param TransferFactoryInterface $transferFactory
     * @param ClientInterface $client
     * @param Logger $logger
     * @param HandlerInterface|null $handler
     * @param ValidatorInterface|null $validator
     * @param ErrorMessageMapperInterface|null $errorMessageMapper
     */
    public function __construct(
        BuilderInterface $requestBuilder,
        TransferFactoryInterface $transferFactory,
        ClientInterface $client,
        Logger $logger,
        ?HandlerInterface $handler = null,
        ?ValidatorInterface $validator = null,
        ?ErrorMessageMapperInterface $errorMessageMapper = null
    ) {
        $this->requestBuilder = $requestBuilder;
        $this->transferFactory = $transferFactory;
        parent::__construct(
            $client,
            $logger,
            $handler,
            $validator,
            $errorMessageMapper
        );
    }

    /**
     * Executes command based on business object
     *
     * @param array<mixed> $commandSubject
     * @return array<mixed>|CommandResultInterface|null
     * @throws ClientException
     * @throws CommandException
     * @throws ConverterException
     */
    public function execute(array $commandSubject): array|CommandResultInterface|null
    {
        $payment = SubjectReader::readPayment($commandSubject);

        $transferO = $this->transferFactory->create(
            $this->requestBuilder->build($commandSubject),
            $payment
        );

        return $this->processRequest($commandSubject, $transferO);
    }
}
