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
use Osio\MagentoAutoPatch\Model\Logger\CustomLogger;
use RuntimeException;
use Throwable;

class ProcessWrapper
{
    /**
     * @var CustomLogger
     */
    private CustomLogger $logger;

    /**
     * @param CustomLogger $logger
     */
    public function __construct(
        CustomLogger $logger
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
        $process = $this->getProcess($command);

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

    /**
     * Get Process
     *
     * @param string $command
     * @return Process
     */
    private function getProcess(string $command): Process
    {
        return new Process(explode(' ', $command));
    }
}
