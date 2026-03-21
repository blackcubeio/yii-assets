<?php

declare(strict_types=1);

/**
 * WebpackAssetBundleCest.php
 *
 * PHP Version 8.3+
 *
 * @author Philippe Gaultier <philippe@blackcube.io>
 * @copyright 2010-2026 Blackcube
 * @license https://blackcube.io/license
 */

namespace Blackcube\Assets\Tests\Unit;

use Blackcube\Assets\WebpackAssetBundle;
use Blackcube\Assets\ManifestAssetBundleInterface;
use Blackcube\Assets\Tests\Support\UnitTester;

/**
 * Unit tests for WebpackAssetBundle.
 *
 * @author Philippe Gaultier <philippe@blackcube.io>
 * @copyright 2010-2026 Blackcube
 * @license https://blackcube.io/license
 */
final class WebpackAssetBundleCest
{
    // ==================== Interface ====================

    public function testImplementsManifestInterface(UnitTester $I): void
    {
        $bundle = new WebpackAssetBundle();

        $I->assertInstanceOf(ManifestAssetBundleInterface::class, $bundle);
    }

    // ==================== Default values ====================

    public function testDefaultCatalogFile(UnitTester $I): void
    {
        $bundle = new WebpackAssetBundle();

        $I->assertEquals('assets-catalog.json', $bundle->getCatalogFile());
    }

    public function testDefaultBundlesEmpty(UnitTester $I): void
    {
        $bundle = new WebpackAssetBundle();

        $I->assertEquals([], $bundle->bundles);
    }

    public function testCustomCatalogFile(UnitTester $I): void
    {
        $bundle = new WebpackAssetBundle();
        $bundle->catalogFile = 'webpack-manifest.json';

        $I->assertEquals('webpack-manifest.json', $bundle->getCatalogFile());
    }

    // ==================== Auto-detection (empty bundles) ====================

    public function testAutoDetectBundlesWithPriorityOrder(UnitTester $I): void
    {
        $bundle = new WebpackAssetBundle();
        $catalog = [
            'app' => ['js' => 'js/app.js'],
            'manifest' => ['js' => 'js/manifest.js'],
            'vendors' => ['js' => 'js/vendors.js'],
        ];

        $bundle->loadFromCatalog($catalog);

        // Order should be: manifest, vendors, app
        $I->assertEquals([
            'js/manifest.js',
            'js/vendors.js',
            'js/app.js',
        ], $bundle->js);
    }

    public function testAutoDetectWithoutManifest(UnitTester $I): void
    {
        $bundle = new WebpackAssetBundle();
        $catalog = [
            'app' => ['js' => 'js/app.js'],
            'vendors' => ['js' => 'js/vendors.js'],
        ];

        $bundle->loadFromCatalog($catalog);

        // Order should be: vendors, app
        $I->assertEquals([
            'js/vendors.js',
            'js/app.js',
        ], $bundle->js);
    }

    public function testAutoDetectWithoutPriorityBundles(UnitTester $I): void
    {
        $bundle = new WebpackAssetBundle();
        $catalog = [
            'app' => ['js' => 'js/app.js'],
            'admin' => ['js' => 'js/admin.js'],
        ];

        $bundle->loadFromCatalog($catalog);

        // Order should be: app, admin (as they appear in catalog)
        $I->assertEquals([
            'js/app.js',
            'js/admin.js',
        ], $bundle->js);
    }

    // ==================== Explicit bundles ====================

    public function testExplicitBundlesOrder(UnitTester $I): void
    {
        $bundle = new WebpackAssetBundle();
        $bundle->bundles = ['app', 'vendors', 'manifest'];
        $catalog = [
            'manifest' => ['js' => 'js/manifest.js'],
            'vendors' => ['js' => 'js/vendors.js'],
            'app' => ['js' => 'js/app.js'],
        ];

        $bundle->loadFromCatalog($catalog);

        // Order should follow explicit bundles array
        $I->assertEquals([
            'js/app.js',
            'js/vendors.js',
            'js/manifest.js',
        ], $bundle->js);
    }

