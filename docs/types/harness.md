# Type - Harness

When defining a harness, a harness block is used to declare common behaviour

## Declaration

```
harness('name'):
  description: Example description here
  require:
    workspace: '~0.3'
```

### Attributes

The following attributes are automatically made available.

|  Key                         |  Notes                                                         |
|------------------------------|----------------------------------------------------------------|
| `harness.name`               |                                                                |
| `harness.description`        |                                                                |

The following attributes can be added to change behaviour

|  Key                          |  Notes                                                        |
|-------------------------------|---------------------------------------------------------------|
| `harness().require.services`  | A list of ws global services to launch on `ws install`        |
| `harness().require.confd`     | A list of confd blocks to execute on `ws harness prepare`     |
| `harness().require.workspace` | A composer/semver version constraint to force upgrade         |

## Examples

### A harness that implements a web server to be served by Workspace's proxy

```yaml
harness('acme-web-server'):
  description: Example description here
  require:
    services:
      - proxy
```

### A harness implementing a confd rendering

```yaml
harness('acme-templatable'):
  description: Example description here
  require:
    confd:
      - harness:/

confd('harness:/'):
  - src: template.conf
```

### A harness requiring version upgrade
```yaml
harness('acme-upgrade'):
  description: Example description here
  require:
    workspace: '~0.3'
```
