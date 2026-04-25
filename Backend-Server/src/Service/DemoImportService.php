<?php

namespace App\Service;

use App\Entity\Address;
use App\Entity\Car;
use App\Entity\Center;
use App\Entity\Device;
use App\Entity\DeviceValue;
use App\Entity\EmergencyRequest;
use App\Entity\Fire;
use App\Entity\FireBrigade;
use App\Entity\Forest;
use App\Entity\MobileSensor;
use App\Entity\RolesGroup;
use App\Entity\TaskFireBrigade;
use App\Entity\User;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class DemoImportService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    /**
     * @return array{success: bool, inserted: array<string, int>, message?: string}
     */
    public function importFromXml(string $xmlContent): array
    {
        $previous = libxml_use_internal_errors(true);
        $root = simplexml_load_string($xmlContent, \SimpleXMLElement::class, LIBXML_NOCDATA);
        libxml_use_internal_errors($previous);

        if ($root === false) {
            $errors = array_map(fn ($e) => trim($e->message), libxml_get_errors());
            libxml_clear_errors();

            return [
                'success' => false,
                'inserted' => [],
                'message' => 'Invalid XML: '.implode('; ', array_slice($errors, 0, 5)),
            ];
        }

        if ($root->getName() !== 'fireguard-demo-data') {
            return [
                'success' => false,
                'inserted' => [],
                'message' => 'Root element must be fireguard-demo-data',
            ];
        }

        $inserted = [
            'roles_group' => 0,
            'user' => 0,
            'center' => 0,
            'forest' => 0,
            'device' => 0,
            'fire' => 0,
            'fire_brigade' => 0,
            'car' => 0,
            'mobile_sensor' => 0,
            'device_value' => 0,
            'task_fire_brigade' => 0,
            'emergency_request' => 0,
            'address' => 0,
        ];

        /** @var array<string, int> $refs */
        $refs = [];

        $conn = $this->em->getConnection();
        $conn->beginTransaction();

        try {
            $this->importRolesGroups($root, $refs, $inserted);
            $this->em->flush();

            $this->importUsers($root, $refs, $inserted);
            $this->em->flush();

            $this->importCenters($root, $refs, $inserted);
            $this->em->flush();

            $this->importForests($root, $refs, $inserted);
            $this->em->flush();

            $this->importDevices($root, $refs, $inserted);
            $this->em->flush();

            $this->importFires($root, $refs, $inserted);
            $this->em->flush();

            $this->importFireBrigades($root, $refs, $inserted);
            $this->em->flush();

            $this->importCars($root, $refs, $inserted);
            $this->em->flush();

            $this->importMobileSensors($root, $refs, $inserted);
            $this->em->flush();

            $this->importDeviceValues($root, $refs, $inserted);
            $this->em->flush();

            $this->importTaskFireBrigades($root, $refs, $inserted);
            $this->em->flush();

            $this->importEmergencyRequests($root, $refs, $inserted);
            $this->em->flush();

            $this->importAddresses($root, $refs, $inserted);
            $this->em->flush();

            $conn->commit();

            return [
                'success' => true,
                'inserted' => $inserted,
                'refs' => $refs,
            ];
        } catch (\Throwable $e) {
            $conn->rollBack();
            $this->em->clear();

            $msg = $e->getMessage();
            if ($e instanceof UniqueConstraintViolationException) {
                $msg = 'Duplicate key (append-only import: change demo emails/plates/names or use a clean DB). '.$msg;
            }

            return [
                'success' => false,
                'inserted' => [],
                'message' => $msg,
            ];
        }
    }

    /**
     * @param array<string, int> $refs
     * @param array<string, int> $inserted
     */
    private function importRolesGroups(\SimpleXMLElement $root, array &$refs, array &$inserted): void
    {
        if (!isset($root->roles_groups)) {
            return;
        }

        /** @var array<string, RolesGroup> $pending */
        $pending = [];

        foreach ($root->roles_groups->roles_group as $node) {
            $ref = (string) $node['ref'];
            if ($ref === '') {
                throw new \InvalidArgumentException('roles_group missing ref');
            }

            $existingId = isset($node['existing_identifier']) ? trim((string) $node['existing_identifier']) : '';
            if ($existingId !== '') {
                $rg = $this->em->getRepository(RolesGroup::class)->findOneBy(['identifier' => $existingId]);
                if ($rg === null) {
                    throw new \InvalidArgumentException('Roles group with identifier "'.$existingId.'" not found');
                }
                $refs[$ref] = (int) $rg->getId();

                continue;
            }

            $name = trim((string) $node['name']);
            $identifier = trim((string) $node['identifier']);
            $standard = filter_var((string) ($node['standard'] ?? '0'), FILTER_VALIDATE_BOOLEAN);

            $roles = [];
            foreach ($node->role as $roleNode) {
                $r = trim((string) $roleNode);
                if ($r !== '') {
                    $roles[] = $r;
                }
            }

            $entity = new RolesGroup();
            $entity->setName($name);
            $entity->setIdentifier($identifier);
            $entity->setStandard($standard);
            $entity->setRoles($roles);

            $this->em->persist($entity);
            $pending[$ref] = $entity;
        }

        if ($pending !== []) {
            $this->em->flush();
            foreach ($pending as $ref => $entity) {
                $refs[$ref] = (int) $entity->getId();
                $inserted['roles_group']++;
            }
        }
    }

    /**
     * @param array<string, int> $refs
     * @param array<string, int> $inserted
     */
    private function importUsers(\SimpleXMLElement $root, array &$refs, array &$inserted): void
    {
        if (!isset($root->users)) {
            return;
        }

        foreach ($root->users->user as $node) {
            $ref = (string) $node['ref'];
            $rgRef = (string) $node['rolesGroupRef'];
            if ($ref === '' || $rgRef === '') {
                throw new \InvalidArgumentException('user missing ref or rolesGroupRef');
            }
            if (!isset($refs[$rgRef])) {
                throw new \InvalidArgumentException('Unknown rolesGroupRef: '.$rgRef);
            }

            $user = new User();
            $user->setEmail(trim((string) $node['email']));
            $user->setFullName(trim((string) $node['fullName']));
            $user->setPhoneNumber(trim((string) $node['phoneNumber']));
            $user->setGender(trim((string) $node['gender']));
            $user->setActive(true);
            $user->setVerifiedAt(new \DateTime());

            $dob = trim((string) $node['dateOfBirth']);
            if ($dob !== '') {
                $user->setDateOfBirth(new \DateTime($dob));
            }

            $plain = (string) $node['password'];
            $user->setPassword($this->passwordHasher->hashPassword($user, $plain));

            $rg = $this->em->getReference(RolesGroup::class, $refs[$rgRef]);
            $user->addRolesGroup($rg);

            $this->em->persist($user);
            $this->em->flush();
            $refs[$ref] = (int) $user->getId();
            $inserted['user']++;
        }
    }

    /**
     * @param array<string, int> $refs
     * @param array<string, int> $inserted
     */
    private function importCenters(\SimpleXMLElement $root, array &$refs, array &$inserted): void
    {
        if (!isset($root->centers)) {
            return;
        }

        foreach ($root->centers->center as $node) {
            $ref = (string) $node['ref'];
            $c = new Center();
            $c->setName(trim((string) $node['name']));
            $c->setDescription(trim((string) $node['description']) ?: null);
            $c->setPhoneNumber(trim((string) $node['phoneNumber']));
            $c->setStatus(trim((string) $node['status']));
            $c->setLongitude(trim((string) $node['longitude']));
            $c->setLatitude(trim((string) $node['latitude']));
            $c->setNameAddress(trim((string) $node['nameAddress']) ?: null);

            $this->em->persist($c);
            $this->em->flush();
            $refs[$ref] = (int) $c->getId();
            $inserted['center']++;
        }
    }

    /**
     * @param array<string, int> $refs
     * @param array<string, int> $inserted
     */
    private function importForests(\SimpleXMLElement $root, array &$refs, array &$inserted): void
    {
        if (!isset($root->forests)) {
            return;
        }

        foreach ($root->forests->forest as $node) {
            $ref = (string) $node['ref'];
            $f = new Forest();
            $f->setName(trim((string) $node['name']));
            $f->setDescription(trim((string) $node['description']) ?: null);
            $f->setLongitude(trim((string) $node['longitude']));
            $f->setLatitude(trim((string) $node['latitude']));
            $f->setNameAddress(trim((string) $node['nameAddress']) ?: null);

            $this->em->persist($f);
            $this->em->flush();
            $refs[$ref] = (int) $f->getId();
            $inserted['forest']++;
        }
    }

    /**
     * @param array<string, int> $refs
     * @param array<string, int> $inserted
     */
    private function importDevices(\SimpleXMLElement $root, array &$refs, array &$inserted): void
    {
        if (!isset($root->devices)) {
            return;
        }

        foreach ($root->devices->device as $node) {
            $ref = (string) $node['ref'];
            $fr = (string) $node['forestRef'];
            if (!isset($refs[$fr])) {
                throw new \InvalidArgumentException('Unknown forestRef: '.$fr);
            }

            $d = new Device();
            $d->setName(trim((string) $node['name']));
            $d->setDescription(trim((string) $node['description']) ?: null);
            $d->setLongitude(trim((string) $node['longitude']));
            $d->setLatitude(trim((string) $node['latitude']));
            $d->setNameAddress(trim((string) $node['nameAddress']) ?: null);
            $d->setForest($this->em->getReference(Forest::class, $refs[$fr]));

            $this->em->persist($d);
            $this->em->flush();
            $refs[$ref] = (int) $d->getId();
            $inserted['device']++;
        }
    }

    /**
     * @param array<string, int> $refs
     * @param array<string, int> $inserted
     */
    private function importFires(\SimpleXMLElement $root, array &$refs, array &$inserted): void
    {
        if (!isset($root->fires)) {
            return;
        }

        foreach ($root->fires->fire as $node) {
            $ref = (string) $node['ref'];
            $forestRef = (string) $node['forestRef'];
            $deviceRef = (string) $node['deviceRef'];
            if (!isset($refs[$forestRef], $refs[$deviceRef])) {
                throw new \InvalidArgumentException('Unknown fire ref(s)');
            }

            $fire = new Fire();
            $fire->setStatus(trim((string) $node['status']));
            $fire->setForest($this->em->getReference(Forest::class, $refs[$forestRef]));
            $fire->setDevice($this->em->getReference(Device::class, $refs[$deviceRef]));

            $this->em->persist($fire);
            $this->em->flush();
            $refs[$ref] = (int) $fire->getId();
            $inserted['fire']++;
        }
    }

    /**
     * @param array<string, int> $refs
     * @param array<string, int> $inserted
     */
    private function importFireBrigades(\SimpleXMLElement $root, array &$refs, array &$inserted): void
    {
        if (!isset($root->fire_brigades)) {
            return;
        }

        foreach ($root->fire_brigades->fire_brigade as $node) {
            $ref = (string) $node['ref'];
            $cr = (string) $node['centerRef'];
            if (!isset($refs[$cr])) {
                throw new \InvalidArgumentException('Unknown centerRef: '.$cr);
            }

            $fb = new FireBrigade();
            $fb->setName(trim((string) $node['name']));
            $fb->setNumberOfTeam((int) $node['numberOfTeam']);
            $fb->setEmail(trim((string) $node['email']));
            $fb->setCenter($this->em->getReference(Center::class, $refs[$cr]));

            $plain = (string) $node['password'];
            $fb->setPassword($this->passwordHasher->hashPassword($fb, $plain));

            $this->em->persist($fb);
            $this->em->flush();
            $refs[$ref] = (int) $fb->getId();
            $inserted['fire_brigade']++;
        }
    }

    /**
     * @param array<string, int> $refs
     * @param array<string, int> $inserted
     */
    private function importCars(\SimpleXMLElement $root, array &$refs, array &$inserted): void
    {
        if (!isset($root->cars)) {
            return;
        }

        foreach ($root->cars->car as $node) {
            $ref = (string) $node['ref'];
            $cr = (string) $node['centerRef'];
            if (!isset($refs[$cr])) {
                throw new \InvalidArgumentException('Unknown centerRef: '.$cr);
            }

            $car = new Car();
            $car->setName(trim((string) $node['name']));
            $car->setModel(trim((string) $node['model']) ?: null);
            $car->setNumberPlate(trim((string) $node['numberPlate']));
            $car->setCenter($this->em->getReference(Center::class, $refs[$cr]));

            $this->em->persist($car);
            $this->em->flush();
            $refs[$ref] = (int) $car->getId();
            $inserted['car']++;
        }
    }

    /**
     * @param array<string, int> $refs
     * @param array<string, int> $inserted
     */
    private function importMobileSensors(\SimpleXMLElement $root, array &$refs, array &$inserted): void
    {
        if (!isset($root->mobile_sensors)) {
            return;
        }

        foreach ($root->mobile_sensors->mobile_sensor as $node) {
            $ref = (string) $node['ref'];
            $cr = (string) $node['centerRef'];
            if (!isset($refs[$cr])) {
                throw new \InvalidArgumentException('Unknown centerRef: '.$cr);
            }

            $ms = new MobileSensor();
            $ms->setName(trim((string) $node['name']));
            $ms->setType(trim((string) $node['type']));
            $ms->setStatus(trim((string) $node['status']));
            $ms->setLongitude(trim((string) $node['longitude']));
            $ms->setLatitude(trim((string) $node['latitude']));
            $ms->setLastSeenAt(new \DateTimeImmutable(trim((string) $node['lastSeenAt'])));
            $ms->setCenter($this->em->getReference(Center::class, $refs[$cr]));

            $this->em->persist($ms);
            $this->em->flush();
            $refs[$ref] = (int) $ms->getId();
            $inserted['mobile_sensor']++;
        }
    }

    /**
     * @param array<string, int> $refs
     * @param array<string, int> $inserted
     */
    private function importDeviceValues(\SimpleXMLElement $root, array &$refs, array &$inserted): void
    {
        if (!isset($root->device_values)) {
            return;
        }

        foreach ($root->device_values->device_value as $node) {
            $ref = (string) $node['ref'];
            $dr = (string) $node['deviceRef'];
            if (!isset($refs[$dr])) {
                throw new \InvalidArgumentException('Unknown deviceRef: '.$dr);
            }

            $dv = new DeviceValue();
            $dv->setValueHeat((float) $node['valueHeat']);
            $dv->setValueMoisture((float) $node['valueMoisture']);
            $dv->setValueGas((float) $node['valueGas']);
            $dv->setDate(new \DateTimeImmutable(trim((string) $node['date'])));
            $dv->setStatus(trim((string) $node['status']));
            $dv->setDevice($this->em->getReference(Device::class, $refs[$dr]));

            $this->em->persist($dv);
            $this->em->flush();
            $refs[$ref] = (int) $dv->getId();
            $inserted['device_value']++;
        }
    }

    /**
     * @param array<string, int> $refs
     * @param array<string, int> $inserted
     */
    private function importTaskFireBrigades(\SimpleXMLElement $root, array &$refs, array &$inserted): void
    {
        if (!isset($root->task_fire_brigades)) {
            return;
        }

        foreach ($root->task_fire_brigades->task_fire_brigade as $node) {
            $ref = (string) $node['ref'];
            $fbr = (string) $node['fireBrigadeRef'];
            $fir = (string) $node['fireRef'];
            if (!isset($refs[$fbr], $refs[$fir])) {
                throw new \InvalidArgumentException('Unknown task fire brigade ref(s)');
            }

            $t = new TaskFireBrigade();
            $t->setNote(trim((string) $node['note']) ?: null);
            $t->setStatus(trim((string) $node['status']));
            $t->setFireBrigade($this->em->getReference(FireBrigade::class, $refs[$fbr]));
            $t->setFire($this->em->getReference(Fire::class, $refs[$fir]));

            $this->em->persist($t);
            $this->em->flush();
            $refs[$ref] = (int) $t->getId();
            $inserted['task_fire_brigade']++;
        }
    }

    /**
     * @param array<string, int> $refs
     * @param array<string, int> $inserted
     */
    private function importEmergencyRequests(\SimpleXMLElement $root, array &$refs, array &$inserted): void
    {
        if (!isset($root->emergency_requests)) {
            return;
        }

        foreach ($root->emergency_requests->emergency_request as $node) {
            $ref = (string) $node['ref'];
            $cc = (string) $node['centerRef'];
            $fir = (string) $node['fireRef'];
            $fbr = (string) $node['fireBrigadeRef'];
            if (!isset($refs[$cc], $refs[$fir], $refs[$fbr])) {
                throw new \InvalidArgumentException('Unknown emergency_request ref(s)');
            }

            $e = new EmergencyRequest();
            $e->setStatus(trim((string) $node['status']));
            $e->setCenter($this->em->getReference(Center::class, $refs[$cc]));
            $e->setFire($this->em->getReference(Fire::class, $refs[$fir]));
            $e->setFireBrigade($this->em->getReference(FireBrigade::class, $refs[$fbr]));

            $this->em->persist($e);
            $this->em->flush();
            $refs[$ref] = (int) $e->getId();
            $inserted['emergency_request']++;
        }
    }

    /**
     * @param array<string, int> $refs
     * @param array<string, int> $inserted
     */
    private function importAddresses(\SimpleXMLElement $root, array &$refs, array &$inserted): void
    {
        if (!isset($root->addresses)) {
            return;
        }

        foreach ($root->addresses->address as $node) {
            $ref = (string) $node['ref'];
            $a = new Address();
            $a->setLongitude(trim((string) $node['longitude']));
            $a->setLatitude(trim((string) $node['latitude']));

            $this->em->persist($a);
            $this->em->flush();
            $refs[$ref] = (int) $a->getId();
            $inserted['address']++;
        }
    }
}
