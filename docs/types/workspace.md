# Type - Workspace

Although the only requirement for a workspace is for a `workspace.yml` file to be present you can use the workspace type to explicitly set the name, description and harness used.

## Declaration

```
workspace('name'):
  description: Example description here
  harness: package:version
```

### Attributes

The following attributes are automatically made available.

|  Key                    |  Notes                                                              |
|-------------------------|---------------------------------------------------------------------|
| `workspace.name`        | When not declared the base name of the workspace directory is used. |
| `workspace.description` |                                                                     |
| `workspace.harness`     |                                                                     |
| `namespace`             | Defaults to DNS friendly version of workspace name.                 |

## Examples

### A magento2 work environment

```
workspace('acme-ltd'):
  description: Example description here
  harness: magento2:latest
  
attribute('namespace'): my-custom-namespace
```
