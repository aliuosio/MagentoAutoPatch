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
     * Send E-Mail
     *
     * @param  string $version
     * @return bool
     */
    public function send(string $version): bool
    {
        try {
            $transport = $this->transportBuilder
                ->setTemplateIdentifier('patch_success')
                ->setTemplateOptions($this->getOptions())
                ->setTemplateVars($this->getVars($version))
                ->setFromByScope('general')
                ->addTo('admin@example.com')
                ->getTransport();

            $transport->sendMessage();
        } catch (LocalizedException|MailException $e) {
            $this->logger->error("Error sending success email: {$e->getMessage()}");
            return false;
        }
        return true;
    }

    /**
     * Get Options
     *
     * @return array
     */
    private function getOptions(): array
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
    private function getVars(string $version): array
    {
        return [
            'version' => $version,
            'update_time' => $this->timezone->date()->format('Y-m-d H:i:s'),
            'message' => 'Magento has been successfully updated with the latest patches.'
        ];
    }
}
