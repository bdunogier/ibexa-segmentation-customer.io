<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\SegmentationCustomerIo\Segmentation;

use eZ\Publish\API\Repository\PermissionResolver;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\UserService;
use eZ\Publish\API\Repository\Values\User\User;
use Ibexa\Platform\Contracts\Segmentation\SegmentationServiceInterface;
use Ibexa\Platform\Segmentation\Value\Segment;
use Ibexa\Platform\Segmentation\Value\SegmentCreateStruct;
use Ibexa\Platform\Segmentation\Value\SegmentGroup;
use Ibexa\Platform\Segmentation\Value\SegmentGroupCreateStruct;
use Ibexa\Platform\Segmentation\Value\SegmentGroupUpdateStruct;
use Ibexa\Platform\Segmentation\Value\SegmentUpdateStruct;

/**
 * https://customer.io/docs/api/
 */
class CustomerIoSegmentationService implements SegmentationServiceInterface
{
    /**
     * @var \Ibexa\SegmentationCustomerIo\Segmentation\CustomerIoHttpClient
     */
    private $client;

    /**
     * @var \eZ\Publish\API\Repository\PermissionResolver
     */
    private $permissionResolver;
    /**
     * @var \eZ\Publish\API\Repository\UserService
     */
    private $userService;
    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    private $repository;

    public function __construct(CustomerIoHttpClient $client, Repository $repository, PermissionResolver $permissionResolver, UserService $userService)
    {
        $this->client = $client;
        $this->permissionResolver = $permissionResolver;
        $this->userService = $userService;
        $this->repository = $repository;
    }

    public function loadSegment(int $segmentId): Segment
    {
        $segmentData = $this->client->loadSegment($segmentId);

        return $this->buildSegment($segmentData);
    }

    public function createSegment(SegmentCreateStruct $createStruct): Segment
    {
        $segmentData = $this->client->createSegment([
            'name' => $createStruct->name,
        ]);

        return $this->buildSegment($segmentData);

    }

    public function updateSegment(int $segmentId, SegmentUpdateStruct $updateStruct): Segment
    {
        // TODO: Implement updateSegment() method.
    }

    public function removeSegment(Segment $segment): void
    {
        $this->client->deleteSegment($segment->id);
    }

    /**
     * @inheritDoc
     */
    public function loadSegmentsAssignedToGroup(SegmentGroup $segmentGroup): array
    {
        if ($segmentGroup->id !== 1) {
            throw new \Exception ("Customer.io does not support segment groups");
        }

        return array_map(
            function(array $segmentRow) {
                return $this->buildSegment($segmentRow);
            },
            $this->client->getSegments()
        );
    }

    public function loadSegmentGroup(int $segmentGroupId): SegmentGroup
    {
        if ($segmentGroupId !== 1) {
            throw new \Exception ("Customer.io does not support segment groups");
        }

        return $this->buildSegmentGroup();
    }

    /**
     * @throws \Exception the feature isn't supported
     */
    public function createSegmentGroup(SegmentGroupCreateStruct $createStruct): SegmentGroup
    {
        throw new \Exception ("Customer.io does not support segment groups");
    }

    /**
     * @throws \Exception the feature isn't supported
     */
    public function updateSegmentGroup(int $segmentGroupId, SegmentGroupUpdateStruct $updateStruct): SegmentGroup
    {
        throw new \Exception ("Customer.io does not support segment groups");
    }

    /**
     * @throws \Exception the feature isn't supported
     */
    public function removeSegmentGroup(SegmentGroup $segmentGroup): void
    {
        throw new \Exception ("Customer.io does not support segment groups");
    }

    /**
     * @inheritDoc
     */
    public function loadSegmentGroups(): array
    {
        return [$this->buildSegmentGroup()];
    }

    /**
     * @inheritDoc
     */
    public function loadSegmentsAssignedToUser(User $user): array
    {
        return array_map(
            function($row) {
                return $this->buildSegment($row);
            },
            $this->client->loadSegmentsAssignedToUser($user->email)
        );
    }

    /**
     * @inheritDoc
     */
    public function loadSegmentsAssignedToCurrentUser(): array
    {
        return $this->loadSegmentsAssignedToUser($this->getCurrentUser());
    }

    public function isUserAssignedToSegment(User $user, Segment $segment): bool
    {
        // curl -i https://beta-api.customer.io/v1/api/customers/:id/segments
    }

    public function assignUserToSegment(User $user, Segment $segment): void
    {
        $this->client->assignUserToSegment($user->email, $segment->id);
    }

    public function unassignUserFromSegment(User $user, Segment $segment): void
    {
        $this->client->unassignUserFromSegment($user->email, $segment->id);
    }

    private function buildSegmentGroup()
    {
        return new SegmentGroup(['id' => 1, 'identifier' => 'customer.io', 'name' => 'Customer.io']);
    }

    private function buildSegment(array $segmentData): Segment
    {
        return new Segment(
            [
                'id' => $segmentData['id'],
                'identifier' => $segmentData['name'],
                'name' => $segmentData['name'],
                'group' => $this->buildSegmentGroup(),
            ]
        );
    }

    private function getCurrentUser(): User
    {
        return $this->repository->sudo(function() {
            return $this->userService->loadUser($this->permissionResolver->getCurrentUserReference()->getUserId());
        });
    }
}
