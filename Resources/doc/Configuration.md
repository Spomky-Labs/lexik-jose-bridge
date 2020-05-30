How to configure this bundle?
=============================

If you installed this bundle using Symfony Flex, it comes with a default configuration.
**It is very important to change the key sets otherwise you will have a security issue.**
**See the last section of this page to understand how to procced.**

*Note: you can find [a complete example from our application configuration used for the tests](https://github.com/Spomky-Labs/lexik-jose-bridge/blob/v2.0/Tests/app/config/config.yml#L27-L41).*

## Without Encryption

```yml
lexik_jose:
    ttl: 3600                                     // The TTL of each token issued by the bundle
    server_name: 'https://my.super.service/'      // This value is used to verify the issuer of the tokens
    audience: 'MyProject'                         // This value is used to verify the audience of the tokens. If not set `server_name` will be used.
    key_set: '%env(LEXIK_JOSE_SIGNATURE_KEYSET)%' // The signature key set (loaded through an env variable)
    key_index: 0                                  // The index of the signature key in the key set
    signature_algorithm: "RS512"                  // The signature algorithm.
    claim_checked:                                // A list of additional claim checker aliases (optional).
        - 'my_claim_checker_alias'                // See https://web-token.spomky-labs.com/components/claim-checker for more information
    mandatory_claims:                             // A list of claims that must be present (optional).
        - 'exp'                                   // See https://web-token.spomky-labs.com/components/claim-checker for more information
        - 'iat'
        - 'iss'
        - 'aud'
```

For all available signature algorithms and key sets, please refer to the [web-token/jwt-framework documentation](https://web-token.spomky-labs.com/).

## With Encryption

```yml
lexik_jose:
    ...
    encryption:
        enabled: true                                  // We enable the encryption (highly recommended)
        key_set: '%env(LEXIK_JOSE_ENCRYPTION_KEYSET)%' // The encryption key set (loaded through an env variable)
        key_index: 0                                   // The index of the encryption key in the key set
        key_encryption_algorithm: 'A256GCMKW'          // The key encryption algorithm
        content_encryption_algorithm: 'A256GCM'        // The content encryption algorithm
```

The `key_set` parameters must contain valid key sets as per the [RFC7517](https://tools.ietf.org/html/rfc7517).

**Note: we highly recommend you to enable the encryption support as the token may contain very sensitive information**

##Creation of the keys

As you can see in the previous sections, the keys are available through environment variables
`LEXIK_JOSE_SIGNATURE_KEYSET` and `LEXIK_JOSE_ENCRYPTION_KEYSET`.

These variables contain keys in the JWKSet format. Hereafter you will find some command lines to create those key sets.

The `key_index` parameters indicate the index of the key used to perform the cypher operation when the token is issued.
It is recommended to use the index value 0 (zero) and perform key rotations to change the key after a period of time.

In the following examples, we will use [this PHAR application](https://web-token.spomky-labs.com/console-command/phar-application).

```sh
curl -OL https://github.com/web-token/jwt-app/raw/gh-pages/jose.phar
curl -OL https://github.com/web-token/jwt-app/raw/gh-pages/jose.phar.pubkey
chmod +x jose.phar
```

###Signature Keyset

The following commands will generate 3 keys. The result should be set to the environment variable `LEXIK_JOSE_SIGNATURE_KEYSET`.

*Note: the algorithm defined with `--alg` shall be the same as the one in `signature_algorithm`.*

#### RSA-based algorithm (`RSxxx`, `PSxxx`)

The recommended key size for RSA signature is 2048 bits. 4096 bits and up offer more protection, but are slower.

```sh
./jose.phar keyset:generate:rsa 3 2048 --use sig --alg RS512 --random_id
```

#### EC-based algorithm (`ESxxx`)

```sh
./jose.phar keyset:generate:ec 3 P-521 --use sig --alg ES512 --random_id
```

#### Octet Key Pair-based algorithm (`EdDSA`)

```sh
./jose.phar keyset:generate:okp 3 Ed25519 --use sig --alg EdDSA --random_id
```

#### Hash-based algorithm (`HSxxx`)

```sh
./jose.phar keyset:generate:oct 3 512 --use sig --alg HS512 --random_id
```

*Note: the key size should be at least of the hash size e.g. HS512 => 512 bits.*

###Encryption Keyset

For encryption, the commands are very similar.

* Change `--use sig` into `--use enc`,
* Use the correct encryption algorithm,
* Set the result to the environment variable `LEXIK_JOSE_ENCRYPTION_KEYSET`.

Example: `./jose.phar keyset:generate:oct 3 512 --use enc --alg A256GCMKW --random_id`

##Rotation of the keys

After a period of time, you should change the key(s) used to compute the tokens.
But you should also keep the current key(s) in the keysets to continue validating already issued tokens.

The period of time for key rotation should be at least equal to the lifetime of the tokens you issue (parameter `ttl`).

The key rotation consist in 2 steps:

1. Create a new key and add on top of the keyset,
2. Remove the last key of that keyset.

The new key shall be of the same type as the ones already present in the keyset.
They usually have the same configuration.
With the PHAR application, change `keyset:` into `key:` and remove the number of keys (3 in our examples).

Example: `./jose.phar key:generate:oct 512 --use enc --alg A256GCMKW --random_id` 

The new key you receive can be added into the keyset. A convenient method exists to ease that step: `./jose.phar keyset:rotate` 
This method requires the keyset as first argument and the new key as second one.

Example: `./jose0phar keyset:rotate '{"keys":[…]}' '{"kid":"…","use":"sig","alg":"RS512","kty":"oct","k":"…"}'`
