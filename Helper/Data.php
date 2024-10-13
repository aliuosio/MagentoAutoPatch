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
}
