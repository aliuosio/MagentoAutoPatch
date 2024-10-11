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

class Magento extends AbstractProcess
{

    /**
     * Run setup upgrade
     *
     * @return Process
     */
    public function runSetupUpgrade(): Process
    {
        return $this->runCommand("bin/magento setup:upgrade");
    }

    /**
     * Run Cache Clear
     *
     * @return Process
     */
    public function runCacheClear(): Process
    {
        return $this->runCommand("bin/magento cache:flush");
    }

    public function getDeployMode(): string
    {
        return $this->runCommand("bin/magento deploy:mode:show")->getOutput();
    }

    /**
     * @return Process
     */
    public function setDeployModeProduction(): Process
    {
        return $this->runCommand("bin/magento deploy:mode:set production");
    }
}
