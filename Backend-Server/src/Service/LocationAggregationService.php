<?php

namespace App\Service;

use App\Entity\Address;
use App\Entity\Car;
use App\Entity\Center;
use App\Entity\Device;
use App\Entity\Fire;
use App\Entity\FireBrigade;
use App\Entity\Forest;
use App\Entity\MobileSensor;
use App\Entity\TaskFireBrigade;
use Doctrine\ORM\EntityManagerInterface;

class LocationAggregationService
{
    public const SOURCE_CENTER = 'CENTER';
    public const SOURCE_DEVICE = 'DEVICE';
    public const SOURCE_FOREST = 'FOREST';
    public const SOURCE_FIRE = 'FIRE';
    public const SOURCE_FIRE_BRIGADE = 'FIRE_BRIGADE';
    public const SOURCE_MOBILE_SENSOR = 'MOBILE_SENSOR';
    public const SOURCE_CAR = 'CAR';
    public const SOURCE_ADDRESS = 'ADDRESS';

    public function __construct(
        private readonly EntityManagerInterface $em
    ) {
    }

    /**
     * @return list<array{source: string, id: int, latitude: string, longitude: string, label: string, related: array<string, mixed>}>
     */
    public function getAllMarkers(): array
    {
        $items = [];

        foreach ($this->em->getRepository(Center::class)->findAll() as $center) {
            $items[] = $this->markerCenter($center);
        }

        foreach ($this->em->getRepository(Device::class)->findAll() as $device) {
            $items[] = $this->markerDevice($device);
        }

        foreach ($this->em->getRepository(Forest::class)->findAll() as $forest) {
            $items[] = $this->markerForest($forest);
        }

        foreach ($this->em->getRepository(Fire::class)->findAll() as $fire) {
            $items[] = $this->markerFire($fire);
        }

        foreach ($this->em->getRepository(FireBrigade::class)->findAll() as $brigade) {
            $items[] = $this->markerFireBrigade($brigade);
        }

        foreach ($this->em->getRepository(MobileSensor::class)->findAll() as $sensor) {
            $items[] = $this->markerMobileSensor($sensor);
        }

        foreach ($this->em->getRepository(Car::class)->findAll() as $car) {
            $items[] = $this->markerCar($car);
        }

        foreach ($this->findAllAddressesSafe() as $address) {
            $items[] = $this->markerAddress($address);
        }

        return $items;
    }

    /**
     * Scoped to brigade center + fires assigned to this brigade; omits standalone Address rows.
     *
     * @return list<array{source: string, id: int, latitude: string, longitude: string, label: string, related: array<string, mixed>}>
     */
    public function getMarkersForFireBrigade(FireBrigade $brigade): array
    {
        $center = $brigade->getCenter();
        if (!$center) {
            return [];
        }

        $centerId = $center->getId();
        $items = [];

        $items[] = $this->markerCenter($center);

        $qb = $this->em->getRepository(MobileSensor::class)->createQueryBuilder('ms')
            ->where('ms.center = :center')
            ->setParameter('center', $center);
        foreach ($qb->getQuery()->getResult() as $sensor) {
            $items[] = $this->markerMobileSensor($sensor);
        }

        $qbB = $this->em->getRepository(FireBrigade::class)->createQueryBuilder('fb')
            ->where('fb.center = :center')
            ->setParameter('center', $center);
        foreach ($qbB->getQuery()->getResult() as $fb) {
            $items[] = $this->markerFireBrigade($fb);
        }

        $qbC = $this->em->getRepository(Car::class)->createQueryBuilder('c')
            ->where('c.center = :center')
            ->setParameter('center', $center);
        foreach ($qbC->getQuery()->getResult() as $car) {
            $items[] = $this->markerCar($car);
        }

        $fires = $this->em->createQueryBuilder()
            ->select('f')
            ->from(Fire::class, 'f')
            ->join(TaskFireBrigade::class, 'tfb', 'WITH', 'tfb.fire = f')
            ->where('tfb.fireBrigade = :brigade')
            ->setParameter('brigade', $brigade)
            ->getQuery()
            ->getResult();

        $seenDeviceIds = [];
        $seenForestIds = [];

        foreach ($fires as $fire) {
            if (!$fire instanceof Fire) {
                continue;
            }
            $items[] = $this->markerFire($fire);
            $device = $fire->getDevice();
            $forest = $fire->getForest();
            if ($device && !isset($seenDeviceIds[$device->getId()])) {
                $seenDeviceIds[$device->getId()] = true;
                $items[] = $this->markerDevice($device);
            }
            if ($forest && !isset($seenForestIds[$forest->getId()])) {
                $seenForestIds[$forest->getId()] = true;
                $items[] = $this->markerForest($forest);
            }
        }

        return $items;
    }

    /**
     * @return list<Address>
     */
    private function findAllAddressesSafe(): array
    {
        try {
            return $this->em->getRepository(Address::class)->findAll();
        } catch (\Throwable) {
            return [];
        }
    }

