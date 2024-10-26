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

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Serialize\Serializer\Json;
use Osio\MagentoAutoPatch\Model\Logger\Log;
use Symfony\Component\Process\Process;

class Composer
{
    /**
     * @var DirectoryList
     */
    private DirectoryList $directoryList;

    /**
     * @var array|null
     */
    private ?array $composerJson = null;

    /**
     * @var Json
     */
    private Json $json;

    /**
     * @var File
     */
    private File $file;

    /**
     * @var ProcessWrapper
     */
    private ProcessWrapper $processWrapper;

    /**
     * @var string|null
     */
    private ?string $composerPath = null;

    /**
     * @var Process|null
     */
    private ?Process $outdated = null;

    /**
     * @var string|null
     */
    private ?string $package = null;

    /**
     * @var Process|null
     */
    private ?Process $latestVersionProcess = null;

    /**
     * @var Log
     */
    private Log $logger;

    /**
     * @param ProcessWrapper $processWrapper
     * @param DirectoryList  $directoryList
     * @param Json           $json
     * @param File           $file
     * @param Log            $logger
     */
    public function __construct(
        ProcessWrapper $processWrapper,
        DirectoryList  $directoryList,
        Json           $json,
        File           $file,
        Log            $logger
    ) {
        $this->directoryList = $directoryList;
        $this->json = $json;
        $this->file = $file;
        $this->processWrapper = $processWrapper;
        $this->logger = $logger;
    }

    /**
     * Get Composer Path
     *
     * @return string|null
     */
    public function getComposerPath(): ?string
    {
        if ($this->composerPath === null) {
            $this->composerPath = "{$this->directoryList->getRoot()}/composer.json";
        }
        return $this->composerPath; // Added return statement
    }

    /**
     * Check for versions
     *
     * @return Process
     */
    public function hasVersions(): Process
    {
        if ($this->outdated !== null) {
            return $this->outdated;
        }

        $this->outdated = $this->processWrapper
            ->runCommand("composer show --outdated {$this->whichMagento()} --all -n");

        return $this->outdated; // Added return statement
    }

    /**
     * Determines the Magento package type (Cloud or Community)
     *
     * @return string|null
     */
    public function whichMagento(): ?string
    {
        if ($this->package !== null) {
            return $this->package;
        }

        $magentoPackages = [
            'magento/magento-cloud-metapackage',
            'magento/product-community-edition'
        ];

        foreach ($magentoPackages as $package) {
            if (isset($this->composerJson['require'][$package])) {
                $this->package = $package;
                break;
            }
        }

        return $this->package;
    }

    /**
     * Retrieve the Magento Module Version
     *
     * @return string
     */
    public function getVersion(): string
    {
        try {
            if ($this->file->isExists($this->getComposerPath())) {
                $this->composerJson = $this->json->unserialize(
                    $this->file->fileGetContents($this->getComposerPath())
                );
                return $this->getMagentoVersionFromJson();
            }
        } catch (FileSystemException $e) {
            $this->logger->critical($e, ['code' => $e->getCode()]);
        }

        return 'unknown';
    }

    /**
     * Extract the Magento version from the composer JSON data
     *
     * @return string
     */
    private function getMagentoVersionFromJson(): string
    {
        return $this->composerJson['require'][$this->whichMagento()] ?? 'unknown';
    }

    /**
     * Download the latest version of the module.
     *
     * @return Process
     */
    public function downloadLatestVersion(): Process
    {
        if ($this->latestVersionProcess !== null) {
            return $this->latestVersionProcess;
        }

        $this->latestVersionProcess = $this->executeDownloadCommand("composer require-commerce");

        if (!$this->latestVersionProcess->isSuccessful()) {
            $this->latestVersionProcess = $this->executeDownloadCommand("composer require");
        }

        return $this->latestVersionProcess;
    }

    /**
     * Execute the download command with the specified base command.
     *
     * @param  string $baseCommand
     * @return Process|null
     */
    private function executeDownloadCommand(string $baseCommand): ?Process
    {
        return $this->processWrapper->runCommand(
            "$baseCommand {$this->whichMagento()}:{$this->getLatest()} -n --no-update"
        );
    }

    /**
     * Update the installed packages
     *
     * @return Process
     */
    public function update(): Process
    {
        return $this->processWrapper->runCommand("composer update -n");
    }

    /**
     * Get latest minor patch version
     *
     * @return string|null
     */
    public function getLatest(): ?string
    {
        $currentVersion = $this->getVersion();
        $baseVersion = $this->extractBaseVersion($currentVersion);

        $availableVersions = $this->fetchOutdatedVersions();
        if (empty($availableVersions)) {
            return null;
        }

        $filteredVersions = $this->filterVersions($availableVersions, $baseVersion);
        if (empty($filteredVersions)) {
            return null;
        }

        $latestPatch = $this->getLatestPatchVersion($filteredVersions);

        return $this->isNewerVersion($currentVersion, $latestPatch) ? $latestPatch : null;
    }

    /**
     * Extracts the base version (major.minor.patch) from the current version.
     *
     * @param  string $currentVersion
     * @return string
     */
    private function extractBaseVersion(string $currentVersion): string
    {
        $versionParts = explode('-', $currentVersion);
        return $versionParts[0];
    }

    /**
     * Fetches all outdated versions from the composer show output.
     *
     * @return array
     */
    private function fetchOutdatedVersions(): array
    {
        preg_match_all('/\d+\.\d+\.\d+-p\d+/m', $this->hasVersions()->getOutput(), $matches);
        return $matches[0] ?? [];
    }

    /**
     * Filters available versions to match the current major.minor.patch version.
     *
     * @param  array  $availableVersions
     * @param  string $baseVersion
     * @return array
     */
    private function filterVersions(array $availableVersions, string $baseVersion): array
    {
        return array_filter(
            $availableVersions,
            fn($version) => preg_match("/^$baseVersion-p\d+$/", $version)
        );
    }

    /**
     * Sorts the filtered versions and returns the latest patch version.
     *
     * @param  array $filteredVersions
     * @return string
     */
    private function getLatestPatchVersion(array $filteredVersions): string
    {
        usort($filteredVersions, 'version_compare');
        return end($filteredVersions);
    }

    /**
     * Compares the current version with the latest patch version.
     *
     * @param  string $currentVersion
     * @param  string $latestPatch
     * @return bool
     */
    private function isNewerVersion(string $currentVersion, string $latestPatch): bool
    {
        return version_compare($currentVersion, $latestPatch, '<');
    }
}
