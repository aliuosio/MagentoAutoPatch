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
        $this->setName("patch:update");
        $this->setDescription("Checks and applies Magento patches automatically");

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
            $output->writeln($this->helper->notEnabled());
            return Cli::RETURN_FAILURE;
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
        $output->writeln("{$this->helper->getCheckMessage()}");
        $output->writeln("{$this->helper->getCurrentVersion($this->composer->getVersion())}");
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
            $output->writeln("{$this->helper->getMinorVersion($this->composer->getLatest())}");
            $output->writeln($this->helper->updateAvaiable());
            $output->writeln($this->getAnswerUpdate($input, $output));
        } else {
            $output->writeln("{$this->helper->getMinorVersion($this->composer->getVersion())}");
            $output->writeln($this->helper->isUpToDate());
        }
    }

    /**
     * Get Question Update
     *
     * @return ConfirmationQuestion
     */
    private function getQuestionUpdate(): ConfirmationQuestion
    {
        return new ConfirmationQuestion($this->helper->getQuestion(), true);
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
        $result = $this->getQuestionHelper()->ask($input, $output, $this->getQuestionUpdate());

        if (!$result) {
            return '';
        }

        foreach ($this->helper->getMessages() as $method => $messages) {
            $output->writeln($messages['startMessage']);
            if (!$this->executeStep($method, $messages['successMessage'], $messages['errorMessage'], $output)) {
                return $this->helper->getError();
            }
        }

        $this->checkAndSetProductionMode($output);

        return $this->helper->getSuccess();
    }

    /**
     * Executes a given step and prints appropriate messages.
     *
     * @param  string          $method
     * @param  string          $successMessage
     * @param  string          $errorMessage
     * @param  OutputInterface $output
     * @return bool
     */
    private function executeStep(
        string          $method,
        string          $successMessage,
        string          $errorMessage,
        OutputInterface $output
    ): bool {
        if ((method_exists($this->composer, $method) && $this->composer->$method()) ||
            (method_exists($this->magento, $method) && $this->magento->$method())) {
            $output->writeln($successMessage);
            return true;
        }
        $output->writeln($errorMessage);

        return false;
    }

    /**
     * Checks the current deploy mode and sets it to production if needed.
     *
     * @param  OutputInterface $output
     * @return void
     */
    private function checkAndSetProductionMode(OutputInterface $output): void
    {
        if (stristr($this->magento->getDeployMode(), 'production')) {
            $output->writeln($this->helper->getProductionMesssage());
            $this->magento->setDeployModeProduction();
            $this->magento->disableMaintenanceMode();
        }
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
