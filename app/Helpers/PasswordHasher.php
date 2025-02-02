<?php
namespace Amin\Event\Helpers;
class PasswordHasher
{
    /**
     * Generate a hashed password.
     *
     * @param string $password The plain password to hash
     * @param int $cost The cost factor (default: 10)
     * @return string The hashed password
     */
    public static function hash(string $password, int $cost = 10): string
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => $cost]);
    }
    public static function verify(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
}
