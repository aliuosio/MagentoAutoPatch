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