    public function testExplicitBundlesSkipsMissing(UnitTester $I): void
    {
        $bundle = new WebpackAssetBundle();
        $bundle->bundles = ['manifest', 'vendors', 'app', 'nonexistent'];
        $catalog = [
            'manifest' => ['js' => 'js/manifest.js'],
            'app' => ['js' => 'js/app.js'],
        ];

        $bundle->loadFromCatalog($catalog);

        $I->assertEquals([
            'js/manifest.js',
            'js/app.js',
        ], $bundle->js);
    }

    // ==================== JS and CSS loading ====================

    public function testLoadJsAndCss(UnitTester $I): void
    {
        $bundle = new WebpackAssetBundle();
        $catalog = [
            'manifest' => ['js' => 'js/manifest.js'],
            'vendors' => ['js' => 'js/vendors.js'],
            'app' => [
                'js' => 'js/app.js',
                'css' => 'css/app.css',
            ],
        ];

        $bundle->loadFromCatalog($catalog);

        $I->assertEquals([
            'js/manifest.js',
            'js/vendors.js',
            'js/app.js',
        ], $bundle->js);
        $I->assertEquals(['css/app.css'], $bundle->css);
    }

    // ==================== cssOnly filter ====================

    public function testCssOnlyFilter(UnitTester $I): void
    {
        $bundle = new WebpackAssetBundle();
        $bundle->cssOnly = ['styles'];
        $catalog = [
            'app' => [
                'js' => 'js/app.js',
                'css' => 'css/app.css',
            ],
            'styles' => [
                'js' => 'js/styles.js',
                'css' => 'css/styles.css',
            ],
        ];

        $bundle->loadFromCatalog($catalog);

        // styles should only have CSS, not JS
        $I->assertEquals(['js/app.js'], $bundle->js);
        $I->assertEquals(['css/app.css', 'css/styles.css'], $bundle->css);
    }

    // ==================== jsOnly filter ====================

    public function testJsOnlyFilter(UnitTester $I): void
    {
        $bundle = new WebpackAssetBundle();
        $bundle->jsOnly = ['manifest', 'vendors'];
        $catalog = [
            'manifest' => [
                'js' => 'js/manifest.js',
                'css' => 'css/manifest.css',
            ],
            'vendors' => [
                'js' => 'js/vendors.js',
                'css' => 'css/vendors.css',
            ],
            'app' => [
                'js' => 'js/app.js',
                'css' => 'css/app.css',
            ],
        ];

        $bundle->loadFromCatalog($catalog);

        // manifest and vendors should only have JS, not CSS
        $I->assertEquals([
            'js/manifest.js',
            'js/vendors.js',
            'js/app.js',
        ], $bundle->js);
        $I->assertEquals(['css/app.css'], $bundle->css);
    }

    // ==================== Empty catalog ====================

    public function testEmptyCatalog(UnitTester $I): void
    {
        $bundle = new WebpackAssetBundle();

        $bundle->loadFromCatalog([]);

        $I->assertEquals([], $bundle->js);
        $I->assertEquals([], $bundle->css);
    }

    // ==================== Preserves existing assets ====================

    public function testPreservesExistingAssets(UnitTester $I): void
    {
        $bundle = new WebpackAssetBundle();
        $bundle->js = ['existing.js'];
        $bundle->css = ['existing.css'];
        $catalog = [
            'app' => [
                'js' => 'js/app.js',
                'css' => 'css/app.css',
            ],
        ];

        $bundle->loadFromCatalog($catalog);

        $I->assertEquals(['existing.js', 'js/app.js'], $bundle->js);
        $I->assertEquals(['existing.css', 'css/app.css'], $bundle->css);
    }
}
