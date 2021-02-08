<?php

namespace SampleNinja\LaravelCdn\Tests;

use Illuminate\Support\Collection;
use Mockery as M;

/**
 * Class FinderTest.
 *
 * @category Test
 *
 * @author  Mahmoud Zalt <mahmoud@vinelab.com>
 */
class FinderTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    public function tearDown(): void
    {
        M::close();
        parent::tearDown();
    }

    public function testReadReturnCorrectDataType()
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

        assertInstanceOf('Symfony\Component\Finder\SplFileInfo', $result->first());

        assertEquals($result, new Collection($result->all()));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testReadThrowsException()
    {
        $asset_holder = new \SampleNinja\LaravelCdn\Asset();

        $asset_holder->init(['include' => []]);

        $console_output = M::mock('Symfony\Component\Console\Output\ConsoleOutput');
        $console_output->shouldReceive('writeln')
            ->atLeast(1);

        $finder = new \SampleNinja\LaravelCdn\Finder($console_output);

        $finder->read($asset_holder);
    }
}
