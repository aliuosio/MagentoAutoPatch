<?php declare(strict_types=1);
/**
 * @author    Osiozekhai Aliu
 * @package   Osio_MagentoAutoPatch
 * @copyright Copyright (c) 2024 Osio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osio\MagentoAutoPatch\Model;

use Symfony\Component\Process\Process;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Throwable;

class ProcessWrapper
{
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }

    /**
     * Run a command
     *
     * @param  string $command
     * @return Process
     * @throws RuntimeException
     */
    public function runCommand(string $command): Process
    {
        $process = new Process(explode(' ', $command));

        try {
            $process->run();
            if (!$process->isSuccessful()) {
                $message = 'Command failed: ' . $process->getErrorOutput();
                $this->logger->error($message);
                throw new RuntimeException($message);
            }
        } catch (Throwable $e) {
            $message = 'Command execution error: ' . $process->getErrorOutput() . ' | Exception: ' . $e->getMessage();
            $this->logger->error($message);
            throw new RuntimeException($message, $e->getCode(), $e);
        }

        return $process;
    }
}
