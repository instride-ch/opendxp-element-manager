<?php

declare(strict_types=1);

/**
 * OpenDXP Element Manager.
 *
 * LICENSE
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md and gpl-3.0.txt
 * files that are distributed with this source code.
 *
 * @copyright 2026 instride AG (https://instride.ch)
 * @license   https://github.com/instride-ch/opendxp-element-manager/blob/main/gpl-3.0.txt GNU General Public License version 3 (GPLv3)
 */

namespace Instride\Bundle\OpenDxpElementManagerBundle\DependencyInjection;

use Instride\Bundle\OpenDxpElementManagerBundle\SaveManager\DuplicationSaveHandler;
use Instride\Bundle\OpenDxpElementManagerBundle\SaveManager\NamingSchemeSaveHandler;
use Instride\Bundle\OpenDxpElementManagerBundle\SaveManager\ObjectSaveManagers;
use Instride\Bundle\OpenDxpElementManagerBundle\SaveManager\UniqueKeySaveHandler;
use Instride\Bundle\OpenDxpElementManagerBundle\SaveManager\ValidationSaveHandler;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Resource\DirectoryResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\File;

class OpenDxpElementManagerExtension extends Extension implements PrependExtensionInterface
{
    public function getAlias(): string
    {
        return 'opendxp_element_manager';
    }

    public function prepend(ContainerBuilder $container): void
    {
    }

    /**
     * @inheritDoc
     *
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader->load('services/duplication.yaml');
        $loader->load('services/save_manager.yaml');

        $this->registerDuplicationCheckerConfiguration($config['duplication'] ?? [], $container, $loader);

        $objectSaveManagers = new Definition(ObjectSaveManagers::class);
        $container->setDefinition(ObjectSaveManagers::class, $objectSaveManagers);

        foreach ($config['classes'] as $className => $classConfig) {
            $this->registerSaveManagerConfiguration(
                $container,
                $className,
                $classConfig ?? [],
                $objectSaveManagers
            );
        }
    }

    /**
     * @throws \Exception
     */
    private function registerSaveManagerConfiguration(
        ContainerBuilder $container,
        string $className,
        array $config,
        Definition $objectSaveManagers
    ): void {
        $definition = new Definition($config['save_manager_class']);
        $options = [
            'naming_scheme' => $config['naming_scheme']['options'] ?? null,
            'duplicates' => $config['duplicates']['options'] ?? null,
            'validations' => $config['validations']['options'] ?? null,
        ];

        if (isset($config['naming_scheme']['enabled']) && $config['naming_scheme']['enabled'] === true) {
            $namingDefinition = new Definition(NamingSchemeSaveHandler::class, [
                new Reference($config['naming_scheme']['service']),
            ]);

            $namingDefinition->setPublic(false);
            $container->setDefinition(
                \sprintf('save_manager.naming_scheme.%s', \strtolower($className)),
                $namingDefinition
            );

            $definition->addMethodCall('addSaveHandler', [
                new Reference(\sprintf('save_manager.naming_scheme.%s', \strtolower($className))),
            ]);
        }

        if (isset($config['unique_key']['enabled']) && $config['unique_key']['enabled'] === true) {
            $definition->addMethodCall('addSaveHandler', [new Reference(UniqueKeySaveHandler::class)]);
        }

        if (isset($config['validations']['enabled_on_save']) && $config['validations']['enabled_on_save'] === true) {
            $definition->addMethodCall('addSaveHandler', [new Reference(ValidationSaveHandler::class)]);
        }

        if (isset($config['duplicates']['enabled_on_save']) && $config['duplicates']['enabled_on_save'] === true) {
            $definition->addMethodCall('addSaveHandler', [new Reference(DuplicationSaveHandler::class)]);
        }

        foreach ($config['save_handlers'] ?? [] as $saveHandler) {
            $definition->addMethodCall('addSaveHandler', [new Reference($saveHandler)]);
        }

        $definition->addMethodCall('setOptions', [$options]);

        $container->setDefinition(\sprintf('save_manager.%s', \strtolower($className)), $definition);

        $objectSaveManagers->addMethodCall(
            'addSaveManager',
            [
                $className,
                new Reference(\sprintf('save_manager.%s', \strtolower($className))),
            ]
        );
        $container->setDefinition(ObjectSaveManagers::class, $objectSaveManagers);
    }

    /**
     * @throws \Exception
     */
    private function registerDuplicationCheckerConfiguration(
        array $config,
        ContainerBuilder $container,
        Loader\YamlFileLoader $loader
    ): void {
        $loader->load('services/duplication.yaml');

        $duplicationBuilder = $container->getDefinition('duplication_checker.builder');

        $files = ['yaml' => []];
        $this->registerDuplicationCheckerMapping($container, $config, $files);

        if (!empty($files['yaml'])) {
            $duplicationBuilder->addMethodCall('addYamlMappings', [$files['yaml']]);
        }

        if (!$container->getParameter('kernel.debug')) {
            $duplicationBuilder->addMethodCall('setMappingCache', [
                new Reference('duplication_checker.mapping.cache.adapter'),
            ]);
        }
    }

    /**
     * @param ContainerBuilder $container
     * @param array            $config
     * @param array            $files
     */
    private function registerDuplicationCheckerMapping(ContainerBuilder $container, array $config, array &$files): void
    {
        $fileRecorder = static function ($extension, $path) use (&$files) {
            $files[$extension][] = $path;
        };

        foreach ($container->getParameter('kernel.bundles_metadata') as $bundle) {
            $dirname = $bundle['path'];

            if ($container->fileExists($file = $dirname . '/Resources/config/duplication.yaml', false)) {
                $fileRecorder('yaml', $file);
            }

            if ($container->fileExists($dir = $dirname . '/Resources/config/duplication', '/^$/')) {
                $this->registerMappingFilesFromDir($dir, $fileRecorder);
            }
        }

        $projectDir = $container->getParameter('kernel.project_dir');

        if ($container->fileExists($dir = $projectDir . '/config/duplication', '/^$/')) {
            $this->registerMappingFilesFromDir($dir, $fileRecorder);
        }

        $this->registerMappingFilesFromConfig($container, $config, $fileRecorder);
    }

    private function registerMappingFilesFromDir($dir, callable $fileRecorder): void
    {
        $files = Finder::create()
            ->followLinks()
            ->files()
            ->in($dir)
            ->name('/\.(ya?ml)$/')
            ->sortByName();

        /** @var File $file */
        foreach ($files as $file) {
            $fileRecorder($file->getExtension(), \realpath($file->getPathname()));
        }
    }

    private function registerMappingFilesFromConfig(
        ContainerBuilder $container,
        array $config,
        callable $fileRecorder
    ): void {
        foreach ($config['mapping']['paths'] ?? [] as $path) {
            if (\is_dir($path)) {
                $this->registerMappingFilesFromDir($path, $fileRecorder);
                $container->addResource(new DirectoryResource($path, '/^$/'));
            } elseif ($container->fileExists($path, false)) {
                if (!\preg_match('/\.(ya?ml)$/', $path, $matches)) {
                    throw new \RuntimeException(
                        \sprintf('Unsupported mapping type in "%s", supported types is only YAML.', $path)
                    );
                }

                $fileRecorder($matches[1], $path);
            } else {
                throw new \RuntimeException(\sprintf('Could not open file or directory "%s".', $path));
            }
        }
    }
}
