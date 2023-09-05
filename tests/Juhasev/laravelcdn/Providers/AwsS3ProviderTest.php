<?php

namespace SampleNinja\LaravelCdn\Tests\Juhasev\laravelcdn\Providers;

use Illuminate\Support\Collection;
use Mockery as M;
use SampleNinja\LaravelCdn\Tests\TestCase;

/**
 * Class AwsS3ProviderTest.
 *
 * @category Test
 *
 * @author   Mahmoud Zalt <mahmoud@vinelab.com>
 */
class AwsS3ProviderTest extends TestCase
{
    private string $url;
    private string $cdn_url;
    private string $path;
    private string $path_url;
    private string|int|array|null|false $pased_url;
    /**
     * @var M\LegacyMockInterface|M\MockInterface|\Symfony\Component\Console\Output\ConsoleOutput|(\Symfony\Component\Console\Output\ConsoleOutput&M\LegacyMockInterface)|(\Symfony\Component\Console\Output\ConsoleOutput&M\MockInterface)
     */
    private M\LegacyMockInterface|\Symfony\Component\Console\Output\ConsoleOutput|M\MockInterface $m_console;
    /**
     * @var \Aws\S3\S3Client|(\Aws\S3\S3Client&M\LegacyMockInterface)|(\Aws\S3\S3Client&M\MockInterface)|M\LegacyMockInterface|M\MockInterface
     */
    private \Aws\S3\S3Client|M\MockInterface|M\LegacyMockInterface $m_s3;
    /**
     * @var M\LegacyMockInterface|M\MockInterface|\Symfony\Component\Finder\SplFileInfo|(\Symfony\Component\Finder\SplFileInfo&M\LegacyMockInterface)|(\Symfony\Component\Finder\SplFileInfo&M\MockInterface)
     */
    private M\LegacyMockInterface|\Symfony\Component\Finder\SplFileInfo|M\MockInterface $m_spl_file;
    /**
     * @var M\LegacyMockInterface|M\MockInterface|\SampleNinja\LaravelCdn\Validators\Contracts\ProviderValidatorInterface|(\SampleNinja\LaravelCdn\Validators\Contracts\ProviderValidatorInterface&M\LegacyMockInterface)|(\SampleNinja\LaravelCdn\Validators\Contracts\ProviderValidatorInterface&M\MockInterface)
     */
    private \SampleNinja\LaravelCdn\Validators\Contracts\ProviderValidatorInterface|M\LegacyMockInterface|M\MockInterface $m_validator;
    /**
     * @var M\LegacyMockInterface|M\MockInterface|\SampleNinja\LaravelCdn\CdnHelper|(\SampleNinja\LaravelCdn\CdnHelper&M\LegacyMockInterface)|(\SampleNinja\LaravelCdn\CdnHelper&M\MockInterface)
     */
    private \SampleNinja\LaravelCdn\CdnHelper|M\LegacyMockInterface|M\MockInterface $m_helper;
    private \SampleNinja\LaravelCdn\Validators\Contracts\ProviderValidatorInterface|M\LegacyMockInterface|M\MockInterface $p_awsS3Provider;

    public function setUp(): void
    {
        parent::setUp();

        $this->url = 'http://www.google.com';
        $this->cdn_url = 'http://my-bucket-name.www.google.com/public/css/cool/style.css';
        $this->path = 'public/css/cool/style.css';
        $this->path_url = 'http://www.google.com/public/css/cool/style.css';
        $this->pased_url = parse_url($this->url);

        $this->m_console = M::mock('Symfony\Component\Console\Output\ConsoleOutput');
        $this->m_console->shouldReceive('writeln')->atLeast(2);

        $this->m_validator = M::mock('SampleNinja\LaravelCdn\Validators\Contracts\ProviderValidatorInterface');
        $this->m_validator->shouldReceive('validate');

        $this->m_helper = M::mock('SampleNinja\LaravelCdn\CdnHelper');
        $this->m_helper->shouldReceive('parseUrl')
            ->andReturn($this->pased_url);

        $this->m_spl_file = M::mock('Symfony\Component\Finder\SplFileInfo');
        $this->m_spl_file->shouldReceive('getPathname')->andReturn('juhasev/laravelcdn/tests/Juhasev/laravelcdn/AwsS3ProviderTest.php');
        $this->m_spl_file->shouldReceive('getRealPath')->andReturn(__DIR__.'/AwsS3ProviderTest.php');

        $this->p_awsS3Provider = M::mock('\SampleNinja\LaravelCdn\Providers\AwsS3Provider[connect]', array(
            $this->m_console,
            $this->m_validator,
            $this->m_helper,
        ));

        $this->m_s3 = M::mock('Aws\S3\S3Client');
        $this->m_s3->shouldReceive('factory')->andReturn('Aws\S3\S3Client');
        $m_command = M::mock('Aws\Command');
        $this->m_s3->shouldReceive('getCommand')->andReturn($m_command);
        $m_command1 = M::mock('Aws\Result')->shouldIgnoreMissing();
        $this->m_s3->shouldReceive('listObjects')->andReturn($m_command1);
        $this->m_s3->shouldReceive('execute');
        $this->p_awsS3Provider->setS3Client($this->m_s3);

        $this->p_awsS3Provider->shouldReceive('connect')->andReturn(true);
    }

