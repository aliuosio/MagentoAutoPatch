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

use Magento\Framework\Exception\FileSystemException;
use Osio\MagentoAutoPatch\Helper\Data;
use Osio\MagentoAutoPatch\Model\Composer;
use Osio\MagentoAutoPatch\Model\Logger\Log;
use Osio\MagentoAutoPatch\Model\Magento;
use Osio\MagentoAutoPatch\Model\Notifier\Email;

class UpdatePatches
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
     * @var Magento
     */
    private Magento $magento;

    /**
     * @param Log      $logger
     * @param Data     $helper
     * @param Email    $email
     * @param Composer $composer
     * @param Magento  $magento
     */
    public function __construct(
        Log      $logger,
        Data     $helper,
        Email    $email,
        Composer $composer,
        Magento  $magento
    ) {
        $this->logger = $logger;
        $this->helper = $helper;
        $this->email = $email;
        $this->composer = $composer;
        $this->magento = $magento;
    }

    /**
     * Execute
     *
     * @return void
     * @throws FileSystemException
     */
    public function execute()
    {
        if ($this->helper->isEnabled() && $this->helper->hasAutoUpdateEnabled()) {
            $runnerSuuccess = false;
            if ($this->runner()) {
                $this->logger->info(self::class . " ran successfully");
                $runnerSuuccess = true;
            }
            if ($this->setNotifiction($runnerSuuccess)) {
                $this->logger->info(self::class . " E-Mail sent");
            }
        }
    }

    /**
     * Set Notifiction
     *
     * @param  bool $runnerSuuccess
     * @return bool
     * @throws FileSystemException
     */
    private function setNotifiction(bool $runnerSuuccess): bool
    {
        if ($this->helper->notifyAfter()) {
            return $this->email->sendAfterPatch(
                $runnerSuuccess,
                $this->composer->getVersion(),
                $this->helper->getNotifyAfterEmail()
            );
        }

        return false;
    }

    /**
     * Runnner
     *
     * @return bool
     * @throws FileSystemException
     */
    private function runner(): bool
    {
        if ($this->composer->getLatest() === null) {
            return false;
        }

        foreach ($this->helper->resetCommands($this->magento->getDeployMode()) as $method => $messages) {
            if (!$this->processCommands($method)) {
                $this->logger->error($messages['errorMessage']);
                return false;
            }
        }

        return true;
    }

    /**
     * Process Commands
     *
     * @param  string $method
     * @return bool
     */
    private function processCommands(
        string $method
    ): bool {
        if ((method_exists($this->composer, $method) && $this->composer->$method())
            || (method_exists($this->magento, $method) && $this->magento->$method())
        ) {
            return true;
        }

        return false;
    }
}
