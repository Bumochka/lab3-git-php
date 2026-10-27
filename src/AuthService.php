<?php

namespace App;

use PDO;
use RuntimeException;

class AuthService
{
    public const BG_COLOR_COOKIE = 'bg_color';
    public const FONT_COLOR_COOKIE = 'font_color';
    public const REMEMBER_COOKIE = 'remember_token';
    public const COOKIE_LIFETIME = 2592000; // 30 дней

    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function findUserByLogin(string $login): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE LOWER(login) = LOWER(:login)');
        $stmt->execute(['login' => $login]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function findUserById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function register(string $login, string $password, string $bgColor, string $fontColor): array
    {
        if ($this->findUserByLogin($login) !== null) {
            throw new RuntimeException('Пользователь с таким логином уже существует.');
        }

        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare('INSERT INTO users (login, password_hash, created_at) VALUES (:login, :pwd, :created)');
            $stmt->execute([
                'login' => $login,
                'pwd' => password_hash($password, PASSWORD_DEFAULT),
                'created' => time(),
            ]);
            $userId = (int)$this->db->lastInsertId();

            $stmt = $this->db->prepare('INSERT INTO user_settings (user_id, bg_color, font_color) VALUES (:uid, :bg, :font)');
            $stmt->execute([
                'uid' => $userId,
                'bg' => $bgColor,
                'font' => $fontColor,
            ]);

            $this->db->commit();
            return $this->findUserById($userId);
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function authenticate(string $login, string $password): ?array
    {
        $user = $this->findUserByLogin($login);
        if (!$user || !password_verify($password, $user['password_hash'])) {
            return null;
        }
        return $user;
    }

    public function settingsFor(int $userId): array
    {
        $stmt = $this->db->prepare('SELECT bg_color, font_color FROM user_settings WHERE user_id = :uid');
        $stmt->execute(['uid' => $userId]);
        $res = $stmt->fetch();
        return $res ?: ['bg_color' => '#ffffff', 'font_color' => '#000000'];
    }

    public function updateSettings(int $userId, string $bgColor, string $fontColor): void
    {
        $stmt = $this->db->prepare('REPLACE INTO user_settings (user_id, bg_color, font_color) VALUES (:uid, :bg, :font)');
        $stmt->execute([
            'uid' => $userId,
            'bg' => $bgColor,
            'font' => $fontColor,
        ]);
    }

    public function sendSettingsCookies(array $user): void
    {
        $settings = $this->settingsFor((int)$user['id']);
        $expires = time() + self::COOKIE_LIFETIME;

        setrawcookie(self::BG_COLOR_COOKIE, $settings['bg_color'], [
            'expires' => $expires,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        setrawcookie(self::FONT_COLOR_COOKIE, $settings['font_color'], [
            'expires' => $expires,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        $_COOKIE[self::BG_COLOR_COOKIE] = $settings['bg_color'];
        $_COOKIE[self::FONT_COLOR_COOKIE] = $settings['font_color'];
    }

    public function issueRememberToken(int $userId): void
    {
        $token = bin2hex(random_bytes(32));
        $expires = time() + self::COOKIE_LIFETIME;

        $stmt = $this->db->prepare('INSERT INTO remember_tokens (token_hash, user_id, expires_at) VALUES (:hash, :uid, :exp)');
        $stmt->execute([
            'hash' => hash('sha256', $token),
            'uid' => $userId,
            'exp' => $expires,
        ]);

        setrawcookie(self::REMEMBER_COOKIE, $token, [
            'expires' => $expires,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        $_COOKIE[self::REMEMBER_COOKIE] = $token;
    }

    public function loginByRememberToken(string $token): ?array
    {
        $stmt = $this->db->prepare('
            SELECT u.* FROM remember_tokens t
            JOIN users u ON u.id = t.user_id
            WHERE t.token_hash = :hash AND t.expires_at > :now
        ');
        $stmt->execute([
            'hash' => hash('sha256', $token),
            'now' => time(),
        ]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function revokeRememberToken(string $token): void
    {
        $stmt = $this->db->prepare('DELETE FROM remember_tokens WHERE token_hash = :hash');
        $stmt->execute(['hash' => hash('sha256', $token)]);
    }

    public function clearAuthCookies(): void
    {
        $opts = ['expires' => time() - 3600, 'path' => '/'];
        setcookie(self::REMEMBER_COOKIE, '', $opts);
        setcookie(self::BG_COLOR_COOKIE, '', $opts);
        setcookie(self::FONT_COLOR_COOKIE, '', $opts);
        unset($_COOKIE[self::REMEMBER_COOKIE], $_COOKIE[self::BG_COLOR_COOKIE], $_COOKIE[self::FONT_COLOR_COOKIE]);
    }
}