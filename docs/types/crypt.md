# Type - crypt
Declare a key which can be used to encrypt and decrypt workspace secrets. 

## Declaration

```
key('default'): 81a7fa14a8ceb8e1c8860031e2bac03f4b939de44fa1a78987a3fcff1bf57100
```
**Note:** The `default` key is randomly generated when a workspace is created, alternatively you can use `ws secret generate-random-key` to generate a random value which can be used as a key.

## Examples

### Using an encrypted value

```
workspace.override.yml:
key('default'): 81a7fa14a8ceb8e1c8860031e2bac03f4b939de44fa1a78987a3fcff1bf57100

>>> ws secret encrypt "Hello World"
YTozOntpOjA7czo3OiJkZWZhdWx0IjtpOjE7czoyNDoi98rFejkefPnZG1CjzGeFyvSAMgafKv2TIjtpOjI7czoyNzoiSwcG2YiM3vV8CdZXgxDM2q+ZmRmPRNyz7OgcIjt9

workspace.yml:

attribute('message'): = decrypt('YTozOntpOjA7czo3OiJkZWZhdWx0IjtpOjE7czoyNDoi98rFejkefPnZG1CjzGeFyvSAMgafKv2TIjtpOjI7czoyNzoiSwcG2YiM3vV8CdZXgxDM2q+ZmRmPRNyz7OgcIjt9')

command('hello'): |
  #!bash|@
  echo "@('message')"
  
>>> ws hello
Hello World
```
