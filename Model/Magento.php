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

class Magento
{

    /**
     * @var ProcessWrapper
     */
    private ProcessWrapper $processWrapper;

    /**
     * @param ProcessWrapper $processWrapper
     */
    public function __construct(
        ProcessWrapper $processWrapper
    )
    {
        $this->processWrapper = $processWrapper;
    }

    /**
     * Run setup upgrade
     *
     * @return Process
     */
    public function runSetupUpgrade(): Process
    {
        return $this->processWrapper->runCommand("bin/magento setup:upgrade --no-interaction");
    }

    /**
     * Run Cache Clear
     *
     * @return Process
     */
    public function runCacheClear(): Process
    {
        return $this->processWrapper->runCommand("bin/magento cache:flush --no-interaction");
    }

    /**
     * Get Deploy Mode
     *
     * @return string
     */
    public function getDeployMode(): string
    {
        return $this->processWrapper->runCommand("bin/magento deploy:mode:show --no-interaction")->getOutput();
    }

    /**
     * Set Prodcution Mode if enabled before
     *
     * @return Process|null
     */
    public function runSetProdcutionMode(): ?Process
    {
        if (stristr($this->getDeployMode(), 'production')) {
            return $this->processWrapper->runCommand("bin/magento deploy:mode:set production --no-interaction");
        }

        return null;
    }
}
