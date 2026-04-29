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

namespace Instride\Bundle\OpenDxpElementManagerBundle;

use Composer\InstalledVersions;
use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Instride\Bundle\OpenDxpElementManagerBundle\DependencyInjection\CompilerPass\AddDataTransformersPass;
use Instride\Bundle\OpenDxpElementManagerBundle\DependencyInjection\CompilerPass\AddSaveHandlerPass;
use Instride\Bundle\OpenDxpElementManagerBundle\DependencyInjection\CompilerPass\AddSimilarityCheckerPass;
use Instride\Bundle\OpenDxpElementManagerBundle\DependencyInjection\OpenDxpElementManagerExtension;
use OpenDxp\Extension\Bundle\AbstractOpenDxpBundle;
use OpenDxp\Extension\Bundle\OpenDxpBundleAdminClassicInterface;
use OpenDxp\Extension\Bundle\Traits\BundleAdminClassicTrait;
use OpenDxp\Extension\Bundle\Traits\PackageVersionTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\Validator\DependencyInjection\AddConstraintValidatorsPass;

class OpenDxpElementManagerBundle extends AbstractOpenDxpBundle implements OpenDxpBundleAdminClassicInterface
{
    use BundleAdminClassicTrait;
    use PackageVersionTrait;

    /**
     * @inheritDoc
     */
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(DoctrineOrmMappingsPass::createXmlMappingDriver(
            [
                \realpath(__DIR__ . '/Resources/config/doctrine/model') => 'Instride\\Bundle\\OpenDxpElementManagerBundle\\Model',
            ],
            ['doctrine.default_entity_manager'],
        ));

        $container->addCompilerPass(new AddConstraintValidatorsPass());
        $container->addCompilerPass(new AddDataTransformersPass());
        $container->addCompilerPass(new AddSimilarityCheckerPass());
        $container->addCompilerPass(new AddSaveHandlerPass());
    }

    /**
     * @inheritDoc
     */
    public function getNiceName(): string
    {
        return 'OpenDXP Element Manager';
    }

    /**
     * @inheritDoc
     */
    public function getDescription(): string
    {
        return 'Manages OpenDXP Element\'s with ease.';
    }

    /**
     * @inheritDoc
     */
    protected function getComposerPackageName(): string
    {
        return 'instride/opendxp-element-manager';
    }

    public function getVersion(): string
    {
        $bundleName = 'instride/opendxp-element-manager';

        if (\class_exists(InstalledVersions::class) && InstalledVersions::isInstalled($bundleName)) {
            return InstalledVersions::getVersion($bundleName);
        }

        return '';
    }

    public function getJsPaths(): array
    {
        return [
            '/bundles/opendxpelementmanager/opendxp/js/duplication_index/item.js',
            '/bundles/opendxpelementmanager/opendxp/js/duplication_index/panel.js',
            '/bundles/opendxpelementmanager/opendxp/js/startup.js',
        ];
    }

    public function getCssPaths(): array
    {
        return [
            '/bundles/opendxpelementmanager/opendxp/css/opendxpelementmanager.css',
        ];
    }

    public function getContainerExtension(): ?ExtensionInterface
    {
        if (null === $this->extension) {
            $this->extension = new OpenDxpElementManagerExtension();
        }

        return $this->extension ?: null;
    }
}
