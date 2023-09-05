<?php

namespace SampleNinja\LaravelCdn\Tests\Juhasev\laravelcdn;

use Mockery as M;
use SampleNinja\LaravelCdn\Tests\TestCase;

/**
 * Class AssetTest.
 *
 * @category Test
 *
 * @author  Mahmoud Zalt <mahmoud@vinelab.com>
 */
class AssetTest extends TestCase
{
    private \SampleNinja\LaravelCdn\Asset $asset;

    public function setUp(): void
    {
        parent::setUp();

        $this->asset = new \SampleNinja\LaravelCdn\Asset();
    }

    public function tearDown(): void
    {
        M::close();
        parent::tearDown();
    }

    public function testInitReturningAssetObject(): void
    {
        $dir = 'foo';

        $result = $this->asset->init([
            'include' => [
                'directories' => $dir,
            ],
        ]);

        // check the returned object is of type SampleNinja\LaravelCdn\Asset
        $this->assertEquals($result, $this->asset);
    }

    public function testIncludedDirectories(): void
    {
        $dir = 'foo';

        $this->asset->init([
            'include' => [
                'directories' => $dir,
            ],
        ]);

        $result = $this->asset->getIncludedDirectories();

        $this->assertEquals($result, $dir);
    }

    public function testIncludedExtensions(): void
    {
        $ext = 'foo';

        $this->asset->init([
            'include' => [
                'extensions' => $ext,
            ],
        ]);

        $result = $this->asset->getIncludedExtensions();

        $this->assertEquals($result, $ext);
    }

    public function testIncludedPatterns(): void
    {
        $pat = 'foo';

        $this->asset->init([
            'include' => [
                'patterns' => $pat,
            ],
        ]);

        $result = $this->asset->getIncludedPatterns();

        $this->assertEquals($result, $pat);
    }

    public function testExcludedDirectories(): void
    {
        $dir = 'foo';

        $this->asset->init([
            'exclude' => [
                'directories' => $dir,
            ],
        ]);

        $result = $this->asset->getExcludedDirectories();

        $this->assertEquals($result, $dir);
    }

    public function testExcludedFiles(): void
    {
        $dir = 'foo';

        $this->asset->init([
            'exclude' => [
                'files' => $dir,
            ],
        ]);

        $result = $this->asset->getExcludedFiles();

        $this->assertEquals($result, $dir);
    }

    public function testExcludedExtensions(): void
    {
        $dir = 'foo';

        $this->asset->init([
            'exclude' => [
                'extensions' => $dir,
            ],
        ]);

        $result = $this->asset->getExcludedExtensions();

        $this->assertEquals($result, $dir);
    }

    public function testExcludedPatterns()
    {
        $dir = 'foo';

        $this->asset->init([
            'exclude' => [
                'patterns' => $dir,
            ],
        ]);

        $result = $this->asset->getExcludedPatterns();

        $this->assertEquals($result, $dir);
    }

    public function testExcludedHidden(): void
    {
        $this->asset->init([
            'exclude' => [
                'hidden' => true,
            ],
        ]);

        $result = $this->asset->getExcludeHidden();

        $this->assertTrue($result);
    }
}
