<?php declare(strict_types=1);
/**
 * @author     Osiozekhai Aliu
 * @package    Osio_MagentoAutoPatch
 * @copyright  Copyright (c) 2024 Osio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osio\MagentoAutoPatch\Console;

use Magento\Framework\Exception\FileSystemException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\Console\Cli;
use Osio\MagentoAutoPatch\Model\PatchUpdater;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Process\Process;

class UpdatePatches extends Command
{

    /** @var PatchUpdater  */
    protected PatchUpdater $patchUpdater;

    /**
     * @param PatchUpdater $patchUpdater
     */
    public function __construct(
        PatchUpdater $patchUpdater
    ) {
        $this->patchUpdater = $patchUpdater;
        parent::__construct();
    }

    /**
     * Configure
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('patch:update');
        $this->setDescription('Checks and applies Magento patches automatically');

        parent::configure();
    }

    /**
     * Execute
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws FileSystemException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Checking for new patches...');
        $output->writeln("<info>Current Magento Version: {$this->patchUpdater->getVersion()}</info>");
        $this->displayLatestVersion($output);

        return Cli::RETURN_SUCCESS;
    }

    /**
     * Display latest Version
     *
     * @param OutputInterface $output
     * @return void
     * @throws FileSystemException
     */
    private function displayLatestVersion(OutputInterface $output): void
    {
        if ($this->patchUpdater->getLatest()) {
            $output->writeln("<info>Latest Magento Version: {$this->patchUpdater->getLatest()}</info>");
            $output->writeln("<info>update needed</info>");
            $output->writeln("Updating Magento...");
        } else {
            $output->writeln("<info>Latest Magento Version: {$this->patchUpdater->getVersion()}</info>");
            $output->writeln("no update needed");
        }
    }
}
