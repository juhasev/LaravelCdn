<?php

namespace SampleNinja\LaravelCdn\Tests\Juhasev\laravelcdn;

use Illuminate\Support\Collection;
use InvalidArgumentException;
use Mockery as M;
use SampleNinja\LaravelCdn\Tests\TestCase;

/**
 * Class FinderTest.
 *
 * @category Test
 *
 * @author  Mahmoud Zalt <mahmoud@vinelab.com>
 */
class FinderTest extends TestCase
{
    public function tearDown(): void
    {
        M::close();
        parent::tearDown();
    }

    public function testReadReturnCorrectDataType(): void
    {
        $asset_holder = new \SampleNinja\LaravelCdn\Asset();

        $asset_holder->init([
            'include' => [
                'directories' => [__DIR__],
            ],
        ]);

        $console_output = M::mock('Symfony\Component\Console\Output\ConsoleOutput');
        $console_output->shouldReceive('writeln')
            ->atLeast(1);

        $finder = new \SampleNinja\LaravelCdn\Finder($console_output);

        $result = $finder->read($asset_holder);

        $this->assertInstanceOf('Symfony\Component\Finder\SplFileInfo', $result->first());

        $this->assertEquals($result, new Collection($result->all()));
    }

    public function testReadThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $asset_holder = new \SampleNinja\LaravelCdn\Asset();

        $asset_holder->init(['include' => []]);

        $console_output = M::mock('Symfony\Component\Console\Output\ConsoleOutput');
        $console_output->shouldReceive('writeln')
            ->atLeast(1);

        $finder = new \SampleNinja\LaravelCdn\Finder($console_output);

        $finder->read($asset_holder);
    }
}
