<?php

namespace App\Listener;

use App\Constant\UserGender;
use App\Constant\EmergencyRequestStatus;
use App\Constant\FireStatusType;
use App\Entity\User;
use App\Entity\Fire;
use App\Entity\EmergencyRequest;
use App\Service\UploadFileService;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Symfony\Component\Translation\LocaleSwitcher;
use Symfony\Contracts\Translation\TranslatorInterface;

class
EntityListener
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly LocaleSwitcher $localeSwitcher,
        private readonly UploadFileService $uploadFileService,

    ) {}

    public function postLoad(PostLoadEventArgs $args): void
    {
        $entity = $args->getObject();

        $locale = $this->localeSwitcher->getLocale();

        if ($entity instanceof User) {
            $entity->setGenderValue(UserGender::getByValue($entity->getGender())?->trans($this->translator, $locale));
        } elseif ($entity instanceof Fire) {
            $entity->setStatusValue(FireStatusType::getByValue($entity->getStatus())?->trans($this->translator, $locale));
        } elseif ($entity instanceof EmergencyRequest) {
            $entity->setStatusValue(EmergencyRequestStatus::getByValue($entity->getStatus())?->trans($this->translator, $locale));
        }
    }

    public function postPersist(PostPersistEventArgs $args): void
    {
        $entity = $args->getObject();

        $locale = $this->localeSwitcher->getLocale();

        if ($entity instanceof User) {
            $entity->setGenderValue(UserGender::getByValue($entity->getGender())?->trans($this->translator, $locale));
        } elseif ($entity instanceof Fire) {
            $entity->setStatusValue(FireStatusType::getByValue($entity->getStatus())?->trans($this->translator, $locale));
        } elseif ($entity instanceof EmergencyRequest) {
            $entity->setStatusValue(EmergencyRequestStatus::getByValue($entity->getStatus())?->trans($this->translator, $locale));
        }

    }

    public function postUpdate(PostUpdateEventArgs $args): void
    {
        $entity = $args->getObject();

        $locale = $this->localeSwitcher->getLocale();

        if ($entity instanceof User) {
            $entity->setGenderValue(UserGender::getByValue($entity->getGender())?->trans($this->translator, $locale));
        } elseif ($entity instanceof Fire) {
            $entity->setStatusValue(FireStatusType::getByValue($entity->getStatus())?->trans($this->translator, $locale));
        } elseif ($entity instanceof EmergencyRequest) {
            $entity->setStatusValue(EmergencyRequestStatus::getByValue($entity->getStatus())?->trans($this->translator, $locale));
        }
    }

    public function postRemove(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof User) {
            $this->uploadFileService->removeFile($entity->getAvatarFileUrl());
        } elseif ($entity instanceof Fire) {
            $entity->setStatusValue(FireStatusType::getByValue($entity->getStatus())?->trans($this->translator, $locale));
        } elseif ($entity instanceof EmergencyRequest) {
            $entity->setStatusValue(EmergencyRequestStatus::getByValue($entity->getStatus())?->trans($this->translator, $locale));
        }
    }
}
