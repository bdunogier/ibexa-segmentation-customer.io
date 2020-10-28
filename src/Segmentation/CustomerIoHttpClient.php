<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\SegmentationCustomerIo\Segmentation;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class CustomerIoHttpClient
{
    /**
     * @var \Symfony\Contracts\HttpClient\HttpClientInterface
     */
    private $httpClient;

    /**
     * @var \Symfony\Contracts\HttpClient\HttpClientInterface
     */
    private $trackHttpClient;

    public function __construct(HttpClientInterface $customerIoBetaHttpClient, HttpClientInterface $customerIoTrackHttpClient)
    {
        $this->httpClient = $customerIoBetaHttpClient;
        $this->trackHttpClient = $customerIoTrackHttpClient;
    }

    public function getSegments(): array
    {
        return $this->httpClient
            ->request('GET', 'https://beta-api.customer.io/v1/api/segments')
            ->toArray()['segments'];
    }

    public function deleteSegment($segmentId)
    {
        $this->httpClient->request('DELETE', 'https://beta-api.customer.io/v1/api/segments/' . $segmentId);
    }

    public function createSegment(array $data): array
    {
        return $this->httpClient->request('POST', 'https://beta-api.customer.io/v1/api/segments', [
            'json' => [
                'segment' => [
                    'name' => $data['name']
                ]
            ]
        ])->toArray()['segment'];
    }

    public function assignUserToSegment(string $email, int $segmentId): void
    {
        $url = sprintf('https://track.customer.io/api/v1/segments/%s/add_customers', $segmentId);

        $this->trackHttpClient->request('POST', $url, [
            'json' => ['ids' => [$email]]
        ]);
    }

    public function unassignUserFromSegment(string $email, int $segmentId): void
    {
        $url = sprintf('https://track.customer.io/api/v1/segments/%s/remove_customers', $segmentId);

        $this->trackHttpClient->request('POST', $url, [
            'json' => ['ids' => [$email]]
        ]);
    }

    public function loadSegmentsAssignedToUser(string $email): array
    {
        $url = sprintf('https://beta-api.customer.io/v1/api/customers/%s/segments', urlencode($email));

        return $this->httpClient->request('GET', $url )->toArray()['segments'];
    }

    public function loadSegment(int $segmentId)
    {
        $url = sprintf('https://beta-api.customer.io/v1/api/segments/%d', $segmentId);

        $return = $this->httpClient->request('GET', $url)->toArray()['segment'];

        print_r($return);
        return $return;
    }
}