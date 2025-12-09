<?php

namespace App\Security;

use Lexik\Bundle\JWTAuthenticationBundle\Security\User\PayloadAwareUserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class JwtUserProvider implements PayloadAwareUserProviderInterface
{
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        // Не используется напрямую, но нужен интерфейс
        return new JwtUser(0, $identifier, ['ROLE_USER']);
    }

    public function loadUserByIdentifierAndPayload(string $identifier, array $payload): UserInterface
    {
        // Берём данные прямо из payload токена, выданного auth-service
        $id = $payload['id'] ?? 0;
        $roles = $payload['roles'] ?? ['ROLE_USER'];

        return new JwtUser($id, $identifier, $roles);
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        return $user;
    }

    public function supportsClass(string $class): bool
    {
        return $class === JwtUser::class;
    }
}
