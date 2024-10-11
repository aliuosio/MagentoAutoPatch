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
        Magento $magento,
        Data         $helper
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
        $output->writeln('Checking for new patches...');
        $output->writeln("<info>Current Magento Version: {$this->composer->getVersion()}</info>");
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
            $output->writeln("<info>Latest Minor Patch Version: {$this->composer->getLatest()}</info>");
            $output->writeln("<info>Update available!</info>");
            $output->writeln($this->getAnswerUpdate($input, $output));
        } else {
            $output->writeln("<info>Latest Minor Patch Version: {$this->composer->getVersion()}</info>");
            $output->writeln("<info>Magento is already up to date!</info>");
        }
    }

    /**
     * Get Question Update
     *
     * @return ConfirmationQuestion
     */
    private function getQuestionUpdate(): ConfirmationQuestion
    {
        return new ConfirmationQuestion('Do you want to update Magento? (Y/n) ', true);
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
            if ($this->composer->downloadLatestVersion() && $this->magento->runSetupUpgrade() && $this->magento->runCacheClear()) {
                $message = 'Updated Magento...';
            } else {
                $message = 'Error while Updating Magento';
            }
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
