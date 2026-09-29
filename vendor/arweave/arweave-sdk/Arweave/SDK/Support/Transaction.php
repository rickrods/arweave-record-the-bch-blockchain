<?php

namespace Arweave\SDK\Support;

use Exception;
use Arweave\SDK\Support\Wallet;
use Arweave\SDK\Support\Helpers;
use phpseclib3\Crypt\RSA;
use phpseclib3\Crypt\PublicKeyLoader;

class Transaction
{
    /**
     * Transaction attributes
     *
     * @var mixed[]
     */
    protected $attributes = [
        'id'        => '',
        'last_tx'   => '',
        'owner'     => '',
        'tags'      => [],
        'target'    => '',
        'quantity'  => '0',
        'data'      => '',
        'reward'    => '0',
        'signature' => '',
    ];

    /**
     * @param string[] $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->attributes = array_merge($this->attributes, $attributes);

        // All fields must be strings, so cast them all
        $this->attributes = array_map(function ($attribute) {
            return is_array($attribute) ? $attribute : (string) $attribute;
        }, $this->attributes);
    }

    /**
     * Get the transaction attributes as an array.
     *
     * @return string[]
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Get an attribute.
     *
     * @param  string $attribute Attribute key
     *
     * @return mixed
     */
    public function getAttribute(string $attribute)
    {
        return $this->attributes[$attribute] ?? null;
    }

    /**
     * Sign the transaction and return the signature.
     *
     * @param  \Arweave\SDK\Support\Wallet $wallet
     *
     * @return string
     *
     * concatenate_buffers([owner, target, id, data, quantity, reward, last]);
     *
     * owner, target, id
     */
    public function sign(Wallet $wallet): string
    {
        $message = $this->getSignatureData();

        $signature = $wallet->sign($message);


        $this->attributes['signature'] = Helpers::base64urlEncode(base64_encode($signature));

        $this->attributes['id'] = Helpers::base64urlEncode(base64_encode(hash('sha256', $signature, true)));

        if (!$this->verify()) {
            throw new Exception('Arweave Transaction - Error validating transaction signature.');
        }

        return $signature;
    }

    public function addTag(string $name, string $value){
        array_push($this->attributes['tags'], [
            'name' => base64_encode(Helpers::base64urlEncode($name)),
            'value' => base64_encode(Helpers::base64urlEncode($value))
        ]);
    }


    /**
     * Get the transaction message to sign.
     *
     * @return string
     */
    public function getSignatureData(): string
    {
        $tag_string = array_reduce($this->attributes['tags'], function($accumulator, $tag){
            return $accumulator .
                base64_decode(Helpers::base64urlDecode($tag['name'])) .
                base64_decode(Helpers::base64urlDecode($tag['value']));
        });

        return base64_decode(Helpers::base64urlDecode($this->attributes['owner'])) .
        base64_decode(Helpers::base64urlDecode($this->attributes['target'])) .
        base64_decode(Helpers::base64urlDecode($this->attributes['data'])) .
        $this->attributes['quantity'] .
        $this->attributes['reward'] .
        base64_decode(Helpers::base64urlDecode($this->attributes['last_tx'])) . 
        $tag_string;
    }

    public function verify(): bool
    {
        $jwk = [
            'kty' => 'RSA',
            'e'   => 'AQAB',
            'n'   => $this->attributes['owner']
        ];
        /** @var RSA\PublicKey $rsa */
        $rsa = PublicKeyLoader::loadFormat('JWK', json_encode($jwk));
        $rsa = $rsa->withPadding(RSA::SIGNATURE_PSS)
                   ->withHash('sha256')
                   ->withMGFHash('sha256')
                   ->withSaltLength(0);
        $message = $this->getSignatureData();
        $signature = base64_decode(Helpers::base64urlDecode($this->attributes['signature']));
        return $rsa->verify($message, $signature);
    }
}
