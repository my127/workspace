# Cheatsheet

This cheatsheet contains useful commands

## Invalid SSL certificate

You need only to restart the Traefik proxy service:

```bash
ws global service proxy restart
```

## Custom proxy domains

```bash
ws global service proxy config domain add mydomain \
  --name=mydomain.site \
  --crt=<certificate-url> \
  --key=<key-url>
ws global service proxy restart
```

See [Custom Global Proxy domains](custom-proxy-domains.md) for import, update,
remove, and project configuration examples.

## How-to receive email

The email service is not running by default. It can be started with:

```bash
ws global service mail enable
```

This will allow email to be viewed at `https://mail.my127.site/`

This will collect email sent from any server through native `sendmail`.
