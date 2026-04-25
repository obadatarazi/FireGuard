<?php

namespace App\Service;

use App\Entity\LocationLive;
use App\Entity\MobileSensor;
use Doctrine\ORM\EntityManagerInterface;

class CollectionSystemService
{
    private CenterService $centerService;
    private FireService $fireService;
    private ForestService $forestService;
    private DeviceService $deviceService;
    private EntityManagerInterface $em;

    public function __construct(
        CenterService $centerService,
        FireService $fireService,
        ForestService $forestService,
        DeviceService $deviceService,
        EntityManagerInterface $em
    ) {
        $this->centerService = $centerService;
        $this->fireService = $fireService;
        $this->forestService = $forestService;
        $this->deviceService = $deviceService;
        $this->em = $em;
    }

    public function getCollectionSystem(): array
    {
        $centers = $this->centerService->findAll();
        $fires = $this->fireService->findAll();
        $forestes = $this->forestService->findAll();
        $devices = $this->deviceService->findAll();
        $mobileRobots = $this->getMobileRobotsWithLiveLocation();

        return [
            'centers' => $centers,
            'fires' => $fires,
            'forestes' => $forestes,
            'devices' => $devices,
            'mobileRobots' => $mobileRobots,
        ];
    }

    private function getMobileRobotsWithLiveLocation(): array
    {
        // All mobile sensors (DRONE and CAR_ROBOT); live rows still keyed by source id when present.
        $robots = $this->em->getRepository(MobileSensor::class)->findAll();

        $liveRows = $this->em->getRepository(LocationLive::class)->findBy([
            'sourceType' => LocationLive::SOURCE_MOBILE_SENSOR,
        ]);

        $liveBySourceId = [];
        foreach ($liveRows as $liveRow) {
            if (!$liveRow instanceof LocationLive) {
                continue;
            }

            $liveBySourceId[$liveRow->getSourceId()] = [
                'latitude' => $liveRow->getLatitude(),
                'longitude' => $liveRow->getLongitude(),
                'recordedAt' => $liveRow->getRecordedAt()?->format(\DateTimeInterface::ATOM),
            ];
        }

        $items = [];
        foreach ($robots as $robot) {
            if (!$robot instanceof MobileSensor) {
                continue;
            }

            $center = $robot->getCenter();
            $items[] = [
                'id' => $robot->getId(),
                'name' => $robot->getName(),
                'type' => $robot->getType(),
                'status' => $robot->getStatus(),
                'center' => $center ? [
                    'id' => $center->getId(),
                    'name' => $center->getName(),
                ] : null,
                'staticLocation' => [
                    'latitude' => $robot->getLatitude(),
                    'longitude' => $robot->getLongitude(),
                ],
                'liveLocation' => $liveBySourceId[$robot->getId()] ?? null,
            ];
        }

        return $items;
    }
}