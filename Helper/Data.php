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

    private const ENABLED = 'patcher/general/enabled';
    private const NOTIFY_BEFORE = 'patcher/settings/notify/before';
    private const NOTIFY_AFTER = 'patcher/settings/notify/after';
    private const MESSAGES = 'patcher/messages';
    private const QUESTION = 'patcher/question';
    private const CHECK_MESG = 'patcher/process/check';
    private const NOT_ENABLED = 'patcher/not_enabled';
    private const MINOR_VERSION = 'patcher/minor_version';
    private const CURRENT_VERSION = 'patcher/current_version';
    private const UPDATE_AVAIABLE = 'patcher/update_avaiable';
    private const IS_UP_TO_DATE = 'patcher/is_up_to_data';
    private const COMMAND_UPDATE = 'patcher/command/update';
    private const COMMAND_DESC = 'patcher/command/description';
    private const SUCCESS = 'patcher/success';
    private const ERROR = 'patcher/error';
    private const PROD_MESG = 'patcher/production_mode_mesg';

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
    public function getMessages(): array
    {
        return $this->scopeConfig->getValue(Data::MESSAGES);
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
}
