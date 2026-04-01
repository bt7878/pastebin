<?php

declare(strict_types=1);

namespace App;

use Random\RandomException;
use RuntimeException;

class PasteService
{
    private string $encKey;
    private string $macKey;
    private string $cipher = 'aes-256-cbc';
    private string $macAlgo = 'sha256';

    public function __construct(private readonly PasteRepository $pasteRepository)
    {
        $encKeyB64 = $_ENV['ENC_KEY'] ?? null;
        $macKeyB64 = $_ENV['MAC_KEY'] ?? null;

        if ($encKeyB64 === null || $macKeyB64 === null) {
            throw new RuntimeException('ENC_KEY and MAC_KEY must be set');
        }

        $this->encKey = base64_decode($encKeyB64, true);
        $this->macKey = base64_decode($macKeyB64, true);

        if ($this->encKey === false || $this->macKey === false) {
            throw new RuntimeException('ENC_KEY and MAC_KEY must be valid base64');
        }
        if (strlen($this->encKey) !== openssl_cipher_key_length($this->cipher)
            || strlen($this->macKey) !== openssl_cipher_key_length($this->cipher)) {
            throw new RuntimeException('ENC_KEY and MAC_KEY must be 32 bytes long');
        }
    }

    public function find(string $token): ?string
    {
        $decoded = base64_decode($token, true);
        if ($decoded === false) {
            return null;
        }

        $hmacLen = $this->hmacOutputLength();
        $ivLen = openssl_cipher_iv_length($this->cipher);

        if (strlen($decoded) < ($hmacLen + $ivLen)) {
            return null;
        }

        $hmac = substr($decoded, 0, $hmacLen);
        $payload = substr($decoded, $hmacLen);
        $iv = substr($payload, 0, $ivLen);
        $cipherText = substr($payload, $ivLen);

        $expectedHmac = hash_hmac($this->macAlgo, $payload, $this->macKey, true);
        if (!hash_equals($hmac, $expectedHmac)) {
            return null;
        }

        $decrypted = openssl_decrypt(
            $cipherText,
            $this->cipher,
            $this->encKey,
            OPENSSL_RAW_DATA,
            $iv
        );
        if ($decrypted === false) {
            throw new RuntimeException('Token decryption failed: ' . openssl_error_string());
        }

        $data = json_decode($decrypted, true);
        if ($data === null) {
            throw new RuntimeException('Failed to decode JSON: ' . json_last_error_msg());
        }

        $payloadB64 = $this->pasteRepository->find($data['id']);
        if ($payloadB64 === null) {
            return null;
        }

        $payload = base64_decode($payloadB64, true);
        $iv = substr($payload, 0, $ivLen);
        $cipherText = substr($payload, $ivLen);

        $text = openssl_decrypt(
            $cipherText,
            $this->cipher,
            base64_decode($data['key'], true),
            OPENSSL_RAW_DATA,
            $iv
        );
        if ($text === false) {
            throw new RuntimeException('Text decryption failed: ' . openssl_error_string());
        }

        return $text;
    }

    private function hmacOutputLength(): int
    {
        return strlen(hash($this->macAlgo, '', true));
    }

    /**
     * @throws RandomException
     */
    public function store(string $text): string
    {
        $ivLen = openssl_cipher_iv_length($this->cipher);

        $userKey = random_bytes(openssl_cipher_key_length($this->cipher));
        $iv = random_bytes($ivLen);
        $cipherText = openssl_encrypt(
            $text,
            $this->cipher,
            $userKey,
            OPENSSL_RAW_DATA,
            $iv
        );

        $id = $this->pasteRepository->store(base64_encode($iv . $cipherText));

        $to_encrypt = json_encode([
            'id' => $id,
            'key' => base64_encode($userKey),
        ]);
        if ($to_encrypt === false) {
            throw new RuntimeException('Failed to encode JSON: ' . json_last_error_msg());
        }

        $iv = random_bytes($ivLen);
        $cipherText = openssl_encrypt(
            $to_encrypt,
            $this->cipher,
            $this->encKey,
            OPENSSL_RAW_DATA,
            $iv
        );

        $payload = $iv . $cipherText;
        $hmac = hash_hmac($this->macAlgo, $payload, $this->macKey, true);

        return base64_encode($hmac . $payload);
    }
}
