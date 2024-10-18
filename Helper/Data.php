<?php declare(strict_types=1);

/**
 * @author    Osiozekhai Aliu
 * @package   Osio_MagentoAutoPatch
 * @copyright Copyright (c) 2024 Osio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osio\MagentoAutoPatch\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{

    private const ENABLED = 'autopatch/general/enabled';
    private const NOTIFY_BEFORE = 'autopatch/settings/notify/before';
    private const NOTIFY_AFTER = 'autopatch/settings/notify/after';
    private const AUTO = 'autopatch/settings/automatic';
    private const COMMNANDS = 'autopatch/commands';
    private const QUESTION = 'autopatch/question';
    private const CHECK_MESG = 'autopatch/process/check';
    private const NOT_ENABLED = 'autopatch/not_enabled';
    private const MINOR_VERSION = 'autopatch/minor_version';
    private const CURRENT_VERSION = 'autopatch/current_version';
    private const UPDATE_AVAIABLE = 'autopatch/update_avaiable';
    private const IS_UP_TO_DATE = 'autopatch/is_up_to_data';
    private const COMMAND_UPDATE = 'autopatch/command/update';
    private const COMMAND_DESC = 'autopatch/command/description';
    private const SUCCESS = 'autopatch/success';
    private const ERROR = 'autopatch/error';
    private const PROD_MESG = 'autopatch/production_mode_mesg';

    /**
     * Check if module enabled
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return (bool)$this->scopeConfig->getValue(Data::ENABLED);
    }

    /**
     * Check if module enabled
     *
     * @return bool
     */
    public function notEnabled(): bool
    {
        return (bool)$this->scopeConfig->getValue(Data::NOT_ENABLED);
    }

    /**
     * Notify before
     *
     * @return bool
     */
    public function notifyBefore(): bool
    {
        return (bool)$this->scopeConfig->getValue(Data::NOTIFY_BEFORE);
    }

    /**
     * Notify after
     *
     * @return bool
     */
    public function notifyAfter(): bool
    {
        return (bool)$this->scopeConfig->getValue(Data::NOTIFY_AFTER);
    }

    /**
     * Get update steps configuration from config.xml
     *
     * @return array
     */
    public function getCommands(): array
    {
        return $this->scopeConfig->getValue(Data::COMMNANDS);
    }

    /**
     * Get update steps configuration from config.xml
     *
     * @return string|null
     */
    public function getQuestion(): ?string
    {
        return $this->scopeConfig->getValue(Data::QUESTION);
    }

    /**
     * Get Check Message
     *
     * @return string|null
     */
    public function getCheckMessage(): ?string
    {
        return $this->scopeConfig->getValue(Data::CHECK_MESG);
    }

    /**
     * Get Minor Version
     *
     * @param  string $string
     * @return string|null
     */
    public function getMinorVersion(string $string): ?string
    {
        return sprintf($this->scopeConfig->getValue(Data::MINOR_VERSION), $string);
    }

    /**
     * Get Current Version
     *
     * @param  string $string
     * @return string|null
     */
    public function getCurrentVersion(string $string): ?string
    {
        return sprintf($this->scopeConfig->getValue(Data::CURRENT_VERSION), $string);
    }

    /**
     * Update Avaiable
     *
     * @return string|null
     */
    public function updateAvaiable(): ?string
    {
        return $this->scopeConfig->getValue(Data::UPDATE_AVAIABLE);
    }

    /**
     * Is up to date
     *
     * @return string|null
     */
    public function isUpToDate(): ?string
    {
        return $this->scopeConfig->getValue(Data::IS_UP_TO_DATE);
    }

    /**
     * Command Update
     *
     * @return string|null
     */
    public function commandUpdate(): ?string
    {
        return $this->scopeConfig->getValue(Data::COMMAND_UPDATE);
    }

    /**
     * Command Update
     *
     * @return string|null
     */
    public function commandDesc(): ?string
    {
        return $this->scopeConfig->getValue(Data::COMMAND_DESC);
    }

    /**
     * Get success
     *
     * @return string|null
     */
    public function getSuccess(): ?string
    {
        return $this->scopeConfig->getValue(Data::SUCCESS);
    }

    /**
     * Get error
     *
     * @return string|null
     */
    public function getError(): ?string
    {
        return $this->scopeConfig->getValue(Data::ERROR);
    }

    /**
     * Get Production Messsage
     *
     * @return string|null
     */
    public function getProductionMesssage(): ?string
    {
        return $this->scopeConfig->getValue(Data::PROD_MESG);
    }

    /**
     * Get Production Messsage
     *
     * @return bool
     */
    public function hasAutoUpdateEnabled(): bool
    {
        return (bool)$this->scopeConfig->getValue(Data::AUTO);
    }

    /**
     * Reset Commands
     *
     * @param  string $deployMode
     * @return array
     */
    public function resetCommands(string $deployMode): array
    {
        $result = $this->getCommands();

        if (!stristr($deployMode, 'production')) {
            unset($result['runSetProdcutionMode']);
        }

        return $result;
    }
}
