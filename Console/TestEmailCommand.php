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

use Magento\Framework\App\State;
use Magento\Framework\Console\Cli;
use Magento\Framework\Exception\LocalizedException;
use Osio\MagentoAutoPatch\Model\Notifier\Email;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestEmailCommand extends Command
{
    /**
     * @var Email
     */
    private Email $email;

    /**
     * @var State
     */
    private State $appState;

    /**
     * TestEmailCommand constructor.
     *
     * @param Email $email
     * @param State $appState
     */
    public function __construct(
        Email $email,
        State $appState
    ) {
        parent::__construct();
        $this->email = $email;
        $this->appState = $appState;
    }

    /**
     * Configure the command
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('patch:test-email')
            ->setDescription('Test sending a success email for Magento patches.');

        parent::configure();
    }

    /**
     * Execute the command
     *
     * @param  InputInterface  $input
     * @param  OutputInterface $output
     * @return int
     * @throws LocalizedException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->appState->setAreaCode('frontend');

        if ($this->email->send('2.4.1')) {
            $output->writeln("<info>Email has been sent successfully.</info>");
        } else {
            $output->writeln("<error>Email could not be sent.</error>");
            return Cli::RETURN_FAILURE;
        }

        return Cli::RETURN_SUCCESS;
    }
}
