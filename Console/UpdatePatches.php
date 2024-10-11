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
use Osio\MagentoAutoPatch\Model\PatchUpdater;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;

class UpdatePatches extends Command
{

    /**
     * @var PatchUpdater
     */
    protected PatchUpdater $patchUpdater;

    /**
     * @var Data
     */
    private Data $helper;

    /**
     * @param PatchUpdater $patchUpdater
     * @param Data         $helper
     */
    public function __construct(
        PatchUpdater $patchUpdater,
        Data $helper
    ) {
        parent::__construct();

        $this->patchUpdater = $patchUpdater;
        $this->helper = $helper;
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
        $output->writeln("<info>Current Magento Version: {$this->patchUpdater->getVersion()}</info>");
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
        if ($this->patchUpdater->getLatest()) {
            $output->writeln("<info>Latest Minor Patch Version: {$this->patchUpdater->getLatest()}</info>");
            $output->writeln("<info>Update available!</info>");
            $output->writeln($this->getAnswerUpdate($input, $output));
        } else {
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
     */
    private function getAnswerUpdate(InputInterface $input, OutputInterface $output): string
    {
        return ($this->getQuestionHelper()->ask($input, $output, $this->getQuestionUpdate())) ?
            'Updating Magento...' : 'Update canceled by the user.';
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
