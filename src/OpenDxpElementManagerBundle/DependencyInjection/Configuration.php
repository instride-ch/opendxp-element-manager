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
 * @copyright 2026 instride AG (https://instride.ch)
 * @license   https://github.com/instride-ch/opendxp-element-manager/blob/main/gpl-3.0.txt GNU General Public License version 3 (GPLv3)
 */

namespace Instride\Bundle\OpenDxpElementManagerBundle\DependencyInjection;

use Instride\Bundle\OpenDxpElementManagerBundle\SaveManager\NamingScheme\ExpressionNamingScheme;
use Instride\Bundle\OpenDxpElementManagerBundle\SaveManager\ObjectSaveManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * @inheritDoc
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('opendxp_element_manager');
        $rootNode = $treeBuilder->getRootNode();

        $this->addDuplicationSection($rootNode);
        $this->addSaveManagerSection($rootNode);

        return $treeBuilder;
    }

    /**
     * @param ArrayNodeDefinition $rootNode
     */
    private function addSaveManagerSection(ArrayNodeDefinition $rootNode): void
    {
        $rootNode
            ->children()
                ->arrayNode('classes')
                    ->useAttributeAsKey('class')
                    ->arrayPrototype()
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('save_manager_class')->defaultValue(ObjectSaveManager::class)->end()
                            ->arrayNode('naming_scheme')
                                ->children()
                                    ->scalarNode('service')->defaultValue(ExpressionNamingScheme::class)->end()
                                    ->booleanNode('enabled')->defaultFalse()->end()
                                    ->arrayNode('options')
                                        ->useAttributeAsKey('name')
                                        ->prototype('scalar')->end()
                                        ->children()
                                            ->scalarNode('parent_path')->end()
                                            ->scalarNode('archive_path')->end()
                                            ->scalarNode('scheme')->info('Expressions are allowed here')->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('unique_key')
                                ->children()
                                    ->booleanNode('enabled')->defaultFalse()->end()
                                ->end()
                            ->end()
                            ->arrayNode('duplicates')
                                ->children()
                                    ->booleanNode('enabled_on_save')->defaultFalse()->end()
                                    ->arrayNode('options')
                                        ->useAttributeAsKey('name')
                                        ->prototype('scalar')->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('validations')
                                ->children()
                                    ->booleanNode('enabled_on_save')->defaultTrue()->end()
                                    ->arrayNode('options')
                                        ->useAttributeAsKey('name')
                                        ->prototype('scalar')->end()
                                        ->children()
                                            ->scalarNode('group')->defaultValue('opendxp_element_manager')->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('save_handlers')
                                ->useAttributeAsKey('service')
                                ->prototype('scalar')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end();
    }

    /**
     * @param ArrayNodeDefinition $rootNode
     */
    private function addDuplicationSection(ArrayNodeDefinition $rootNode): void
    {
        $rootNode
            ->children()
                ->arrayNode('duplication')
                    ->info('duplication configuration')
                    ->children()
                        ->arrayNode('mapping')
                            ->addDefaultsIfNotSet()
                            ->fixXmlConfig('path')
                            ->children()
                                ->arrayNode('paths')
                                    ->defaultValue([])
                                    ->prototype('scalar')->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }
}
