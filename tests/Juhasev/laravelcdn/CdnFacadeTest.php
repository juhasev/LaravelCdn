<?php

namespace SampleNinja\LaravelCdn\Tests\Juhasev\laravelcdn;

use Mockery as M;
use SampleNinja\LaravelCdn\Exceptions\EmptyPathException;
use SampleNinja\LaravelCdn\Tests\TestCase;

/**
 * Class CdnFacadeTest.
 *
 * @category Test
 *
 * @author   Mahmoud Zalt <mahmoud@vinelab.com>
 */
class CdnFacadeTest extends TestCase
{
    private string $asset_path;
    private string $path_path;
    private string $asset_url;
    /**
     * @var M\LegacyMockInterface|M\MockInterface|\SampleNinja\LaravelCdn\Providers\AwsS3Provider|(\SampleNinja\LaravelCdn\Providers\AwsS3Provider&M\LegacyMockInterface)|(\SampleNinja\LaravelCdn\Providers\AwsS3Provider&M\MockInterface)
     */
    private \SampleNinja\LaravelCdn\Providers\AwsS3Provider|M\LegacyMockInterface|M\MockInterface $provider;
    /**
     * @var M\LegacyMockInterface|M\MockInterface|\SampleNinja\LaravelCdn\Contracts\ProviderFactoryInterface|(\SampleNinja\LaravelCdn\Contracts\ProviderFactoryInterface&M\LegacyMockInterface)|(\SampleNinja\LaravelCdn\Contracts\ProviderFactoryInterface&M\MockInterface)
     */
    private \SampleNinja\LaravelCdn\Contracts\ProviderFactoryInterface|M\MockInterface|M\LegacyMockInterface $provider_factory;
    /**
     * @var M\LegacyMockInterface|M\MockInterface|\SampleNinja\LaravelCdn\Contracts\CdnHelperInterface|(\SampleNinja\LaravelCdn\Contracts\CdnHelperInterface&M\LegacyMockInterface)|(\SampleNinja\LaravelCdn\Contracts\CdnHelperInterface&M\MockInterface)
     */
    private M\LegacyMockInterface|M\MockInterface|\SampleNinja\LaravelCdn\Contracts\CdnHelperInterface $helper;
    private \SampleNinja\LaravelCdn\Validators\CdnFacadeValidator $validator;
    private \SampleNinja\LaravelCdn\CdnFacade $facade;

    public function setUp(): void
    {
        parent::setUp();

        $configuration_file = [
            'bypass'    => false,
            'default'   => 'AwsS3',
            'url'       => 'https://s3.amazonaws.com',
            'threshold' => 10,
            'providers' => [
                'aws' => [
                    's3' => [
                        'region'      => 'rrrrrrrrrrrgggggggggnnnnn',
                        'version'     => 'vvvvvvvvssssssssssnnnnnnn',
                        'buckets'     => [
                            'bbbuuuucccctttt' => '*',
                        ],
                        'acl'         => 'public-read',
                        'cloudfront'  => [
                            'use'     => false,
                            'cdn_url' => '',
                        ],
                    ],
                ],
            ],
            'include'   => [
                'directories' => [__DIR__],
                'extensions'  => [],
                'patterns'    => [],
            ],
            'exclude'   => [
                'directories' => [],
                'files'       => [],
                'extensions'  => [],
                'patterns'    => [],
                'hidden'      => true,
            ],
        ];

        $this->asset_path = 'foo/bar.php';
        $this->path_path = 'public/foo/bar.php';
        $this->asset_url = 'https://bucket.s3.amazonaws.com/public/foo/bar.php';

        $this->provider = M::mock('SampleNinja\LaravelCdn\Providers\AwsS3Provider');

        $this->provider_factory = M::mock('SampleNinja\LaravelCdn\Contracts\ProviderFactoryInterface');
        $this->provider_factory->shouldReceive('create')->once()->andReturn($this->provider);

        $this->helper = M::mock('SampleNinja\LaravelCdn\Contracts\CdnHelperInterface');
        $this->helper->shouldReceive('getConfigurations')->once()->andReturn($configuration_file);
        $this->helper->shouldReceive('cleanPath')->andReturn($this->asset_path);
        $this->helper->shouldReceive('startsWith')->andReturn(true);

        $this->validator = new \SampleNinja\LaravelCdn\Validators\CdnFacadeValidator();

        $this->facade = new \SampleNinja\LaravelCdn\CdnFacade(
            $this->provider_factory, $this->helper, $this->validator);
    }

    public function tearDown(): void
    {
        M::close();
        parent::tearDown();
    }

    public function testAssetIsCallingUrlGenerator(): void
    {
        $this->provider->shouldReceive('urlGenerator')
                       ->once()
                       ->andReturn($this->asset_url);

        $result = $this->facade->asset($this->asset_path);
        // assert is calling the url generator
        $this->assertEquals($result, $this->asset_url);
    }

    public function testPathIsCallingUrlGenerator(): void
    {
        $this->provider->shouldReceive('urlGenerator')
                       ->once()
                       ->andReturn($this->asset_url);

        $result = $this->facade->asset($this->path_path);
        // assert is calling the url generator
        $this->assertEquals($result, $this->asset_url);
    }

    public function testUrlGeneratorThrowsException(): void
    {
        try {
            $this->invokeMethod($this->facade, 'generateUrl', [null, null]);
        } catch (\Exception $e) {
            $this->assertInstanceOf(EmptyPathException::class, $e);
            $this->assertEquals('Path does not exist.', $e->getMessage());
        }
    }
}
