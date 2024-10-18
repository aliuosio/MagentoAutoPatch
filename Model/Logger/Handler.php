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

use Monolog\Handler\StreamHandler;
use Magento\Framework\Filesystem\Driver\File;
use Monolog\Logger as MonologLogger;
use RuntimeException;
use Throwable;

class Handler extends StreamHandler
{
    /**
     * @var File
     */
    protected File $fileDriver;

    /**
     * @var int
     */
    protected int $loggerType = MonologLogger::INFO;

    /**
     * @var string
     */
    protected string $fileName = '/var/log/auto-patch.log';

    /**
     * @param File $fileDriver
     */
    public function __construct(
        File $fileDriver
    ) {
        $this->fileDriver = $fileDriver;
        $this->resetLog($this->getMyStream());

        parent::__construct($this->getMyStream(), $this->loggerType);
    }

    /**
     * Get My Stream
     *
     * @return string
     */
    private function getMyStream(): string
    {
        return BP . $this->fileName;
    }

    /**
     * Reset the log file using Magento's file handling.
     *
     * @param  string $stream
     * @return void
     */
    protected function resetLog(string $stream): void
    {
        try {
            if ($this->fileDriver->isExists($stream)) {
                $this->fileDriver->filePutContents($stream, '');
            }
        } catch (Throwable $e) {
            $message = 'Command execution error: ' . $e->getTraceAsString() . ' | Exception: ' . $e->getMessage();
            throw new RuntimeException($message, $e->getCode(), $e);
        }
    }
}
