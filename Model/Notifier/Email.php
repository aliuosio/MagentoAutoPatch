<?php declare(strict_types=1);

namespace Osio\MagentoAutoPatch\Model\Notifier;

use Magento\Framework\App\Area;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Osio\MagentoAutoPatch\Model\Logger\Log;
use Osio\MagentoMailAttachment\Model\TransportBuilder;
use Osio\MagentoMailAttachment\Model\TransportBuilderFactory;

class Email
{
    /**
     * @var TransportBuilderFactory
     */
    private TransportBuilderFactory $transportBuilderFactory;

    /**
     * @var Log
     */
    private Log $logger;

    /**
     * @var TimezoneInterface
     */
    private TimezoneInterface $timezone;

    /**
     * @var File
     */
    private File $file;

    /**
     * @param TransportBuilderFactory $transportBuilderFactory
     * @param Log                     $logger
     * @param TimezoneInterface       $timezone
     * @param File                    $file
     */
    public function __construct(
        TransportBuilderFactory $transportBuilderFactory,
        Log                     $logger,
        TimezoneInterface       $timezone,
        File                    $file
    ) {
        $this->transportBuilderFactory = $transportBuilderFactory;
        $this->logger = $logger;
        $this->timezone = $timezone;
        $this->file = $file;
    }

    /**
     * Send New Patch Info
     *
     * @param  string|null $current_version
     * @param  string|null $new_version
     * @param  string|null $notificationEmail
     * @return bool
     */
    public function sendNewPatchInfo(?string $current_version, ?string $new_version, ?string $notificationEmail): bool
    {
        return $this->sendEmail(
            'patch_new',
            ['current_version' => $current_version, 'new_version' => $new_version],
            $notificationEmail
        );
    }

    /**
     * Send E-Mail after patch
     *
     * @param  bool        $isRunnerSuccess
     * @param  string      $previous_version
     * @param  string      $new_version
     * @param  string|null $notificationEmail
     * @return bool
     */
    public function sendAfterPatch(
        bool    $isRunnerSuccess,
        string  $previous_version,
        string  $new_version,
        ?string $notificationEmail
    ): bool {
        return $this->sendEmail(
            $this->getAfterPatchTemplate($isRunnerSuccess),
            $this->getSuccessVars($previous_version, $new_version),
            $notificationEmail
        );
    }

    /**
     * Send an E-Mail with a specific template and variables
     *
     * @param  string      $templateId
     * @param  array       $templateVars
     * @param  string|null $recipientEmail
     * @return bool
     */
    public function sendEmail(string $templateId, array $templateVars, ?string $recipientEmail): bool
    {
        try {
            $transport = $this->prepareTransport($templateId, $templateVars, $recipientEmail);
            $this->addAttachmentIfNecessary($transport, $templateId);
            $transport->getTransport()->sendMessage();
        } catch (MailException|LocalizedException $e) {
            $this->logger->error("Error sending email: {$e->getMessage()}");
            return false;
        }

        return true;
    }

    /**
     * Prepare the transport for sending the email
     *
     * @param  string      $templateId
     * @param  array       $templateVars
     * @param  string|null $recipientEmail
     * @return TransportBuilder|null
     */
    private function prepareTransport(
        string  $templateId,
        array   $templateVars,
        ?string $recipientEmail
    ): ?TransportBuilder {
        try {
            $transportBuilder = $this->transportBuilderFactory->create();
            return $transportBuilder
                ->setTemplateIdentifier($templateId)
                ->setTemplateOptions($this->getAreaAndStoreVars())
                ->setTemplateVars($templateVars)
                ->setFromByScope('general')
                ->addTo($recipientEmail);
        } catch (MailException $e) {
            $this->logger->error("Error preparing email transport: {$e->getMessage()}");
            return null;
        }
    }

    /**
     * Add attachment if the template ID requires it
     *
     * @param  TransportBuilder $transport
     * @param  string           $templateId
     * @return void
     */
    private function addAttachmentIfNecessary(TransportBuilder $transport, string $templateId): void
    {
        try {
            if ($templateId === 'patch_failure') {
                $logFilePath = BP . '/var/log/auto-patch.log';
                $content = $this->file->fileGetContents($logFilePath);
                $fileName = 'auto-patch.log';
                $fileType = 'text/plain';

                $attachmentAdded = $transport->addAttachment($content, $fileName, $fileType) ? 'Yes' : 'No';
                $this->logger->info("Attachment added: $attachmentAdded");
            }
        } catch (FileSystemException $e) {
            $this->logger->critical($e->getMessage(), ['code' => $e->getCode()]);
        }
    }

    /**
     * Get After Patch Template
     *
     * @param  bool $runnerSuuccess
     * @return string
     */
    private function getAfterPatchTemplate(bool $runnerSuuccess): string
    {
        return ($runnerSuuccess) ? 'patch_success' : 'patch_failure';
    }

    /**
     * Get Options
     *
     * @return array
     */
    private function getAreaAndStoreVars(): array
    {
        return [
            'area' => Area::AREA_FRONTEND,
            'store' => 1
        ];
    }

    /**
     * Get Variables
     *
     * @param  string $previous_version
     * @param  string $new_version
     * @return array
     */
    private function getSuccessVars(string $previous_version, string $new_version): array
    {
        return [
            'previous_version' => $previous_version,
            'new_version' => $new_version,
            'update_time' => $this->timezone->date()->format('Y-m-d H:i:s')
        ];
    }
}
