<?php

declare(strict_types=1);

/**
 * ViteAssetBundleCest.php
 *
 * PHP Version 8.3+
 *
 * @author Philippe Gaultier <philippe@blackcube.io>
 * @copyright 2010-2026 Blackcube
 * @license https://blackcube.io/license
 */

namespace Blackcube\Assets\Tests\Unit;

use Blackcube\Assets\ViteAssetBundle;
use Blackcube\Assets\ManifestAssetBundleInterface;
use Blackcube\Assets\Tests\Support\UnitTester;

/**
 * Unit tests for ViteAssetBundle.
 *
 * @author Philippe Gaultier <philippe@blackcube.io>
 * @copyright 2010-2026 Blackcube
 * @license https://blackcube.io/license
 */
final class ViteAssetBundleCest
{
    // ==================== Interface ====================

    public function testImplementsManifestInterface(UnitTester $I): void
    {
        $bundle = new ViteAssetBundle();

        $I->assertInstanceOf(ManifestAssetBundleInterface::class, $bundle);
    }

    // ==================== Default values ====================

    public function testDefaultCatalogFile(UnitTester $I): void
    {
        $bundle = new ViteAssetBundle();

        $I->assertEquals('assets-catalog.json', $bundle->getCatalogFile());
    }

    public function testDefaultJsOptions(UnitTester $I): void
    {
        $bundle = new ViteAssetBundle();

        $I->assertEquals(['type' => 'module', 'defer' => true], $bundle->jsOptions);
    }

    public function testCustomCatalogFile(UnitTester $I): void
    {
        $bundle = new ViteAssetBundle();
        $bundle->catalogFile = 'custom-manifest.json';

        $I->assertEquals('custom-manifest.json', $bundle->getCatalogFile());
    }

    // ==================== loadFromCatalog ====================

    public function testLoadFromCatalogWithJsAndCss(UnitTester $I): void
    {
        $bundle = new ViteAssetBundle();
        $catalog = [
            'app' => [
                'js' => 'js/app.abc123.js',
                'css' => 'css/app.def456.css',
            ],
        ];

        $bundle->loadFromCatalog($catalog);

        $I->assertEquals(['js/app.abc123.js'], $bundle->js);
        $I->assertEquals(['css/app.def456.css'], $bundle->css);
    }

    public function testLoadFromCatalogWithJsOnly(UnitTester $I): void
    {
        $bundle = new ViteAssetBundle();
        $catalog = [
            'vendor' => [
                'js' => 'js/vendor.xyz789.js',
            ],
        ];

        $bundle->loadFromCatalog($catalog);

        $I->assertEquals(['js/vendor.xyz789.js'], $bundle->js);
        $I->assertEquals([], $bundle->css);
    }

    public function testLoadFromCatalogWithCssOnly(UnitTester $I): void
    {
        $bundle = new ViteAssetBundle();
        $catalog = [
            'styles' => [
                'css' => 'css/styles.abc123.css',
            ],
        ];

        $bundle->loadFromCatalog($catalog);

        $I->assertEquals([], $bundle->js);
        $I->assertEquals(['css/styles.abc123.css'], $bundle->css);
    }

    public function testLoadFromCatalogMultipleEntries(UnitTester $I): void
    {
        $bundle = new ViteAssetBundle();
        $catalog = [
            'app' => [
                'js' => 'js/app.abc123.js',
                'css' => 'css/app.def456.css',
            ],
            'vendor' => [
                'js' => 'js/vendor.xyz789.js',
            ],
            'styles' => [
                'css' => 'css/extra.uvw012.css',
            ],
        ];

        $bundle->loadFromCatalog($catalog);

        $I->assertEquals(['js/app.abc123.js', 'js/vendor.xyz789.js'], $bundle->js);
        $I->assertEquals(['css/app.def456.css', 'css/extra.uvw012.css'], $bundle->css);
    }

    public function testLoadFromCatalogEmptyCatalog(UnitTester $I): void
    {
        $bundle = new ViteAssetBundle();

        $bundle->loadFromCatalog([]);

        $I->assertEquals([], $bundle->js);
        $I->assertEquals([], $bundle->css);
    }

    public function testLoadFromCatalogPreservesExistingAssets(UnitTester $I): void
    {
        $bundle = new ViteAssetBundle();
        $bundle->js = ['existing.js'];
        $bundle->css = ['existing.css'];

        $catalog = [
            'app' => [
                'js' => 'js/app.abc123.js',
                'css' => 'css/app.def456.css',
            ],
        ];

        $bundle->loadFromCatalog($catalog);

        $I->assertEquals(['existing.js', 'js/app.abc123.js'], $bundle->js);
        $I->assertEquals(['existing.css', 'css/app.def456.css'], $bundle->css);
    }
}
