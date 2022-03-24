# Workspace [![CI](https://github.com/my127/workspace/actions/workflows/ci.yml/badge.svg)](https://github.com/my127/workspace/actions/workflows/ci.yml)

Workspace is a tool to orchestrate and bring consistency to your project environments.

## Documentation

### Getting Started
#### Requirements
 - `PHP-7.3+`
 - `sodium` php extension installed and activated in php.ini if it's not enabled by default
 - `curl` if you wish to use the global traefik proxy
 - `docker 17.04.0+`
 - `docker-compose (compose file version 3.1+)`

#### Installation

Download the `ws` file from the [Latest Release](https://github.com/my127/workspace/releases/latest) make executable and move to a location in your PATH, eg.
```bash
curl --output ./ws --location https://github.com/my127/workspace/releases/download/0.2.1/ws
chmod +x ws && sudo mv ws /usr/local/bin/ws
```

Confirm you can run the `ws` command, e.g.
```
ws --help
```

#### Creating a workspace
```
# TODO
```

### Anatomy of a workspace

#### Key Concepts
 - [Workspace](docs/concepts/workspace.md)
 - [Harness](docs/concepts/harness.md)

#### Types

 - [Attribute](docs/types/attribute.md)
 - [Command](docs/types/command.md)
 - [Confd](docs/types/confd.md)
 - [Crypt](docs/types/crypt.md)
 - [Function](docs/types/function.md)
 - [Harness](docs/types/harness.md)
   - [Repository](docs/types/harness-repository.md)
 - [Subscriber](docs/types/subscriber.md)
 - [Workspace](docs/types/workspace.md)

#### Interpreters

 - [Bash](docs/interpreters/bash.md)
 - [PHP](docs/interpreters/php.md)

### Tutorials

 - [Creating a simple harness](docs/tutorials/create-harness.md)

### Building

First install development dependencies by running `composer install`. This will set up [humbug/box] as well.

To build workspace, you can run the `build.sh` script.

To test the build in multiple PHP versions, there is a docker-compose.yml provided.

To build:
```bash
docker-compose build --pull
```
To fix volume permissions, if you are using Linux, run:
```bash
HOST_OS_FAMILY=linux docker-compose up -d
```
If you are using macOS, run:
```bash
HOST_OS_FAMILY=darwin docker-compose up -d
```

You can now do:
```bash
docker-compose exec -u build builder73 /app/build.sh
docker-compose exec -u build builder74 /app/build.sh
docker-compose exec -u build builder80 /app/build.sh
```

### Release

### Performing a Release

1. Head to the [releases page] and create a new release:
    * Enter the tag name to be created
    * Give it a title containing tag name
    * Click "Auto-generate release notes"
    * Examine the generated release notes. For every entry in the `Other Changes` section,
      examine the Pull Requests and assign each pull request either a `enhancement` label
      for a new feature, `bug` for a bugfix or `deprecated` for 
      a deprecation.
    * Cancel the release if any pull request labels needed changing, and repeat from 1
    * Click `Publish Release`

[releases page]: https://github.com/my127/workspace/releases
