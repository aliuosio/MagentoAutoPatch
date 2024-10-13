<?php declare(strict_types=1);
/**
 * @author    Osiozekhai Aliu
 * @package   Osio_MagentoAutoPatch
 * @copyright Copyright (c) 2024 Osio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osio\MagentoAutoPatch\Model\Logger;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class CustomHandler extends StreamHandler
{
    /**
     * Constructer call Parents Constructor
     *
     * @param string $filePath
     * @param string $level
     */
    public function __construct(string $filePath, string $level = Logger::DEBUG)
    {
        parent::__construct($filePath, $level);
    }
}
