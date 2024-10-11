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

abstract class AbstractProcess
{
    /**
     * Run a Magento command
     *
     * @param  string $command
     * @return Process
     */
    public function runCommand(string $command): Process
    {
        $process = new Process(explode(' ', $command));
        $process->run();

        return $process;
    }
}
