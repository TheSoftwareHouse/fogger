<?php

namespace App\Config;

use App\Config\Model\Mongo\Config;
use Symfony\Component\Serializer\Encoder\YamlEncoder;
use Symfony\Component\Serializer\SerializerInterface;

class ConfigLoader
{
    public const DEFAULT_FILENAME = 'fogger.yaml';
    public const DIRECTORY = '/fogger';

    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public static function forgePath(string $filename): string
    {
        return sprintf("%s/%s", self::DIRECTORY, $filename ?? self::DEFAULT_FILENAME);
    }

    public function save(Config $config, ?string $filename = null)
    {
        file_put_contents(
            self::forgePath($filename),
            $this->serializer->serialize($config, YamlEncoder::FORMAT, ['yaml_inline' => 4])
        );
    }

    public function load(string $filename): Config
    {
        /** @var Config $config */
        $config = $this->serializer->deserialize(
            file_get_contents(self::forgePath($filename)),
            Config::class,
            YamlEncoder::FORMAT
        );

        return $config;
    }
}
