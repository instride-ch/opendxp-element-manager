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

use Instride\Bundle\OpenDxpElementManagerBundle\Controller\Admin\DuplicatesIndexController;
use Instride\Bundle\OpenDxpElementManagerBundle\Factory\Factory;
use Instride\Bundle\OpenDxpElementManagerBundle\Model\Duplicate;
use Instride\Bundle\OpenDxpElementManagerBundle\Model\DuplicateFalsePositive;
use Instride\Bundle\OpenDxpElementManagerBundle\Model\DuplicateFalsePositiveInterface;
use Instride\Bundle\OpenDxpElementManagerBundle\Model\DuplicateInterface;
use Instride\Bundle\OpenDxpElementManagerBundle\Model\DuplicateObject;
use Instride\Bundle\OpenDxpElementManagerBundle\Model\DuplicateObjectInterface;
use Instride\Bundle\OpenDxpElementManagerBundle\Model\PotentialDuplicate;
use Instride\Bundle\OpenDxpElementManagerBundle\Model\PotentialDuplicateInterface;
use Instride\Bundle\OpenDxpElementManagerBundle\Repository\DuplicateObjectRepository;
use Instride\Bundle\OpenDxpElementManagerBundle\Repository\DuplicateRepository;
use Instride\Bundle\OpenDxpElementManagerBundle\Repository\PotentialDuplicateRepository;
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
        $this->addModelsSection($rootNode);
        $this->addOpenDxpResourcesSection($rootNode);

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
                            ->arrayNode('duplicates_index')
                                ->children()
                                    ->booleanNode('enabled')->defaultFalse()->end()
                                    ->arrayNode('groups')
                                        ->useAttributeAsKey('name')
                                        ->arrayPrototype()
                                            ->children()
                                                ->scalarNode('name')->end()
                                                ->arrayNode('fields')
                                                    ->useAttributeAsKey('name')
                                                    ->variablePrototype()->end()
                                                ->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                    ->arrayNode('list_fields')
                                        ->prototype('array')
                                            ->prototype('scalar')
                                            ->end()
                                        ->end()
                                        ->defaultValue([['id', 'className']])
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

    /**
     * @param ArrayNodeDefinition $node
     */
    private function addOpenDxpResourcesSection(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
                ->arrayNode('opendxp_admin')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('js')
                            ->useAttributeAsKey('name')
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('css')
                            ->useAttributeAsKey('name')
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('editmode_js')
                            ->useAttributeAsKey('name')
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('editmode_css')
                            ->useAttributeAsKey('name')
                            ->prototype('scalar')->end()
                        ->end()
                        ->scalarNode('permissions')
                            ->cannotBeOverwritten()
                            ->defaultValue(['index', 'filter'])
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    /**
     * @param ArrayNodeDefinition $node
     */
    private function addModelsSection(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
                ->arrayNode('resources')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('duplicate')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('options')->end()
                                ->arrayNode('classes')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('model')->defaultValue(Duplicate::class)->cannotBeEmpty()->end()
                                        ->scalarNode('interface')->defaultValue(DuplicateInterface::class)->cannotBeEmpty()->end()
                                        ->scalarNode('factory')->defaultValue(Factory::class)->cannotBeEmpty()->end()
                                        ->scalarNode('repository')->defaultValue(DuplicateRepository::class)->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('duplicate_false_positive')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('options')->end()
                                ->arrayNode('classes')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('model')->defaultValue(DuplicateFalsePositive::class)->cannotBeEmpty()->end()
                                        ->scalarNode('interface')->defaultValue(DuplicateFalsePositiveInterface::class)->cannotBeEmpty()->end()
                                        ->scalarNode('factory')->defaultValue(Factory::class)->cannotBeEmpty()->end()
                                        ->scalarNode('repository')->cannotBeEmpty()->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('duplicate_object')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('options')->end()
                                ->arrayNode('classes')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('model')->defaultValue(DuplicateObject::class)->cannotBeEmpty()->end()
                                        ->scalarNode('interface')->defaultValue(DuplicateObjectInterface::class)->cannotBeEmpty()->end()
                                        ->scalarNode('factory')->defaultValue(Factory::class)->cannotBeEmpty()->end()
                                        ->scalarNode('repository')->defaultValue(DuplicateObjectRepository::class)->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('potential_duplicate')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('options')->end()
                                ->arrayNode('classes')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('model')->defaultValue(PotentialDuplicate::class)->cannotBeEmpty()->end()
                                        ->scalarNode('interface')->defaultValue(PotentialDuplicateInterface::class)->cannotBeEmpty()->end()
                                        ->scalarNode('admin_controller')->defaultValue(DuplicatesIndexController::class)->cannotBeEmpty()->end()
                                        ->scalarNode('factory')->defaultValue(Factory::class)->cannotBeEmpty()->end()
                                        ->scalarNode('repository')->defaultValue(PotentialDuplicateRepository::class)->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }
}
