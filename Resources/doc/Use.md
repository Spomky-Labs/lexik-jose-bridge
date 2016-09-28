How to use this bundle?
=======================

Almost everything is done for you by the bundle.

# Console Commands

## Delete and Generate New Keys

Signature and (optional) encryption keys are automatically created.
If you want to delete or generate new keys, then you can use the following commands:

```sh
bin/console spomky-labs:lexik_jose:delete
```

or 

```sh
bin/console spomky-labs:lexik_jose:regen
```

**Use with caution**

*We recommend you to use these commands only if you think they are compromised.*
*All tokens issued with the previous keys will not be verified and then rejected, even if they are not expired.*

## Rotate Keys

For security purpose, you are encouraged to rotate your keys.
A console command is also available to ease your work:

```sh
bin/console spomky-labs:lexik_jose:rotate
```

By default, keys rotate every 7 days, but you can define your own period:

```sh
bin/console spomky-labs:lexik_jose:rotate "2 days"
```

The argument can be anything understood by [\DateInterval::createFromDateString()](https://secure.php.net/manual/en/dateinterval.createfromdatestring.php).
We recommend you to set at least the lifetime of the token (see the [LexikJWTAuthenticationBundle](https://github.com/lexik/LexikJWTAuthenticationBundle) configuration).