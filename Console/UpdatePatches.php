<?php declare(strict_types=1);
/**
 * @author    Osiozekhai Aliu
 * @package   Osio_MagentoAutoPatch
 * @copyright Copyright (c) 2024 Osio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osio\MagentoAutoPatch\Console;

use Magento\Framework\Exception\FileSystemException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Magento\Framework\Console\Cli;
use Osio\MagentoAutoPatch\Model\PatchUpdater;
use Symfony\Component\Console\Command\Command;

class UpdatePatches extends Command
{

    /**
     * @var PatchUpdater  
     */
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
     * @param  InputInterface  $input
     * @param  OutputInterface $output
     * @return int
     * @throws FileSystemException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Checking for new patches...');
        $output->writeln("<info>Current Magento Version: {$this->patchUpdater->getVersion()}</info>");
        $this->displayLatestVersion($input, $output);

        return Cli::RETURN_SUCCESS;
    }

    /**
     * Display latest Version
     *
     * @param  InputInterface  $input
     * @param  OutputInterface $output
     * @return void
     * @throws FileSystemException
     */
    private function displayLatestVersion(InputInterface $input, OutputInterface $output): void
    {
        if ($this->patchUpdater->getLatest()) {
            $output->writeln("<info>Latest Minor Patch Version: {$this->patchUpdater->getLatest()}</info>");
            $output->writeln("<info>Update available!</info>");

            // Ask the user if they want to update
            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion('Do you want to update Magento? (Y/n) ', true);

            if ($helper->ask($input, $output, $question)) {
                $output->writeln('Updating Magento...');
                // Add your update logic here
                // $this->patchUpdater->applyUpdate(); // Example update call
            } else {
                $output->writeln('Update canceled by the user.');
            }

        } else {
            $output->writeln("<info>Magento is already up to date!</info>");
        }
    }
}
