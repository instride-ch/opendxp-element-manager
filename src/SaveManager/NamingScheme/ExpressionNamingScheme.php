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

namespace Instride\Bundle\OpenDxpElementManagerBundle\SaveManager\NamingScheme;

use OpenDxp\Bundle\CoreBundle\EventListener\Traits\OpenDxpContextAwareTrait;
use OpenDxp\Http\Request\Resolver\OpenDxpContextResolver;
use OpenDxp\Model\DataObject\AbstractObject;
use OpenDxp\Model\DataObject\Concrete;
use OpenDxp\Model\DataObject\Service;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExpressionNamingScheme implements NamingSchemeInterface
{
    use OpenDxpContextAwareTrait;

    public function __construct(
        private readonly ExpressionLanguage $expressionLanguage,
        private readonly RequestStack $requestStack,
        OpenDxpContextResolver $contextResolver
    ) {
        $this->setOpenDxpContextResolver($contextResolver);
    }

    /**
     * @inheritDoc
     */
    public function apply(Concrete $object, array $options): void
    {
        $optionsResolver = new OptionsResolver();
        $optionsResolver->setDefaults([
            'parent_path' => '/',
            'archive_path' => '/_tmp',
            'scheme' => '',
            'auto_prefix_path' => true,
            'skip_path_for_variant' => false,
            'initial_key_mapping' => null,
        ]);
        $optionsResolver->setRequired([
            'parent_path',
            'archive_path',
            'scheme',
            'auto_prefix_path',
        ]);

        $options = $optionsResolver->resolve($options);

        $autoPrefixPath = $options['auto_prefix_path'];
        $parentPath = $object->isPublished() ? $options['parent_path'] : $options['archive_path'];

        $namingScheme = $this->expressionLanguage->evaluate(
            $options['scheme'],
            \array_merge($options, ['object' => $object, 'path' => $parentPath])
        );

        // Map initial key to an object field
        if ($options['initial_key_mapping']) {
            $request = $this->requestStack->getMainRequest();

            if (
                null !== $request &&
                $this->matchesOpenDxpContext($request, OpenDxpContextResolver::CONTEXT_ADMIN) &&
                $object->getKey() &&
                !$object->getId()
            ) {
                $setter = \sprintf('set%s', \ucfirst($options['initial_key_mapping']));

                if (\method_exists($object, $setter)) {
                    $object->$setter($object->getKey());
                }
            }
        }

        if (\is_array($namingScheme)) {
            $key = $namingScheme[\count($namingScheme) - 1];
            unset($namingScheme[\count($namingScheme) - 1]);

            if ($autoPrefixPath) {
                $parentPath .= '/' . \implode('/', $namingScheme);
            } else {
                $parentPath = '/' . \implode('/', $namingScheme);
            }
        } else {
            $key = $namingScheme;
        }

        if (!$key) {
            $className = \strtolower(\ltrim(\preg_replace(
                '/[A-Z]([A-Z](?![a-z]))*/',
                '_$0',
                $object->getClassName()
            ), '_'));
            $key = \uniqid(\sprintf('%s_', $className), true);
        }

        $object->setKey($key);
        $parentPath = $this->correctPath($parentPath);

        if (!$options['skip_path_for_variant'] || $object->getType() !== AbstractObject::OBJECT_TYPE_VARIANT) {
            if ($parentObject = Concrete::getByPath($parentPath)) {
                $object->setParent($parentObject);
            } else {
                $object->setParent(Service::createFolderByPath($parentPath));
            }
        }

        $object->setKey(Service::getUniqueKey($object));
    }

    private function correctPath(string $path): string
    {
        return \str_replace('//', '/', $path);
    }
}
