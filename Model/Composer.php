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
use Symfony\Component\Process\Process;

class Composer
{

    /**
     * @var DirectoryList
     */
    private DirectoryList $directoryList;

    /**
     * @var array
     */
    private array $composerJson;

    /**
     * @var Json  
     */
    private Json $json;

    /**
     * @var File  
     */
    private File $file;

    /**
     * @param DirectoryList $directoryList
     * @param Json          $json
     * @param File          $file
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
     * Get Composer Path
     *
     * @return string
     */
    public function getComposerPath(): string
    {
        return "{$this->directoryList->getRoot()}/composer.json";
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
     * Get Module Version
     *
     * @return string
     * @throws FileSystemException
     */
    public function getVersion(): string
    {
        if ($this->file->isExists($this->getComposerPath())) {
            $this->composerJson = $this->json->unserialize(
                $this->file->fileGetContents($this->getComposerPath())
            );
        }

        return $this->composerJson['require'][$this->whichMagento()] ?? 'Unknown';
    }
}
