<?php declare(strict_types=1);
/**
 * @author    Osiozekhai Aliu
 * @package   Osio_MagentoAutoPatch
 * @copyright Copyright (c) 2024 Osio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osio\MagentoAutoPatch\Model\Notifier;

use Laminas\Mime\Mime;
use Laminas\Mime\PartFactory;
use Magento\Framework\App\TemplateTypesInterface;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Mail\AddressConverter;
use Magento\Framework\Mail\EmailMessageInterfaceFactory;
use Magento\Framework\Mail\Exception\InvalidArgumentException;
use Magento\Framework\Mail\MessageInterface;
use Magento\Framework\Mail\MessageInterfaceFactory;
use Magento\Framework\Mail\MimeInterface;
use Magento\Framework\Mail\MimeMessageInterfaceFactory;
use Magento\Framework\Mail\MimePartInterfaceFactory;
use Magento\Framework\Mail\Template\FactoryInterface;
use Magento\Framework\Mail\Template\SenderResolverInterface;
use Magento\Framework\Mail\Template\TransportBuilder as TransportBuilderAlias;
use Magento\Framework\Mail\TemplateInterface;
use Magento\Framework\Mail\TransportInterface;
use Magento\Framework\Mail\TransportInterfaceFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Escaper;
use Osio\MagentoAutoPatch\Model\Logger\Log;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TransportBuilder extends TransportBuilderAlias
{

    /**
     * @var array
     */
    private array $messageData = [];

    /**
     * @var EmailMessageInterfaceFactory
     */
    private $emailMessageInterfaceFactory;

    /**
     * @var MimeMessageInterfaceFactory
     */
    private $mimeMessageInterfaceFactory;

    /**
     * @var MimePartInterfaceFactory
     */
    private $mimePartInterfaceFactory;

    /**
     * @var AddressConverter|null
     */
    private $addressConverter;

    /**
     * @var array
     */
    protected array $attachments = [];

    /**
     * @var PartFactory|mixed
     */
    protected PartFactory $partFactory;

    /**
     * @var Escaper
     */
    private Escaper $escaper;

    /**
     * @var Log
     */
    private Log $logger;

    /**
     * @param Log                               $logger
     * @param Escaper                           $escaper
     * @param FactoryInterface                  $templateFactory
     * @param MessageInterface                  $message
     * @param SenderResolverInterface           $senderResolver
     * @param ObjectManagerInterface            $objectManager
     * @param TransportInterfaceFactory         $mailTransportFactory
     * @param MessageInterfaceFactory|null      $messageFactory
     * @param EmailMessageInterfaceFactory|null $emailMessageInterfaceFactory
     * @param MimeMessageInterfaceFactory|null  $mimeMessageInterfaceFactory
     * @param MimePartInterfaceFactory|null     $mimePartInterfaceFactory
     * @param AddressConverter|null             $addressConverter
     */
    public function __construct(
        Log                          $logger,
        Escaper                      $escaper,
        FactoryInterface             $templateFactory,
        MessageInterface             $message,
        SenderResolverInterface      $senderResolver,
        ObjectManagerInterface       $objectManager,
        TransportInterfaceFactory    $mailTransportFactory,
        MessageInterfaceFactory      $messageFactory = null,
        EmailMessageInterfaceFactory $emailMessageInterfaceFactory = null,
        MimeMessageInterfaceFactory  $mimeMessageInterfaceFactory = null,
        MimePartInterfaceFactory     $mimePartInterfaceFactory = null,
        AddressConverter             $addressConverter = null
    ) {
        parent::__construct(
            $templateFactory,
            $message,
            $senderResolver,
            $objectManager,
            $mailTransportFactory,
            $messageFactory,
            $emailMessageInterfaceFactory,
            $mimeMessageInterfaceFactory,
            $mimePartInterfaceFactory,
            $addressConverter
        );
        $this->templateFactory = $templateFactory;
        $this->objectManager = $objectManager;
        $this->_senderResolver = $senderResolver;
        $this->mailTransportFactory = $mailTransportFactory;
        $this->emailMessageInterfaceFactory = $emailMessageInterfaceFactory ?: $this->objectManager
            ->get(EmailMessageInterfaceFactory::class);
        $this->mimeMessageInterfaceFactory = $mimeMessageInterfaceFactory ?: $this->objectManager
            ->get(MimeMessageInterfaceFactory::class);
        $this->mimePartInterfaceFactory = $mimePartInterfaceFactory ?: $this->objectManager
            ->get(MimePartInterfaceFactory::class);
        $this->addressConverter = $addressConverter ?: $this->objectManager
            ->get(AddressConverter::class);
        $this->partFactory = $objectManager->get(PartFactory::class);
        $this->escaper = $escaper;
        $this->logger = $logger;
    }

    /**
     * Add cc address
     *
     * @param  array|string $address
     * @param  string       $name
     * @return $this
     */
    public function addCc($address, $name = ''): TransportBuilder
    {
        $this->addAddressByType('cc', $address, $name);

        return $this;
    }

    /**
     * Add to address
     *
     * @param  array|string $address
     * @param  string       $name
     * @return $this
     * @throws InvalidArgumentException
     */
    public function addTo($address, $name = ''): TransportBuilder
    {
        $this->addAddressByType('to', $address, $name);

        return $this;
    }

    /**
     * Add bcc address
     *
     * @param  array|string $address
     * @return $this
     * @throws InvalidArgumentException
     */
    public function addBcc($address): TransportBuilder
    {
        $this->addAddressByType('bcc', $address);

        return $this;
    }

    /**
     * Set Reply-To Header
     *
     * @param  string      $email
     * @param  string|null $name
     * @return $this
     * @throws InvalidArgumentException
     */
    public function setReplyTo($email, $name = null): TransportBuilder
    {
        $this->addAddressByType('replyTo', $email, $name);

        return $this;
    }

    /**
     * Set mail from address
     *
     * @param      string|array $from
     * @return     $this
     * @throws     InvalidArgumentException|MailException
     * @see        setFromByScope()
     * @deprecated 102.0.1 This function sets the from address but does not provide
     * a way of setting the correct from addresses based on the scope.
     */
    public function setFrom($from): TransportBuilder
    {
        return $this->setFromByScope($from);
    }

    /**
     * Set mail from address by scopeId
     *
     * @param  string|array $from
     * @param  string|int   $scopeId
     * @return $this
     * @throws InvalidArgumentException
     * @throws MailException
     * @since  102.0.1
     */
    public function setFromByScope($from, $scopeId = null): TransportBuilder
    {
        $result = $this->_senderResolver->resolve($from, $scopeId);
        $this->addAddressByType('from', $result['email'], $result['name']);

        return $this;
    }

    /**
     * Set template identifier
     *
     * @param  string $templateIdentifier
     * @return $this
     */
    public function setTemplateIdentifier($templateIdentifier): TransportBuilder
    {
        $this->templateIdentifier = $templateIdentifier;

        return $this;
    }

    /**
     * Set template model
     *
     * @param  string $templateModel
     * @return $this
     */
    public function setTemplateModel($templateModel): TransportBuilder
    {
        $this->templateModel = $templateModel;
        return $this;
    }

    /**
     * Set template vars
     *
     * @param  array $templateVars
     * @return $this
     */
    public function setTemplateVars($templateVars): TransportBuilder
    {
        $this->templateVars = $templateVars;

        return $this;
    }

    /**
     * Set template options
     *
     * @param  array $templateOptions
     * @return $this
     */
    public function setTemplateOptions($templateOptions): TransportBuilder
    {
        $this->templateOptions = $templateOptions;

        return $this;
    }

    /**
     * Get mail transport
     *
     * @return TransportInterface
     */
    public function getTransport(): TransportInterface
    {
        try {
            $this->prepareMessage();
            $mailTransport = $this->mailTransportFactory->create(['message' => clone $this->message]);
        } finally {
            $this->reset();
        }

        return $mailTransport;
    }

    /**
     * Reset object state
     *
     * @return $this
     */
    protected function reset(): TransportBuilder
    {
        $this->messageData = [];
        $this->templateIdentifier = null;
        $this->templateVars = null;
        $this->templateOptions = null;
        return $this;
    }

    /**
     * Get template
     *
     * @return TemplateInterface
     */
    protected function getTemplate(): TemplateInterface
    {
        return $this->templateFactory->get($this->templateIdentifier, $this->templateModel)
            ->setVars($this->templateVars)
            ->setOptions($this->templateOptions);
    }

    /**
     * Get Template Type
     *
     * @param  TemplateInterface $template
     * @return string
     */
    private function getTemplateType(TemplateInterface $template): ?string
    {
        switch ($template->getType()) {
        case TemplateTypesInterface::TYPE_TEXT:
            return MimeInterface::TYPE_TEXT;
        case TemplateTypesInterface::TYPE_HTML:
            return MimeInterface::TYPE_HTML;
        }

        $this->logger->error('Unknown template type: ' . $template->getType());

        return null;
    }

    /**
     * Prepare message.
     *
     * @return $this
     */
    protected function prepareMessage(): TransportBuilder
    {
        $template = $this->getTemplate();
        $content = $template->processTemplate();

        $mimePart = $this->mimePartInterfaceFactory->create(
            ['content' => $content, 'type' => $this->getTemplateType($template)]
        );
        $parts = count($this->attachments) ? array_merge([$mimePart], $this->attachments) : [$mimePart];
        $this->messageData['body'] = $this->mimeMessageInterfaceFactory->create(
            ['parts' => $parts]
        );

        $this->messageData['subject'] = $this->escaper->escapeHtml($template->getSubject());
        $this->message = $this->emailMessageInterfaceFactory->create($this->messageData);

        return $this;
    }

    /**
     * Handles possible incoming types of email (string or array)
     *
     * @param  string       $addressType
     * @param  string|array $email
     * @param  string|null  $name
     * @return void
     * @throws InvalidArgumentException
     */
    private function addAddressByType(string $addressType, $email, ?string $name = null): void
    {
        if (is_string($email)) {
            $this->messageData[$addressType][] = $this->addressConverter->convert($email, $name);
            return;
        }

        $convertedAddressArray = $this->addressConverter->convertMany($email);
        $this->messageData[$addressType] = array_merge($this->messageData[$addressType] ?? [], $convertedAddressArray);
    }

    /**
     * Add Attachment
     *
     * @param  string|null $content
     * @param  string|null $fileName
     * @param  string|null $fileType
     * @return TransportBuilder
     */
    public function addAttachment(?string $content, ?string $fileName, ?string $fileType): TransportBuilder
    {
        $attachmentPart = $this->partFactory->create();
        $attachmentPart->setContent($content)
            ->setType($fileType)
            ->setFileName($fileName)
            ->setDisposition(Mime::DISPOSITION_ATTACHMENT)
            ->setEncoding(Mime::ENCODING_BASE64);
        $this->attachments[] = $attachmentPart;

        return $this;
    }
}
