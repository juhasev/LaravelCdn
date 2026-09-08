<?php

namespace SampleNinja\LaravelCdn\Providers;

use SampleNinja\LaravelCdn\Providers\Contracts\ProviderInterface;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * Class Provider.
 *
 * @category Drivers Abstract Class
 *
 * @author   Mahmoud Zalt <mahmoud@vinelab.com>
 */
abstract class Provider implements ProviderInterface
{
    /**
     * @var string
     */
    protected $key;

    /**
     * @var string
     */
    protected $secret;

    /**
     * @var string
     */
    protected $region;

    /**
     * @var string
     */
    protected $version;

    /**
     * @var string
     */
    protected $url;

    /**
     * @var ConsoleOutput
     */
    public $console;

    abstract public function upload($assets);
}
