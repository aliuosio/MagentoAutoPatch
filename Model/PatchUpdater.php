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
        Json          $json,
        File          $file
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
     * Check if cloud or communinty Magento version
     *
     * @return string
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
     * Get latest Patch
     *
     * @return string|null
     * @throws FileSystemException
     */
    public function getLatest(): ?string
    {
        preg_match_all('/\d+\.\d+\.\d+-p\d+/m', $this->hasVersions()->getOutput(), $matches);

        if (empty($matches[0])) {
            return null;
        }

        return $this->compareVersions($matches);
    }

    /**
     * Compare Versions
     *
     * @param array $matches
     * @return string|null
     * @throws FileSystemException
     */
    private function compareVersions(array $matches): ?string
    {
        $latestPatch = max($matches[0]);
        if (version_compare($this->getVersion(), $latestPatch, '>=')) {
            return null;
        }

        return $latestPatch;
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
