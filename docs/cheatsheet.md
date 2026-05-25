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
  --crt=https://raw.githubusercontent.com/my-org/private-proxy-config/main/certs/mydomain.site.crt \
  --key=https://raw.githubusercontent.com/my-org/private-proxy-config/main/certs/mydomain.site.key
ws global service proxy restart
```

Set the project domain in `workspace.yml`:

```yaml
attributes:
  domain: mydomain.site
```

## How-to receive email

The email service is not running by default. It can be started with:

```bash
ws global service mail enable
```

This will allow email to be viewed at `https://mail.my127.site/`

This will collect email sent from any server through native `sendmail`.
