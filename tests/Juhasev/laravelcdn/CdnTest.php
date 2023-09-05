<?php

namespace SampleNinja\LaravelCdn\Tests\Juhasev\laravelcdn;

use Illuminate\Support\Collection;
use Mockery as M;
use SampleNinja\LaravelCdn\Tests\TestCase;

/**
 * Class CdnTest.
 *
 * @category Test
 *
 * @author  Mahmoud Zalt <mahmoud@vinelab.com>
 */
class CdnTest extends TestCase
{
    public function tearDown(): void
    {
        M::close();
        parent::tearDown();
    }

    public function testPushCommandReturnTrue(): void
    {
        $m_asset = M::mock('SampleNinja\LaravelCdn\Contracts\AssetInterface');
        $m_asset->shouldReceive('init')
            ->once()
            ->andReturn($m_asset);
        $m_asset->shouldReceive('setAssets')
            ->once();

        $m_asset->shouldReceive('getAssets')
            ->once()
            ->andReturn(new Collection());

        $m_finder = M::mock('SampleNinja\LaravelCdn\Contracts\FinderInterface');
        $m_finder->shouldReceive('read')
            ->with($m_asset)
            ->once()
            ->andReturn(new Collection());

        $m_provider = M::mock('SampleNinja\LaravelCdn\Providers\Provider');
        $m_provider->shouldReceive('upload')
            ->once()
            ->andReturn(true);

        $m_provider_factory = M::mock('SampleNinja\LaravelCdn\Contracts\ProviderFactoryInterface');
        $m_provider_factory->shouldReceive('create')
            ->once()
            ->andReturn($m_provider);

        $m_helper = M::mock('SampleNinja\LaravelCdn\Contracts\CdnHelperInterface');
        $m_helper->shouldReceive('getConfigurations')
            ->once()
            ->andReturn([]);

        $cdn = new \SampleNinja\LaravelCdn\Cdn(
            $m_finder,
            $m_asset,
            $m_provider_factory,
            $m_helper
        );

        $result = $cdn->push();

        $this->assertTrue($result);
    }

    /**
     * Integration Test.
     */
    public function testPushCommand(): void
    {
        $configuration_file = [
            'bypass'    => false,
            'default'   => 'AwsS3',
            'url'       => 'https://s3.amazonaws.com',
            'threshold' => 10,
            'providers' => [
                'aws' => [
                    's3' => [
                        'region'      => 'us-standard',
                        'version'     => 'latest',
                        'buckets'     => [
                            'my-bucket-name' => '*',
                        ],
                        'acl'         => 'public-read',
                        'cloudfront'  => [
                            'use'     => false,
                            'cdn_url' => '',
                        ],
                        'metadata' => [],

                        'expires' => gmdate('D, d M Y H:i:s T', strtotime('+5 years')),

                        'cache-control' => 'max-age=2628000',
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

        $m_console = M::mock('Symfony\Component\Console\Output\ConsoleOutput');
        $m_console->shouldReceive('writeln')->atLeast(1);

        $finder = new \SampleNinja\LaravelCdn\Finder($m_console);

        $asset = new \SampleNinja\LaravelCdn\Asset();

        $provider_factory = new \SampleNinja\LaravelCdn\ProviderFactory();

        $m_config = M::mock('Illuminate\Config\Repository');
        $m_config->shouldReceive('get')
            ->with('cdn')
            ->once()
            ->andReturn($configuration_file);

        $helper = new \SampleNinja\LaravelCdn\CdnHelper($m_config);

        $m_console = M::mock('Symfony\Component\Console\Output\ConsoleOutput');
        $m_console->shouldReceive('writeln')->atLeast(2);

        $m_validator = M::mock('SampleNinja\LaravelCdn\Validators\Contracts\ProviderValidatorInterface');
        $m_validator->shouldReceive('validate');

        $m_helper = M::mock('SampleNinja\LaravelCdn\CdnHelper');

        $m_spl_file = M::mock('Symfony\Component\Finder\SplFileInfo');
        $m_spl_file->shouldReceive('getPathname')
            ->andReturn('SampleNinja\LaravelCdn/tests/Juhasev/laravelcdn/AwsS3ProviderTest.php');
        $m_spl_file->shouldReceive('getRealPath')
            ->andReturn(__DIR__.'/AwsS3ProviderTest.php');

        // partial mock
        $p_aws_s3_provider = M::mock('\SampleNinja\LaravelCdn\Providers\AwsS3Provider[connect]',
        [
            $m_console,
            $m_validator,
            $m_helper,
        ]);

        $m_s3 = M::mock('Aws\S3\S3Client');
        $m_s3->shouldReceive('factory')->andReturn('Aws\S3\S3Client');
        $m_command = M::mock('Aws\Command');
        $m_s3->shouldReceive('getCommand')->andReturn($m_command);
        $m_command1 = M::mock('Aws\Result')->shouldIgnoreMissing();
        $m_s3->shouldReceive('listObjects')->andReturn($m_command1);
        $m_s3->shouldReceive('execute');
        $p_aws_s3_provider->setS3Client($m_s3);

        $p_aws_s3_provider->shouldReceive('connect')->andReturn(true);

        \Illuminate\Support\Facades\App::shouldReceive('make')
            ->once()
            ->andReturn($p_aws_s3_provider);

        $cdn = new \SampleNInja\LaravelCdn\Cdn($finder,
            $asset,
            $provider_factory,
            $helper
        );

        $result = $cdn->push();

        $this->assertTrue($result);
    }
}
