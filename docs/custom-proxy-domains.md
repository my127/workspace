# Custom Global Proxy domains

Workspace uses `my127.site` by default for local HTTPS hostnames. You can also
register extra Global Proxy domains on your machine, so different projects can
use different DNS suffixes while sharing the same Traefik proxy.

## Contents

- [Register a domain](#register-a-domain)
- [Host the proxy configuration files](#host-the-proxy-configuration-files)
- [Use a domain in a project](#use-a-domain-in-a-project)

Registered domains are stored in:

```text
~/.config/my127/workspace/proxy-domains.yml
```

The built-in `my127.site` domain remains available by default. Do not add it to
`proxy-domains.yml`; that file is for extra domains registered on the machine.

## Register a domain

A proxy domain must be registered before the Global Proxy can serve it. The
certificate and key must be reachable `http://` or `https://` URLs.

Certificate URLs may point at private locations that are already reachable from
the machine, such as an internal HTTP(S) endpoint.

The certificate must cover the bare domain and the subdomains used by projects
or global services. For example, a certificate for `mydomain.site` should also
cover `*.mydomain.site`.

## Host the proxy configuration files

Before developers import a custom proxy domain, the organisation needs stable
HTTP(S) URLs for:

- `domains.yml`: the Workspace proxy domain configuration to import.
- `fullchain.pem`: the certificate served by the Global Proxy.
- `privkey.pem`: the private key used by that certificate.

The files do not need to live in the same repository, website, or directory.
The certificate and key URLs are read from the attributes in `domains.yml`; the
layout below keeps them together only to make the example easy to follow.

GitHub raw URLs work only for public repositories because Workspace does not
authenticate to GitHub. For private certificate material, use organisation-only
HTTP(S) URLs reachable from developer machines.

Suggested structure for one or more domains:

```text
public-proxy-config/
├── domains.yml
└── certs/
    ├── mydomain.site/
    │   ├── fullchain.pem
    │   └── privkey.pem
    └── otherdomain.site/
        ├── fullchain.pem
        └── privkey.pem
```

`domains.yml` can reference any number of domains. Each entry should point to
the hosted certificate and key URLs for that domain. When certificates are
renewed, update the hosted certificate files and keep the URLs stable, then
developers only need to restart the Global Proxy. Re-import `domains.yml` only
when registering a new machine or adding a domain; use `domain update` when an
existing domain definition changes.

The following commands are alternatives for common tasks. Run the one that
matches the change you want to make.

```bash
# List registered proxy domains.
ws global service proxy config domain list

# Register one domain manually.
ws global service proxy config domain add mydomain \
  --name=mydomain.site \
  --crt=https://raw.githubusercontent.com/my-org/public-proxy-config/main/certs/mydomain.site/fullchain.pem \
  --key=https://raw.githubusercontent.com/my-org/public-proxy-config/main/certs/mydomain.site/privkey.pem

# Import domains from a local file.
ws global service proxy config domain import domains.yml

# Import domains from a public GitHub repository.
ws global service proxy config domain import https://raw.githubusercontent.com/my-org/public-proxy-config/main/domains.yml

# Import domains from an internal website.
ws global service proxy config domain import https://proxy-config.example.internal/domains.yml

# Replace one registered domain.
ws global service proxy config domain update mydomain \
  --name=mydomain.site \
  --crt=https://raw.githubusercontent.com/my-org/public-proxy-config/main/certs/mydomain.site/fullchain.pem \
  --key=https://raw.githubusercontent.com/my-org/public-proxy-config/main/certs/mydomain.site/privkey.pem

# Remove one registered domain.
ws global service proxy config domain remove mydomain
```

Example `domains.yml`:

```yaml
attributes:
  global:
    service:
      proxy:
        domains:
          mydomain:
            name: mydomain.site
            https:
              crt: https://raw.githubusercontent.com/my-org/public-proxy-config/main/certs/mydomain.site/fullchain.pem
              key: https://raw.githubusercontent.com/my-org/public-proxy-config/main/certs/mydomain.site/privkey.pem
            crt_file: mydomain.site.crt
            key_file: mydomain.site.key
          otherdomain:
            name: otherdomain.site
            https:
              crt: https://proxy-config.example.internal/certs/otherdomain.site/fullchain.pem
              key: https://proxy-config.example.internal/certs/otherdomain.site/privkey.pem
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