    public function tearDown(): void
    {
        M::close();
        parent::tearDown();
    }

    public function testInitializingObject(): void
    {
        $configurations = [
            'default' => 'AwsS3',
            'url' => 'https://s3.amazonaws.com',
            'threshold' => 10,
            'providers' => [
                'aws' => [
                    's3' => [
                        'region' => 'us-standard',
                        'version' => 'latest',
                        'buckets' => [
                            'my-bucket-name' => '*',
                        ],
                        'acl' => 'public-read',
                        'cloudfront' => [
                            'use' => false,
                            'cdn_url' => null,
                        ],
                        'metadata' => [],
                        'expires' => gmdate('D, d M Y H:i:s T', strtotime('+5 years')),
                        'cache-control' => 'max-age=2628000',
                        'http' => null,
                    ],
                ],
            ],
        ];

        $awsS3Provider_obj = $this->p_awsS3Provider->init($configurations);

        $this->assertInstanceOf('SampleNinja\LaravelCdn\Providers\AwsS3Provider', $awsS3Provider_obj);
    }

    public function testUploadingAssets(): void
    {
        $configurations = [
            'default' => 'AwsS3',
            'url' => 'https://s3.amazonaws.com',
            'threshold' => 10,
            'providers' => [
                'aws' => [
                    's3' => [
                        'region' => 'us-standard',
                        'version' => 'latest',
                        'buckets' => [
                            'my-bucket-name' => '*',
                        ],
                        'acl' => 'public-read',
                        'cloudfront' => [
                            'use' => false,
                            'cdn_url' => null,
                        ],
                        'metadata' => [],
                        'expires' => gmdate('D, d M Y H:i:s T', strtotime('+5 years')),
                        'cache-control' => 'max-age=2628000',
                        'http' => null,
                    ],
                ],
            ],
        ];

        $this->p_awsS3Provider->init($configurations);

        $result = $this->p_awsS3Provider->upload(new Collection([$this->m_spl_file]));

        $this->assertTrue($result);
    }

    public function testUrlGenerator(): void
    {
        $configurations = [
            'default' => 'AwsS3',
            'url' => 'https://s3.amazonaws.com',
            'threshold' => 10,
            'providers' => [
                'aws' => [
                    's3' => [
                        'region' => 'us-standard',
                        'version' => 'latest',
                        'buckets' => [
                            'my-bucket-name' => '*',
                        ],
                        'acl' => 'public-read',
                        'cloudfront' => [
                            'use' => false,
                            'cdn_url' => null,
                        ],
                        'metadata' => [],
                        'expires' => gmdate('D, d M Y H:i:s T', strtotime('+5 years')),
                        'cache-control' => 'max-age=2628000',
                        'http' => null,
                    ],
                ],
            ],
        ];

        $this->p_awsS3Provider->init($configurations);

        $result = $this->p_awsS3Provider->urlGenerator($this->path);

        $this->assertEquals($this->cdn_url, $result);
    }

    public function testEmptyUrlGenerator(): void
    {
        $configurations = [
            'default' => 'AwsS3',
            'url' => 'https://s3.amazonaws.com',
            'threshold' => 10,
            'providers' => [
                'aws' => [
                    's3' => [
                        'region' => 'us-standard',
                        'version' => 'latest',
                        'buckets' => [
                            '' => '*',
                        ],
                        'acl' => 'public-read',
                        'cloudfront' => [
                            'use' => false,
                            'cdn_url' => null,
                        ],
                        'metadata' => [],
                        'expires' => gmdate('D, d M Y H:i:s T', strtotime('+5 years')),
                        'cache-control' => 'max-age=2628000',
                        'http' => null,
                    ],
                ],
            ],
        ];

        $this->p_awsS3Provider->init($configurations);

        $result = $this->p_awsS3Provider->urlGenerator($this->path);

        $this->assertEquals($this->path_url, $result);
    }
}
