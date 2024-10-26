<?php declare(strict_types=1);
/**
 * @author    Osiozekhai Aliu
 * @package   Osio_MagentoAutoPatch
 * @copyright Copyright (c) 2024 Osio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osio\MagentoAutoPatch\Cron;

use Osio\MagentoAutoPatch\Helper\Data;
use Osio\MagentoAutoPatch\Model\Composer;
use Osio\MagentoAutoPatch\Model\Logger\Log;
use Osio\MagentoAutoPatch\Model\Notifier\Email;

class NewPatchNotification
{
    /**
     * @var Log
     */
    protected Log $logger;

    /**
     * @var Data
     */
    private Data $helper;

    /**
     * @var Email
     */
    private Email $email;

    /**
     * @var Composer
     */
    private Composer $composer;

    /**
     * @param Log      $logger
     * @param Data     $helper
     * @param Email    $email
     * @param Composer $composer
     */
    public function __construct(
        Log      $logger,
        Data     $helper,
        Email    $email,
        Composer $composer
    ) {
        $this->logger = $logger;
        $this->helper = $helper;
        $this->email = $email;
        $this->composer = $composer;
    }

    /**
     * Execute
     *
     * @return void
     */
    public function execute()
    {
        if ($this->helper->isEnabled() && $this->helper->notifyBefore()) {
            if ($this->composer->getLatest()) {
                $this->logger->info(self::class . " ran successfully");
            }
            if ($this->setNotifiction()) {
                $this->logger->info(self::class . " E-Mail sent");
            }
        }
    }

    /**
     * Set Notifiction
     *
     * @return bool
     */
    private function setNotifiction(): bool
    {
        return $this->email->sendNewPatchInfo(
            $this->composer->getLatest(),
            $this->helper->getNotifyBeforeEmail()
        );
    }
}
