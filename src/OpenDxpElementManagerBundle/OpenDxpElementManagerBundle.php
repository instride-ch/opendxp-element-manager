<?php

declare(strict_types=1);

/**
 * OpenDxp Element Manager.
 *
 * LICENSE
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md and gpl-3.0.txt
 * files that are distributed with this source code.
 *
 * @copyright 2024 instride AG (https://instride.ch)
 * @license   https://github.com/instride-ch/opendxp-element-manager/blob/main/gpl-3.0.txt GNU General Public License version 3 (GPLv3)
 */

namespace Instride\Bundle\OpenDxpElementManagerBundle;

use Composer\InstalledVersions;
use CoreShop\Bundle\ResourceBundle\AbstractResourceBundle;
use CoreShop\Bundle\ResourceBundle\CoreShopResourceBundle;
use Instride\Bundle\OpenDxpElementManagerBundle\DependencyInjection\CompilerPass\AddDataTransformersPass;
use Instride\Bundle\OpenDxpElementManagerBundle\DependencyInjection\CompilerPass\AddSaveHandlerPass;
use Instride\Bundle\OpenDxpElementManagerBundle\DependencyInjection\CompilerPass\AddSimilarityCheckerPass;
use OpenDxp\Extension\Bundle\Traits\PackageVersionTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Validator\DependencyInjection\AddConstraintValidatorsPass;

class OpenDxpElementManagerBundle extends AbstractResourceBundle
{
    use PackageVersionTrait;

    /**
     * @inheritDoc
     */
    public function getSupportedDrivers(): array
    {
        return [
            CoreShopResourceBundle::DRIVER_DOCTRINE_ORM,
        ];
    }

    protected function getModelNamespace(): ?string
    {
        return 'Instride\Bundle\OpenDxpElementManagerBundle\Model';
    }

    /**
     * @inheritDoc
     */
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new AddConstraintValidatorsPass(
            'duplication_checker.validator_factory',
            'duplication_checker.constraint_validator'
        ));
        $container->addCompilerPass(new AddDataTransformersPass());
        $container->addCompilerPass(new AddSimilarityCheckerPass());
        $container->addCompilerPass(new AddSaveHandlerPass());
    }

    /**
     * @inheritDoc
     */
    public function getNiceName(): string
    {
        return 'OpenDxp Element Manager';
    }

    /**
     * @inheritDoc
     */
    public function getDescription(): string
    {
        return 'Manages OpenDxp Element\'s with ease.';
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
}
