<?php

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\Security\Core\User\UserInterface;

class JWTCreatedListener
{
    public function onJWTCreated(JWTCreatedEvent $event): void
    {
        $user = $event->getUser();

        if ($user instanceof UserInterface && method_exists($user, 'getId')) {
            $data = $event->getData();
            $data['id'] = $user->getId(); // добавляем id в payload
            $event->setData($data);
        }
    }
}