    private function markerCenter(Center $center): array
    {
        return [
            'source' => self::SOURCE_CENTER,
            'id' => $center->getId(),
            'latitude' => (string) $center->getLatitude(),
            'longitude' => (string) $center->getLongitude(),
            'label' => $center->getName() ?? ('Center #' . $center->getId()),
            'related' => [
                'name' => $center->getName(),
                'nameAddress' => $center->getNameAddress(),
            ],
        ];
    }

    private function markerDevice(Device $device): array
    {
        $forest = $device->getForest();

        return [
            'source' => self::SOURCE_DEVICE,
            'id' => $device->getId(),
            'latitude' => (string) $device->getLatitude(),
            'longitude' => (string) $device->getLongitude(),
            'label' => $device->getName() ?? ('Device #' . $device->getId()),
            'related' => [
                'name' => $device->getName(),
                'nameAddress' => $device->getNameAddress(),
                'forest' => $forest ? [
                    'id' => $forest->getId(),
                    'name' => $forest->getName(),
                ] : null,
            ],
        ];
    }

    private function markerForest(Forest $forest): array
    {
        return [
            'source' => self::SOURCE_FOREST,
            'id' => $forest->getId(),
            'latitude' => (string) $forest->getLatitude(),
            'longitude' => (string) $forest->getLongitude(),
            'label' => $forest->getName() ?? ('Forest #' . $forest->getId()),
            'related' => [
                'name' => $forest->getName(),
                'nameAddress' => $forest->getNameAddress(),
            ],
        ];
    }

    private function markerFire(Fire $fire): array
    {
        $device = $fire->getDevice();
        $forest = $fire->getForest();
        $lat = $device ? (string) $device->getLatitude() : '';
        $lng = $device ? (string) $device->getLongitude() : '';

        return [
            'source' => self::SOURCE_FIRE,
            'id' => $fire->getId(),
            'latitude' => $lat,
            'longitude' => $lng,
            'label' => sprintf('Fire #%d (%s)', $fire->getId(), $fire->getStatus() ?? ''),
            'related' => [
                'status' => $fire->getStatus(),
                'device' => $device ? [
                    'id' => $device->getId(),
                    'name' => $device->getName(),
                ] : null,
                'forest' => $forest ? [
                    'id' => $forest->getId(),
                    'name' => $forest->getName(),
                ] : null,
            ],
        ];
    }

    private function markerFireBrigade(FireBrigade $brigade): array
    {
        $center = $brigade->getCenter();
        $lat = $center ? (string) $center->getLatitude() : '';
        $lng = $center ? (string) $center->getLongitude() : '';

        return [
            'source' => self::SOURCE_FIRE_BRIGADE,
            'id' => $brigade->getId(),
            'latitude' => $lat,
            'longitude' => $lng,
            'label' => $brigade->getName() ?? ('Fire brigade #' . $brigade->getId()),
            'related' => [
                'name' => $brigade->getName(),
                'email' => $brigade->getEmail(),
                'center' => $center ? [
                    'id' => $center->getId(),
                    'name' => $center->getName(),
                ] : null,
            ],
        ];
    }

    private function markerMobileSensor(MobileSensor $sensor): array
    {
        $center = $sensor->getCenter();

        return [
            'source' => self::SOURCE_MOBILE_SENSOR,
            'id' => $sensor->getId(),
            'latitude' => (string) $sensor->getLatitude(),
            'longitude' => (string) $sensor->getLongitude(),
            'label' => $sensor->getName() ?? ('Sensor #' . $sensor->getId()),
            'related' => [
                'name' => $sensor->getName(),
                'type' => $sensor->getType(),
                'status' => $sensor->getStatus(),
                'center' => $center ? [
                    'id' => $center->getId(),
                    'name' => $center->getName(),
                ] : null,
            ],
        ];
    }

    private function markerCar(Car $car): array
    {
        $center = $car->getCenter();
        $lat = $center ? (string) $center->getLatitude() : '';
        $lng = $center ? (string) $center->getLongitude() : '';

        return [
            'source' => self::SOURCE_CAR,
            'id' => $car->getId(),
            'latitude' => $lat,
            'longitude' => $lng,
            'label' => ($car->getName() ?? 'Car') . ' (' . $car->getNumberPlate() . ')',
            'related' => [
                'name' => $car->getName(),
                'numberPlate' => $car->getNumberPlate(),
                'model' => $car->getModel(),
                'center' => $center ? [
                    'id' => $center->getId(),
                    'name' => $center->getName(),
                ] : null,
            ],
        ];
    }

    private function markerAddress(Address $address): array
    {
        return [
            'source' => self::SOURCE_ADDRESS,
            'id' => $address->getId(),
            'latitude' => (string) $address->getLatitude(),
            'longitude' => (string) $address->getLongitude(),
            'label' => 'Address #' . $address->getId(),
            'related' => [],
        ];
    }
}
