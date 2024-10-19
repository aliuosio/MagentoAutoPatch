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
use Osio\MagentoAutoPatch\Model\Logger\Log;
use RuntimeException;
use Throwable;

class ProcessWrapper
{
    /**
     * @var Log
     */
    private Log $logger;

    /**
     * @param Log $logger
     */
    public function __construct(
        Log $logger
    ) {
        $this->logger = $logger;
    }

    /**
     * Run a command with optional dry-run
     *
     * @param  string $command
     * @param  bool   $dryRun
     * @return Process
     * @throws RuntimeException
     */
    public function runCommand(string $command, bool $dryRun = true): Process
    {
        if ($dryRun && $this->supportsDryRun($command)) {
            $dryRunProcess = $this->runDryRunCommand($command);

            // Only proceed if the dry run was successful
            if (!$dryRunProcess->isSuccessful()) {
                return $dryRunProcess; // Return the failed dry run process
            }
            $this->logger->info("Dry run succeeded. Proceeding with actual command: $command");
        }

        return $this->runActualCommand($command);
    }

    /**
     * Run a command in dry-run mode
     *
     * @param string $command
     * @return Process
     */
    private function runDryRunCommand(string $command): Process
    {
        $dryRunCommand = $command . ' --dry-run';
        $this->logger->info("Executing command in dry-run mode: $dryRunCommand");

        $process = $this->getProcess($dryRunCommand);
        $process->run();

        if (!$process->isSuccessful()) {
            $this->logProcessError($process, 'Dry run failed');
        } else {
            $this->logger->info("Dry run succeeded.");
        }

        return $process;
    }

    /**
     * Run the actual command
     *
     * @param string $command
     * @return Process
     */
    private function runActualCommand(string $command): Process
    {
        $this->logger->info("Executing command: $command");
        $process = $this->getProcess($command);

        try {
            $process->run();
            if (!$process->isSuccessful()) {
                $this->logProcessError($process, 'Command failed');
            }
        } catch (Throwable $e) {
            $this->logProcessException($process, $e);
        }

        return $process;
    }

    /**
     * Log error message from a failed process
     *
     * @param Process $process
     * @param string $context
     */
    private function logProcessError(Process $process, string $context): void
    {
        $message = "$context: " . $process->getErrorOutput();
        $this->logger->error($message);
    }

    /**
     * Log exception details during command execution
     *
     * @param Process $process
     * @param Throwable $e
     */
    private function logProcessException(Process $process, Throwable $e): void
    {
        $message = 'Command execution error: '
            . $process->getErrorOutput()
            . ' | Exception: '
            . $e->getTraceAsString();
        $this->logger->error($message);
    }

    /**
     * Check if a command supports dry-run option
     *
     * @param string $command
     * @return bool
     */
    private function supportsDryRun(string $command): bool
    {
        $dryRunCommands = [
            'composer require',
            'composer update',
        ];

        return count(array_filter($dryRunCommands, fn($dryRunCommand) => strpos($command, $dryRunCommand) === 0)) > 0;
    }

    /**
     * Get Process
     *
     * @param  string $command
     * @return Process
     */
    private function getProcess(string $command): Process
    {
        return new Process(explode(' ', $command));
    }
}
