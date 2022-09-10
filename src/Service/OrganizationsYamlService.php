<?php

declare(strict_types=1);

namespace App\Service;

use RuntimeException;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Yaml\Exception\DumpException;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;
use Symfony\Contracts\Cache\CacheInterface;
use UnexpectedValueException;

class OrganizationsYamlService
{
    private const YAML_FILE_PATH = 'organizations.yaml';

    private const YAML_CACHE_KEY_NAME = 'organizations_list_cache_key_name';

    private ParameterBagInterface $parameterBag;

    private CacheInterface $cache;

    public function __construct(
        ParameterBagInterface $parameterBag
    ) {
        $this->parameterBag = $parameterBag;
        $this->cache = new FilesystemAdapter();
    }

    public function write(array $organizations): void
    {
        try {
            $yaml = Yaml::dump(
                ['organizations' => array_values($organizations)],
                5,
                2,
            );

            $yamlFilePath = $this->getFilePath();
            file_put_contents($yamlFilePath, $yaml);
            $this->cache->delete(self::YAML_CACHE_KEY_NAME);
        } catch (DumpException $e) {
            throw new RuntimeException('Unable to dump yaml the file');
        }
    }

    public function read(): array
    {
        return $this->cache->get(
            self::YAML_CACHE_KEY_NAME,
            function () {
                $yamlFilePath = $this->getFilePath();

                try {
                    $parsedYamlContent = Yaml::parseFile(
                        $yamlFilePath,
                        Yaml::PARSE_EXCEPTION_ON_INVALID_TYPE
                    );
                } catch (ParseException $exception) {
                    throw new RuntimeException(
                        sprintf(
                            'Unable to parse the YAML string: %s',
                            $exception->getMessage()
                        )
                    );
                }

                $organizations = $parsedYamlContent['organizations'] ?? null;

                if (null === $organizations) {
                    $message = sprintf(
                        'Unable to parse file "%s" and get organizations',
                        $yamlFilePath)
                    ;

                    throw new UnexpectedValueException($message);
                }

                return $organizations;
            }
        );
    }

    private function getFilePath(): string
    {
        $yamlFilePath = sprintf(
            '%s/%s',
            $this->parameterBag->get('kernel.project_dir'),
            self::YAML_FILE_PATH
        );

        if (false === file_exists($yamlFilePath)) {
            throw new FileNotFoundException($yamlFilePath);
        }

        return $yamlFilePath;
    }
}
