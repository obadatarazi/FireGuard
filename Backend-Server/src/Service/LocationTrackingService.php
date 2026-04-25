<?php

namespace App\Service;

use App\Entity\Car;
use App\Entity\Center;
use App\Entity\LocationHistory;
use App\Entity\LocationLive;
use App\Entity\MobileSensor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LocationTrackingService
{
    public function __construct(
        private readonly EntityManagerInterface $em
    ) {
    }

    public function record(string $sourceType, int $sourceId, string $latitude, string $longitude, ?\DateTimeInterface $recordedAt = null): array
    {
        $resolved = $this->resolveSource($sourceType, $sourceId);
        /** @var Center $center */
        $center = $resolved['center'];
        $normalizedType = $resolved['sourceType'];
        $now = $recordedAt ?? new \DateTime();

        $live = $this->em->getRepository(LocationLive::class)->findOneBy([
            'sourceType' => $normalizedType,
            'sourceId' => $sourceId,
        ]);

        if (!$live) {
            $live = new LocationLive();
            $live
                ->setSourceType($normalizedType)
                ->setSourceId($sourceId)
                ->setCenter($center);
            $this->em->persist($live);
        }

        $live
            ->setLatitude($latitude)
            ->setLongitude($longitude)
            ->setRecordedAt($now);

        $history = new LocationHistory();
        $history
            ->setSourceType($normalizedType)
            ->setSourceId($sourceId)
            ->setCenter($center)
            ->setLatitude($latitude)
            ->setLongitude($longitude)
            ->setRecordedAt($now);

        $this->em->persist($history);
        $this->em->flush();

        return [
            'sourceType' => $normalizedType,
            'sourceId' => $sourceId,
            'centerId' => $center->getId(),
            'latitude' => $live->getLatitude(),
            'longitude' => $live->getLongitude(),
            'recordedAt' => $live->getRecordedAt()?->format(\DateTimeInterface::ATOM),
        ];
    }

    public function getLive(?string $sourceType = null, ?int $sourceId = null, ?int $centerId = null): array
    {
        $qb = $this->em->getRepository(LocationLive::class)->createQueryBuilder('ll')
            ->leftJoin('ll.center', 'c')
            ->addSelect('c')
            ->where('1 = 1');

        if ($sourceType) {
            $qb->andWhere('ll.sourceType = :sourceType')
                ->setParameter('sourceType', $this->normalizeSourceType($sourceType));
        }
        if ($sourceId) {
            $qb->andWhere('ll.sourceId = :sourceId')
                ->setParameter('sourceId', $sourceId);
        }
        if ($centerId) {
            $qb->andWhere('c.id = :centerId')
                ->setParameter('centerId', $centerId);
        }

        $qb->orderBy('ll.recordedAt', 'DESC');

        $items = [];
        foreach ($qb->getQuery()->getResult() as $row) {
            if (!$row instanceof LocationLive) {
                continue;
            }
            $items[] = [
                'sourceType' => $row->getSourceType(),
                'sourceId' => $row->getSourceId(),
                'centerId' => $row->getCenter()?->getId(),
                'latitude' => $row->getLatitude(),
                'longitude' => $row->getLongitude(),
                'recordedAt' => $row->getRecordedAt()?->format(\DateTimeInterface::ATOM),
            ];
        }

        return $items;
    }

    public function getHistory(array $filters): array
    {
        $sourceType = $filters['sourceType'] ?? null;
        $sourceId = $filters['sourceId'] ?? null;
        $centerId = $filters['centerId'] ?? null;
        $limit = $filters['limit'] ?? 1000;
        $day = $filters['day'] ?? null;
        $month = $filters['month'] ?? null;
        $year = $filters['year'] ?? null;
        $from = $filters['from'] ?? null;
        $to = $filters['to'] ?? null;

        $qb = $this->em->getRepository(LocationHistory::class)->createQueryBuilder('lh')
            ->leftJoin('lh.center', 'c')
            ->addSelect('c')
            ->where('1 = 1');

        if ($sourceType) {
            $qb->andWhere('lh.sourceType = :sourceType')
                ->setParameter('sourceType', $this->normalizeSourceType((string) $sourceType));
        }
        if ($sourceId) {
            $qb->andWhere('lh.sourceId = :sourceId')
                ->setParameter('sourceId', (int) $sourceId);
        }
        if ($centerId) {
            $qb->andWhere('c.id = :centerId')
                ->setParameter('centerId', (int) $centerId);
        }

        if ($day) {
            $start = new \DateTime((string) $day . ' 00:00:00');
            $end = new \DateTime((string) $day . ' 23:59:59');
            $qb->andWhere('lh.recordedAt BETWEEN :startDay AND :endDay')
                ->setParameter('startDay', $start)
                ->setParameter('endDay', $end);
        } elseif ($month) {
            $start = new \DateTime((string) $month . '-01 00:00:00');
            $end = clone $start;
            $end->modify('last day of this month')->setTime(23, 59, 59);
            $qb->andWhere('lh.recordedAt BETWEEN :startMonth AND :endMonth')
                ->setParameter('startMonth', $start)
                ->setParameter('endMonth', $end);
        } elseif ($year) {
            $start = new \DateTime((string) $year . '-01-01 00:00:00');
            $end = new \DateTime((string) $year . '-12-31 23:59:59');
            $qb->andWhere('lh.recordedAt BETWEEN :startYear AND :endYear')
                ->setParameter('startYear', $start)
                ->setParameter('endYear', $end);
        } else {
            if ($from) {
                $qb->andWhere('lh.recordedAt >= :fromDate')
                    ->setParameter('fromDate', new \DateTime((string) $from));
            }
            if ($to) {
                $qb->andWhere('lh.recordedAt <= :toDate')
                    ->setParameter('toDate', new \DateTime((string) $to));
            }
        }

        $qb->orderBy('lh.recordedAt', 'ASC')
            ->setMaxResults((int) $limit);

        $items = [];
        foreach ($qb->getQuery()->getResult() as $row) {
            if (!$row instanceof LocationHistory) {
                continue;
            }
            $items[] = [
                'sourceType' => $row->getSourceType(),
                'sourceId' => $row->getSourceId(),
                'centerId' => $row->getCenter()?->getId(),
                'latitude' => $row->getLatitude(),
                'longitude' => $row->getLongitude(),
                'recordedAt' => $row->getRecordedAt()?->format(\DateTimeInterface::ATOM),
            ];
        }

        return $items;
    }

    public function assertSourceBelongsToCenter(string $sourceType, int $sourceId, Center $center): void
    {
        $resolved = $this->resolveSource($sourceType, $sourceId);
        /** @var Center $sourceCenter */
        $sourceCenter = $resolved['center'];
        if ($sourceCenter->getId() !== $center->getId()) {
            throw new NotFoundHttpException('Source not found in your center.');
        }
    }

    private function resolveSource(string $sourceType, int $sourceId): array
    {
        $normalizedType = $this->normalizeSourceType($sourceType);
        if ($normalizedType === LocationLive::SOURCE_CAR) {
            $entity = $this->em->getRepository(Car::class)->find($sourceId);
        } else {
            $entity = $this->em->getRepository(MobileSensor::class)->find($sourceId);
        }

        if (!$entity) {
            throw new NotFoundHttpException('Source not found.');
        }

        $center = $entity->getCenter();
        if (!$center) {
            throw new BadRequestHttpException('Source has no center.');
        }

        return [
            'sourceType' => $normalizedType,
            'center' => $center,
        ];
    }

    private function normalizeSourceType(string $sourceType): string
    {
        $normalized = strtoupper(trim($sourceType));
        $allowed = [LocationLive::SOURCE_CAR, LocationLive::SOURCE_MOBILE_SENSOR];
        if (!in_array($normalized, $allowed, true)) {
            throw new BadRequestHttpException('Invalid sourceType. Use CAR or MOBILE_SENSOR.');
        }

        return $normalized;
    }
}
