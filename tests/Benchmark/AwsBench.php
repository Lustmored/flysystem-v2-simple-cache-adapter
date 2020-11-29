<?php

namespace Tests\Lustmored\Flysystem\Cache\Benchmark;

use Aws\S3\S3Client;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use League\Flysystem\AwsS3V3\PortableVisibilityConverter;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\Visibility;
use Symfony\Component\Dotenv\Dotenv;

class AwsBench extends AbstractFilesystemBenchmark
{
    protected static function createFilesystemAdapter(): FilesystemAdapter
    {
        $dotenv = new Dotenv();
        $dir = dirname(__DIR__, 2);
        $dotenv->loadEnv("{$dir}/.env.bench");

        $client = new S3Client([
            'credentials' => [
                'key' => $_ENV['S3_KEY'],
                'secret' => $_ENV['S3_SECRET'],
            ],
            'region' => $_ENV['S3_REGION'],
            'version' => 'latest',
        ]);

        return new AwsS3V3Adapter($client, $_ENV['S3_BUCKET'], '', new PortableVisibilityConverter(Visibility::PRIVATE));
    }
}
