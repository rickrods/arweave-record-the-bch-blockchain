<?php

namespace Arweave\SDK\Support;

use Exception;
use phpseclib3\Crypt\RSA;
use phpseclib3\Crypt\PublicKeyLoader;

class Wallet
{
    const HASH = 'sha256';

    /**
     * @var \phpseclib3\Crypt\RSA\PrivateKey
     */
    private $private;

    protected $owner;
    protected $address;

    public function __construct(array $jwk = [])
    {
        if (!$jwk) {
            throw new Exception('No key file specified');
        }

        $this->private = $this->RSAPrivateFromJWK($jwk);
        $this->owner = $jwk['n'];
        $this->address = static::ownerToAddress($this->owner);
    }

    private static function ownerToAddress(string $owner): string
    {
        return Helpers::base64urlEncode(base64_encode(hash('sha256', base64_decode(Helpers::base64urlDecode($owner)), true)));
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function getOwner(): string
    {
        return $this->owner;
    }

    public function sign($message): string
    {
        return $this->private->sign($message);
    }

    private function RSAPrivateFromJWK(array $jwk): RSA\PrivateKey
    {
        /** @var RSA\PrivateKey $rsa */
        $rsa = PublicKeyLoader::loadFormat('JWK', json_encode($jwk));

        if (!$rsa instanceof RSA\PrivateKey) {
            throw new Exception('Failed to read private RSA JWK');
        }

        return $rsa->withPadding(RSA::SIGNATURE_PSS)
                   ->withHash('sha256')
                   ->withMGFHash('sha256')
                   ->withSaltLength(0);
    }
}