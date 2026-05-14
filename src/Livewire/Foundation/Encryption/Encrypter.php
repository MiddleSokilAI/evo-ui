<?php

namespace EvoUI\Livewire\Foundation\Encryption;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Contracts\Encryption\Encrypter as EncrypterContract;
use Illuminate\Contracts\Encryption\EncryptException;

class Encrypter implements EncrypterContract
{
    protected string $key;

    public function __construct()
    {
        $seed = (string) config('app.key', '');

        if ($seed === '') {
            $seed = defined('EVO_CORE_PATH') ? EVO_CORE_PATH : __DIR__;
        }

        $this->key = hash('sha256', $seed);
    }

    public function encrypt(#[\SensitiveParameter] $value, $serialize = true): string
    {
        $payload = base64_encode($serialize ? serialize($value) : (string) $value);
        $mac = hash_hmac('sha256', $payload, $this->key);

        return base64_encode(json_encode(['payload' => $payload, 'mac' => $mac], JSON_THROW_ON_ERROR));
    }

    public function decrypt($payload, $unserialize = true)
    {
        try {
            $data = json_decode(base64_decode((string) $payload, true) ?: '', true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $exception) {
            throw new DecryptException('Invalid encrypted payload.', 0, $exception);
        }

        if (!is_array($data) || !isset($data['payload'], $data['mac'])) {
            throw new DecryptException('Invalid encrypted payload.');
        }

        $mac = hash_hmac('sha256', (string) $data['payload'], $this->key);

        if (!hash_equals($mac, (string) $data['mac'])) {
            throw new DecryptException('Invalid encrypted payload signature.');
        }

        $decoded = base64_decode((string) $data['payload'], true);

        if ($decoded === false) {
            throw new DecryptException('Invalid encrypted payload body.');
        }

        return $unserialize ? unserialize($decoded) : $decoded;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    /** @return list<string> */
    public function getAllKeys(): array
    {
        return [$this->key];
    }

    /** @return list<string> */
    public function getPreviousKeys(): array
    {
        return [];
    }
}
