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

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\App\Area;
use Osio\MagentoAutoPatch\Model\Logger\Log;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class Email
{

    /**
     * @var TransportBuilder
     */
    private TransportBuilder $transportBuilder;

    /**
     * @var Log
     */
    private Log $logger;

    /**
     * @var TimezoneInterface
     */
    private TimezoneInterface $timezone;

    /**
     * @param TransportBuilder  $transportBuilder
     * @param Log               $logger
     * @param TimezoneInterface $timezone
     */
    public function __construct(
        TransportBuilder  $transportBuilder,
        Log               $logger,
        TimezoneInterface $timezone
    ) {
        $this->transportBuilder = $transportBuilder;
        $this->logger = $logger;
        $this->timezone = $timezone;
    }

    /**
     * Send E-Mail after patch
     *
     * @param  bool        $isRunnerSuccess
     * @param  string      $version
     * @param  string|null $notificationEmail
     * @return bool
     */
    public function sendAfterPatch(bool $isRunnerSuccess, string $version, ?string $notificationEmail): bool
    {
        return $this->sendEmail(
            $this->getAfterPatchTemplate($isRunnerSuccess),
            $this->getSuccessVars($version),
            $notificationEmail
        );
    }

    /**
     * Send New Patch Info
     *
     * @param  string|null $latestVersion
     * @param  string|null $notificationEmail
     * @return bool
     */
    public function sendNewPatchInfo(?string $latestVersion, ?string $notificationEmail): bool
    {
        return $this->sendEmail(
            'patch_new',
            ['version' => $latestVersion],
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
    private function sendEmail(string $templateId, array $templateVars, ?string $recipientEmail): bool
    {
        try {
            $transport = $this->transportBuilder
                ->setTemplateIdentifier($templateId)
                ->setTemplateOptions($this->getAreaAndStoreVars())
                ->setTemplateVars($templateVars)
                ->setFromByScope('general')
                ->addTo($recipientEmail)
                ->getTransport();

            $transport->sendMessage();
        } catch (LocalizedException|MailException $e) {
            $this->logger->error("Error sending email: {$e->getMessage()}");
            return false;
        }
        return true;
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
     * @param  string $version
     * @return array
     */
    private function getSuccessVars(string $version): array
    {
        return [
            'version' => $version,
            'update_time' => $this->timezone->date()->format('Y-m-d H:i:s'),
        ];
    }
}
