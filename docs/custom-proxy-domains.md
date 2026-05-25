# Custom Global Proxy domains

Workspace uses `my127.site` by default for local HTTPS hostnames. You can also
register extra Global Proxy domains on your machine, so different projects can
use different DNS suffixes while sharing the same Traefik proxy.

Registered domains are stored in:

```text
~/.config/my127/workspace/proxy-domains.yml
```

The built-in `my127.site` domain remains available by default. Do not add it to
`proxy-domains.yml`; that file is for extra domains registered on the machine.

## Register a domain

A proxy domain must be registered before the Global Proxy can serve it. The
certificate and key must be reachable `http://` or `https://` URLs.

```bash
ws global service proxy config domain add mydomain \
  --name=mydomain.site \
  --crt=https://raw.githubusercontent.com/my-org/private-proxy-config/main/certs/mydomain.site.crt \
  --key=https://raw.githubusercontent.com/my-org/private-proxy-config/main/certs/mydomain.site.key
```

Certificate URLs may point at private locations that are already readable from
the machine, such as a raw file URL in a private GitHub repository.

The certificate must cover the bare domain and the subdomains used by projects
or global services. For example, a certificate for `mydomain.site` should also
cover `*.mydomain.site`.

You can inspect, import, replace, or remove registered domains with:

```bash
ws global service proxy config domain list
ws global service proxy config domain import proxy-domains.yml
ws global service proxy config domain import https://raw.githubusercontent.com/my-org/proxy-config/main/proxy-domains.yml
ws global service proxy config domain update mydomain \
  --name=mydomain.site \
  --crt=https://raw.githubusercontent.com/my-org/private-proxy-config/main/certs/mydomain.site.crt \
  --key=https://raw.githubusercontent.com/my-org/private-proxy-config/main/certs/mydomain.site.key
ws global service proxy config domain remove mydomain
```

Example `proxy-domains.yml`:

```yaml
attributes:
  global:
    service:
      proxy:
        domains:
          mydomain:
            name: mydomain.site
            https:
              crt: https://raw.githubusercontent.com/my-org/private-proxy-config/main/certs/mydomain.site.crt
              key: https://raw.githubusercontent.com/my-org/private-proxy-config/main/certs/mydomain.site.key
            crt_file: mydomain.site.crt
            key_file: mydomain.site.key
          otherdomain:
            name: otherdomain.site
            https:
              crt: https://raw.githubusercontent.com/my-org/private-proxy-config/main/certs/otherdomain.site.crt
              key: https://raw.githubusercontent.com/my-org/private-proxy-config/main/certs/otherdomain.site.key
            crt_file: otherdomain.site.crt
            key_file: otherdomain.site.key
```

After changing registered domains, restart the proxy:

```bash
ws global service proxy restart
```

Enabled global services such as mail, logger, or tracing may also need a
restart before they pick up the new host rules.

## Use a domain in a project

A project uses the normal `domain` attribute. Register the proxy domain first,
then set the project domain:

```yaml
attributes:
  domain: mydomain.site
```
