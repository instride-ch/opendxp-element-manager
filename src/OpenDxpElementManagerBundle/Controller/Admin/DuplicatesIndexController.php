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

namespace Instride\Bundle\OpenDxpElementManagerBundle\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Instride\Bundle\OpenDxpElementManagerBundle\Metadata\DuplicatesIndex\MetadataInterface;
use Instride\Bundle\OpenDxpElementManagerBundle\Metadata\DuplicatesIndex\MetadataRegistryInterface;
use Instride\Bundle\OpenDxpElementManagerBundle\Model\PotentialDuplicateInterface;
use Instride\Bundle\OpenDxpElementManagerBundle\Repository\PotentialDuplicateRepositoryInterface;
use OpenDxp\Bundle\AdminBundle\Controller\AdminAbstractController;
use OpenDxp\Tool\Admin;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;

class DuplicatesIndexController extends AdminAbstractController
{
    public function __construct(
        private readonly MetadataRegistryInterface $metadataRegistry,
        private readonly PotentialDuplicateRepositoryInterface $potentialDuplicateRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly SerializerInterface $serializer,
        private readonly ParameterBagInterface $parameters,
    ) {
    }

    /**
     * @throws ExceptionInterface
     */
    public function listAction(): JsonResponse
    {
        return $this->jsonView($this->metadataRegistry->all(), ['List']);
    }

    /**
     * @throws ExceptionInterface
     */
    public function getAction(Request $request): JsonResponse
    {
        $user = Admin::getCurrentUser();

        if (!$user || !$user->isAllowed('opendxp_element_manager')) {
            throw new AccessDeniedHttpException('Access denied.');
        }

        $resource = $this->findByClassNameOr404($request->get('className'));

        return $this->jsonView(
            [
                'data' => $resource,
                'options' => [
                    'merge_supported' => $this->parameters->get('opendxp_element_manager.merge_supported'),
                ],
                'success' => true,
            ],
            ['Detailed']
        );
    }

    /**
     * @throws ExceptionInterface
     * @throws \Exception
     */
    public function getPotentialDuplicatesAction(Request $request): JsonResponse
    {
        $declined = $request->query->get('declined', false);
        $metadata = $this->findByClassNameOr404($request->query->get('className'));
        $offset = (int) $request->query->get('offset', 0);
        $limit = (int) $request->query->get('limit', 50);

        $declined = $declined === 'true';

        $result = $this->potentialDuplicateRepository->findForClassName($metadata->getClassName(), $declined, $offset, $limit);
        $count = $this->potentialDuplicateRepository->findCountForClassName($metadata->getClassName(), $declined);

        $listResult = [];

        foreach ($result as $res) {
            $fromObject = $res->getDuplicateFrom()->getObject();
            $toObject = $res->getDuplicateTo()->getObject();

            $fromResult = [
                'objectId' => $fromObject->getId(),
                'extId' => \sprintf('%s-%s-from', $fromObject->getId(), $res->getId()),
            ];
            $toResult = [
                'objectId' => $toObject->getId(),
                'extId' => \sprintf('%s-%s-to', $toObject->getId(), $res->getId()),
            ];

            foreach ($metadata->getListFields() as $listField) {
                if (!\is_array($listField)) {
                    $listField = [$listField];
                }

                $listFieldId = \implode(',', $listField);

                $fromResult[$listFieldId] = [];
                $toResult[$listFieldId] = [];

                foreach ($listField as $field) {
                    $fromResult[$listFieldId][] = $fromObject->get($field);
                    $toResult[$listFieldId][] = $toObject->get($field);
                }

                $fromResult[$listFieldId] = \implode(' ', $fromResult[$listFieldId]);
                $toResult[$listFieldId] = \implode(' ', $toResult[$listFieldId]);
            }

            $fromResult['duplicationId'] = $res->getId();
            $fromResult['declined'] = $res->getDeclined();
            $fromResult['objectIdOther'] = $toObject->getId();
            $fromResult['_isFirstColumn'] = true;

            $toResult['duplicationId'] = $res->getId();
            $toResult['declined'] = $res->getDeclined();
            $toResult['objectIdOther'] = $fromObject->getId();
            $toResult['_isFirstColumn'] = false;

            $listResult[] = $fromResult;
            $listResult[] = $toResult;
        }

        return $this->jsonView([
            'total' => $count * 2,
            'data' => $listResult,
            'success' => true,
        ], ['Detailed']);
    }

    /**
     * @throws ExceptionInterface
     */
    public function declineDuplicationAction(Request $request): JsonResponse
    {
        $potentialDuplicate = $this->potentialDuplicateRepository->find($request->request->get('id'));

        if (!$potentialDuplicate instanceof PotentialDuplicateInterface) {
            throw $this->createNotFoundException();
        }

        $potentialDuplicate->setDeclined(true);

        $this->entityManager->persist($potentialDuplicate);
        $this->entityManager->flush();

        return $this->jsonView(['success' => true]);
    }

    /**
     * @throws ExceptionInterface
     */
    public function unDeclineDuplicationAction(Request $request): JsonResponse
    {
        $potentialDuplicate = $this->potentialDuplicateRepository->find($request->request->get('id'));

        if (!$potentialDuplicate instanceof PotentialDuplicateInterface) {
            throw $this->createNotFoundException();
        }

        $potentialDuplicate->setDeclined(false);

        $this->entityManager->persist($potentialDuplicate);
        $this->entityManager->flush();

        return $this->jsonView(['success' => true]);
    }

    protected function findByClassNameOr404(string $className): MetadataInterface
    {
        if (!$this->metadataRegistry->has($className)) {
            throw $this->createNotFoundException(\sprintf('The "%s" has not been found', $className));
        }

        return $this->metadataRegistry->get($className);
    }

    /**
     * @throws ExceptionInterface
     */
    private function jsonView(mixed $data, array $groups = []): JsonResponse
    {
        $context = $groups === [] ? [] : ['duplicate_index_groups' => $groups];
        $json = $this->serializer->serialize($data, 'json', $context);

        return new JsonResponse($json, 200, [], true);
    }
}
