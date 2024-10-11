<?php

namespace Osio\MagentoAutoPatch\Model;

use Symfony\Component\Process\Process;

class Magento
{
    /**
     * Run setup upgrade
     *
     * @return bool
     */
    public function runSetupUpgrade(): bool
    {
        $command = "bin/magento setup:upgrade";
        $updateProcess = new Process(explode(' ', $command));
        $updateProcess->run();

        return $updateProcess->isSuccessful();
    }

    /**
     * Run Cache Clear
     *
     * @return bool
     */
    public function runCacheClear(): bool
    {
        $command = "bin/magento cache:flush";
        $updateProcess = new Process(explode(' ', $command));
        $updateProcess->run();

        return $updateProcess->isSuccessful();
    }
}
