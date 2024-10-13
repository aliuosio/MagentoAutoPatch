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
use Magento\Framework\Console\Cli;
use Osio\MagentoAutoPatch\Model\Composer;
use Symfony\Component\Console\Command\Command;

class Notify extends Command
{
    /**
     * @var Composer
     */
    private Composer $composer;

    /**
     * @var Data
     */
    private Data $helper;

    /**
     * @param Composer $composer
     * @param Data     $helper
     */
    public function __construct(
        Composer $composer,
        Data     $helper
    ) {
        parent::__construct();
        $this->composer = $composer;
        $this->helper = $helper;
    }

    /**
     * Configure
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('patch:notify');
        $this->setDescription('Checks for Magento patches and sends notification');

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
            $this->composer->getLatest();
        } else {
            $output->writeln('Auto Patcher is not enabled in Backend');
            return Cli::RETURN_FAILURE;
        }

        return Cli::RETURN_SUCCESS;
    }
}
