<?php

namespace SampleNinja\LaravelCdn\Tests;

use Illuminate\Support\Facades\App;
use Mockery as M;
use SampleNinja\LaravelCdn\ProviderFactory;

/**
 * Class ProviderFactoryTest.
 *
 * @category Test
 *
 * @author  Mahmoud Zalt <mahmoud@vinelab.com>
 */
class ProviderFactoryTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->provider_factory = new ProviderFactory();
    }

    public function tearDown(): void
    {
        M::close();
        parent::tearDown();
    }

    public function testCreateReturnCorrectProviderObject()
    {
        $configurations = ['default' => 'AwsS3'];

        $m_aws_s3 = M::mock('SampleNinja\LaravelCdn\Providers\AwsS3Provider');

        App::shouldReceive('make')->once()->andReturn($m_aws_s3);

        $m_aws_s3->shouldReceive('init')
            ->with($configurations)
            ->once()
            ->andReturn($m_aws_s3);

        $provider = $this->provider_factory->create($configurations);

        $this->assertEquals($provider, $m_aws_s3);
    }

    public function testCreateThrowsExceptionWhenMissingDefaultConfiguration()
    {
        $configurations = ['default' => ''];

        $m_aws_s3 = M::mock('SampleNinja\LaravelCdn\Providers\AwsS3Provider');

        App::shouldReceive('make')->once()->andReturn($m_aws_s3);

        $this->provider_factory->create($configurations);
    }
}
