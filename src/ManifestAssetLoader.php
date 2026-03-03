<?php

declare(strict_types=1);

/**
 * ManifestAssetLoader.php
 *
 * PHP Version 8.3+
 *
 * @author Philippe Gaultier <philippe@blackcube.io>
 * @copyright 2010-2026 Blackcube
 * @license https://blackcube.io/license
 */

namespace Blackcube\Assets;

use RuntimeException;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Assets\AssetBundle;
use Yiisoft\Assets\AssetLoaderInterface;

/**
 * Decorator for AssetLoader that handles manifest-based bundles.
 *
 * Works with all standard AssetBundle classes.
 * Adds manifest loading for bundles implementing ManifestAssetBundleInterface.
 *
 * @author Philippe Gaultier <philippe@blackcube.io>
 * @copyright 2010-2026 Blackcube
 * @license https://blackcube.io/license
 */
final class ManifestAssetLoader implements AssetLoaderInterface
{
    public function __construct(
        private readonly AssetLoaderInterface $innerLoader,
        private readonly Aliases $aliases,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getAssetUrl(AssetBundle $bundle, string $assetPath): string
    {
        return $this->innerLoader->getAssetUrl($bundle, $assetPath);
    }

    /**
     * @inheritDoc
     */
    public function loadBundle(string $name, array $config = []): AssetBundle
    {
        $bundle = $this->innerLoader->loadBundle($name, $config);

        if ($bundle instanceof ManifestAssetBundleInterface) {
            $catalog = $this->readCatalog($bundle);
            $bundle->loadFromCatalog($catalog);
        }

        return $bundle;
    }

    /**
     * Reads and decodes the catalog JSON file.
     *
     * @param AssetBundle&ManifestAssetBundleInterface $bundle
     * @return array Decoded catalog
     * @throws RuntimeException If sourcePath is not set or catalog file not found
     */
    private function readCatalog(AssetBundle&ManifestAssetBundleInterface $bundle): array
    {
        if ($bundle->sourcePath === null) {
            throw new RuntimeException(
                sprintf('sourcePath must be set for manifest bundle %s', $bundle::class)
            );
        }

        $sourcePath = $this->aliases->get($bundle->sourcePath);
        $catalogPath = $sourcePath . '/' . $bundle->getCatalogFile();

        if (!file_exists($catalogPath)) {
            throw new RuntimeException(
                sprintf('Catalog file not found: %s', $catalogPath)
            );
        }

        $content = file_get_contents($catalogPath);
        if ($content === false) {
            throw new RuntimeException(
                sprintf('Failed to read catalog file: %s', $catalogPath)
            );
        }

        return json_decode($content, true, 512, JSON_THROW_ON_ERROR);
    }
}
