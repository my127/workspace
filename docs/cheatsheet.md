# Cheatsheet

This cheatsheet contains useful commands

## Invalid SSL certificate

You need only to restart the Traefik proxy service:

```bash
ws global service proxy restart
```

## Custom proxy domain

See [Custom Global Proxy Domain](custom-proxy-domain.md) for the full setup.

Minimal global config file:

```text
~/.config/my127/workspace/proxy.yml
```

```yaml
attribute('global.service.proxy.domain'): dev.example.test
attribute('global.service.proxy.https.crt'): https://example.test/dev.example.test.crt
attribute('global.service.proxy.https.key'): https://example.test/dev.example.test.key
```

Restart the proxy after changing these values.

## How-to receive email

The email service is not running by default. It can be started with:

```bash
ws global service mail enable
```

This will allow email to be viewed at `https://mail.<proxy-domain>/`

This will collect email sent from any server through native `sendmail`.
