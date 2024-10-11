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

/**
 * @method getVersion()
 * @method downloadLatestVersion()
 * @method getLatest()
 */
class PatchUpdater
{

    /**
     * @var Composer
     */
    private Composer $composer;

    /**
     * @param Composer $composer
     */
    public function __construct(
        Composer    $composer
    ) {
        $this->composer = $composer;
    }

    /**
     * @inheritdoc
     */
    public function __call($method, $args)
    {
        if (method_exists($this->composer, $method)) {
            return call_user_func_array([$this->composer, $method], $args);
        }

        return false;
    }
}
