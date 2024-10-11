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
use Osio\MagentoAutoPatch\Helper\Data;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Magento\Framework\Console\Cli;
use Osio\MagentoAutoPatch\Model\Composer;
use Osio\MagentoAutoPatch\Model\Magento;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;

class UpdatePatches extends Command
{

    /**
     * @var Data
     */
    private Data $helper;

    /**
     * @var Composer
     */
    private Composer $composer;

    /**
     * @var Magento
     */
    private Magento $magento;

    /**
     * @param Composer $composer
     * @param Magento  $magento
     * @param Data     $helper
     */
    public function __construct(
        Composer $composer,
        Magento  $magento,
        Data     $helper
    ) {
        parent::__construct();

        $this->helper = $helper;
        $this->composer = $composer;
        $this->magento = $magento;
    }

    /**
     * Configure
     *
     * @return void
     */
    protected function configure(): void
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
        if ($this->helper->isEnabled()) {
            $this->runner($input, $output);
        } else {
            $output->writeln('Auto Patcher is not enabled in Backend');
        }
        return Cli::RETURN_SUCCESS;
    }

    /**
     * Command wrapper
     *
     * @param  InputInterface  $input
     * @param  OutputInterface $output
     * @return void
     * @throws FileSystemException
     */
    private function runner(InputInterface $input, OutputInterface $output): void
    {
        $output->writeln('<comment>Checking for new patches...</comment>');
        $output->writeln("Current Magento Version: {$this->composer->getVersion()}");
        $this->displayLatestVersion($input, $output);
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
        if ($this->composer->getLatest()) {
            $output->writeln("<comment>Latest Minor Patch Version: {$this->composer->getLatest()}</comment>");
            $output->writeln("Update available!");
            $output->writeln($this->getAnswerUpdate($input, $output));
        } else {
            $output->writeln("<comment>Latest Minor Patch Version: {$this->composer->getVersion()}</comment>");
            $output->writeln("Magento is already up to date!");
        }
    }

    /**
     * Get Question Update
     *
     * @return ConfirmationQuestion
     */
    private function getQuestionUpdate(): ConfirmationQuestion
    {
        return new ConfirmationQuestion('<question>Do you want to update Magento? (Y/n)</question>', true);
    }

    /**
     * Get Answer Update
     *
     * @param  InputInterface  $input
     * @param  OutputInterface $output
     * @return string
     * @throws FileSystemException
     */
    private function getAnswerUpdate(InputInterface $input, OutputInterface $output): string
    {
        $message = '';
        $result = $this->getQuestionHelper()->ask($input, $output, $this->getQuestionUpdate());

        if ($result) {
            // Step 1: Download latest version
            $output->writeln('<comment>Downloading latest version...</comment>');
            if ($this->composer->downloadLatestVersion()) {
                $output->writeln('<info>Downloaded latest version successfully.</info>');
            } else {
                $output->writeln('<error>Error downloading latest version.</error>');
                return 'Error while updating Magento';
            }

            // Step 2: Run setup upgrade
            $output->writeln('<comment>Running setup upgrade...</comment>');
            if ($this->magento->runSetupUpgrade()) {
                $output->writeln('<info>Setup upgrade completed successfully.</info>');
            } else {
                $output->writeln('<error>Error during setup upgrade.</error>');
                return 'Error while updating Magento';
            }

            // Step 3: Clear cache
            $output->writeln('<comment>Clearing cache...</comment>');
            if ($this->magento->runCacheClear()) {
                $output->writeln('<info>Cache cleared successfully.</info>');
            } else {
                $output->writeln('<error>Error clearing cache.</error>');
                return 'Error while updating Magento';
            }

            // Step 3:Check Deploy Mode and compile production mode
            if (stristr($this->magento->getDeployMode(), 'production')) {
                $output->writeln('<comment>Setting Production Mode as before... this can take some time</comment>');
                $this->magento->setDeployModeProduction();
            }

            $message = '<comment>Magento updated successfully!</comment>';
        }

        return $message;
    }

    /**
     * Get Question Helper class
     *
     * @return QuestionHelper
     */
    protected function getQuestionHelper(): QuestionHelper
    {
        return $this->getHelper('question');
    }
}
