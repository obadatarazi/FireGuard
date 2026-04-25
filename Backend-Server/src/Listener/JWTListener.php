<?php

namespace App\Listener;

use App\Entity\FireBrigade;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTDecodedEvent;
use Symfony\Component\Security\Core\User\UserInterface;

class JWTListener
{

    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param JWTDecodedEvent $event
     *
     * @return void
     */
    public function onJWTDecodedFireBrigade(JWTDecodedEvent $event): void
    {
        $payload = $event->getPayload();
        $this->em->getRepository(FireBrigade::class)->findOneByEmail($payload['email']);
    }

    /**
     * @param JWTDecodedEvent $event
     *
     * @return void
     */
    public function onJWTDecoded(JWTDecodedEvent $event)
    {
        $payload = $event->getPayload();
        /** @var User $user */
        $this->em->getRepository(User::class)->findOneByEmail($payload['email']);
    }

    public function onJWTCreated(JWTCreatedEvent $event)
    {
        /** @var User $user */
        $user = $event->getUser();
        if ($user instanceof UserInterface) {

            $payload = $event->getData();

            unset($payload['roles']);

            $event->setData($payload);

        }
    }

}
