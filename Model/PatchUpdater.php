<?php declare(strict_types=1);
/**
 * @author     Osiozekhai Aliu
 * @package    Osio_MagentoAutoPatch
 * @copyright  Copyright (c) 2024 Osio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osio\MagentoAutoPatch\Model;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Filesystem\Driver\File;
use Symfony\Component\Process\Process;

class PatchUpdater
{
    /** @var File */
    private File $file;

    /** @var DirectoryList */
    private DirectoryList $directoryList;

    /** @var Json */
    private Json $json;

    /** @var array */
    private array $composerJson;

    /**
     * @param DirectoryList $directoryList
     * @param Json $json
     * @param File $file
     */
    public function __construct(
        DirectoryList $directoryList,
        Json $json,
        File $file
    ) {
        $this->directoryList = $directoryList;
        $this->json = $json;
        $this->file = $file;
    }

    /**
     * Get Module Version
     *
     * @return string
     * @throws FileSystemException
     */
    public function getVersion(): string
    {
        if ($this->file->isExists($this->getComposerPath())) {
            $this->composerJson = $this->json->unserialize($this->file->fileGetContents($this->getComposerPath()));
        }

        return $this->composerJson['require'][$this->whichMagento()] ?? 'Unknown';
    }

    /**
     * Check if cloud or community Magento version
     *
     * @return string|null
     */
    public function whichMagento(): ?string
    {
        $magentoPackages = [
            'magento/magento-cloud-metapackage',
            'magento/product-community-edition'
        ];

        foreach ($magentoPackages as $package) {
            if (array_key_exists($package, $this->composerJson['require'])) {
                return $package;
            }
        }

        return null;
    }

    /**
     * Check for versions
     *
     * @return Process
     */
    public function hasVersions(): Process
    {
        $process = new Process(['composer', 'show', '--outdated', $this->whichMagento(), '--all']);
        $process->run();

        return $process;
    }

    /**
     * Get the latest patch version within the same minor version.
     *
     * @return string|null
     * @throws FileSystemException
     */
    public function getLatest(): ?string
    {
        // Get the current version and extract the major.minor.patch part
        $currentVersion = $this->getVersion(); // e.g., '2.4.6-p1'
        $versionParts = explode('-', $currentVersion); // Split version and patch
        $baseVersion = $versionParts[0]; // '2.4.6'

        // Fetch all outdated versions from composer show output
        preg_match_all('/\d+\.\d+\.\d+-p\d+/m', $this->hasVersions()->getOutput(), $matches);

        if (empty($matches[0])) {
            return null; // No available patch versions
        }

        // Filter versions that match the current major.minor version
        $filteredMatches = array_filter(
            $matches[0],
            function ($version) use ($baseVersion) {
                return preg_match("/^{$baseVersion}-p\d+$/", $version); // Match the exact base version
            }
        );

        if (empty($filteredMatches)) {
            return null; // No valid patch versions found for the current version
        }

        // Sort filtered versions to get the latest patch version
        usort($filteredMatches, 'version_compare');

        // Get the highest patch version
        $latestPatch = end($filteredMatches); // Get the highest patch version

        // Return the latest patch version if it's newer than the current version
        return version_compare($currentVersion, $latestPatch, '<') ? $latestPatch : null;
    }

    /**
     * Get Composer Path
     *
     * @return string
     */
    private function getComposerPath(): string
    {
        return "{$this->directoryList->getRoot()}/composer.json";
    }
}
