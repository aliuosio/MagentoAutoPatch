<?php declare(strict_types=1);

namespace Osio\MagentoAutoPatch\Model;

use Symfony\Component\Process\Process;
use RuntimeException;

class ProcessWrapper
{
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
                throw new RuntimeException('Command failed: ' . $process->getErrorOutput());
            }
        } catch (\Throwable $e) {
            throw new RuntimeException(
                'Command execution error: ' . $process->getErrorOutput() . ' | Exception: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }

        return $process;
    }
}
