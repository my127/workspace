# Type - Workspace

Although the only requirement for a workspace is for a `workspace.yml` file to be present you can use the workspace type to explicitly set the name, description and harness used.

## Declaration

```
workspace('name'):
  description: Example description here
  harness: package:version
  overlay: tools/workspace
  require:
    workspace: '~0.3'
```

### Attributes

The following attributes are automatically made available.

|  Key                    |  Notes                                                              |
|-------------------------|---------------------------------------------------------------------|
| `workspace.name`        | When not declared the base name of the workspace directory is used. |
| `workspace.description` |                                                                     |
| `namespace`             | Defaults to DNS friendly version of workspace name.                 |

The following attributes can be added to change behaviour

|  Key                            |  Notes                                                       |
|---------------------------------|--------------------------------------------------------------|
| `workspace().harness`           | A workspace harness to install                               |
| `workspace().overlay`           | A directory to overlay onto the .my127ws folder              |
| `workspace().require.workspace` | A composer/semver version constraint to force upgrade        |

## Examples

### A magento2 work environment

```yaml
workspace('acme-ltd'):
  description: Example description here
  harness: magento2:latest
  
attribute('namespace'): my-custom-namespace
```

### A project requiring version upgrade
```yaml
workspace('acme-upgrade'):
  description: Example description here
  require:
    workspace: '~0.3'
```

### A project implementing a overlay of a harness

In order to customise the harness, with overlay files in the project's tools/workspace folder

```yaml
workspace('acme-ltd'):
  description: Example description here
  harness: magento2:latest
  overlay: tools/workspace
```