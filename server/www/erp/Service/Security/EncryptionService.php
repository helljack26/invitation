<?php

namespace Service\Security;

class EncryptionService
{
    private string $cipher = "aes-256-cbc";
    private string $key;
    private string $iv;

    public function __construct(string $encryptionKey)
    {
        $this->key = hash('sha256', $encryptionKey, true);
        // Генерируем IV (инициализационный вектор) при шифровании
    }

    public function encrypt(string $plaintext): string
    {
        $ivLength = openssl_cipher_iv_length($this->cipher);
        $iv = openssl_random_pseudo_bytes($ivLength);
        $ciphertext = openssl_encrypt($plaintext, $this->cipher, $this->key, 0, $iv);
        // Сохраняем IV вместе с шифротекстом
        return base64_encode($iv . $ciphertext);
    }

    public function decrypt(string $ciphertextBase64): string
    {
        $ciphertext = base64_decode($ciphertextBase64);
        $ivLength = openssl_cipher_iv_length($this->cipher);
        $iv = substr($ciphertext, 0, $ivLength);
        $ciphertext = substr($ciphertext, $ivLength);
        return openssl_decrypt($ciphertext, $this->cipher, $this->key, 0, $iv);
    }
}
