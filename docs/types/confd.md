# Type - confd
Declare a directory as holding configuration files, the twig template engine can then be used to render any listed files to the specified destination. Any declared attributes and functions are available to the template.

As the entire directory is made available to the twig environment features such as `block`, `extends`, `include` and `import` are available allowing you to cleanly organise your templates.

## Declaration

```
confd('path:/directory'):
  - { src: 'template.twig', dst: 'path:/file' }
```

## Examples

### Using a command to apply templates from a config directory

```
confd('workspace:/confd'):
  - { src: 'docker-compose/.env.twig', dst: 'workspace:/.env' }
  - { src: 'magento/auth.json.twig',   dst: 'workspace:/auth.json' }
  - { src: 'magento/env.php.twig',     dst: 'workspace:/app/etc/env.php' }
  
command('apply config'): |
  #!php
  $ws->confd('workspace:/confd')->apply();
```

On the `src` side the path is relative to the directory specified in the main confd declaration, for the `dst` you can use any of the available path types but `workspace:` and `harness:` will be the most common.

The above example uses a command to apply the config, the harness type also allows you to list confd directories which should be applied as part of the installation process.

