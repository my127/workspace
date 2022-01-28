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
curl --output ./ws --location https://github.com/my127/workspace/releases/download/0.1.3/ws
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

#### Changelog Generation

We are keeping a changelog, powered by [GitHub Changelog Generator].

When ready to tag a release, make a new branch from the `0.2.x` branch for the changelog entries:
1. Generate a `repo` scope token for use with the changelog generator: https://github.com/settings/tokens/new?description=GitHub%20Changelog%20Generator%20token
2. Export it in your environment: `export CHANGELOG_GITHUB_TOKEN=...`
3. Run the following docker command to generate the changelog, replacing `1.2.0` with the version number as needed:
  ```bash
  docker run -e CHANGELOG_GITHUB_TOKEN="$CHANGELOG_GITHUB_TOKEN" -it --rm -v "$(pwd)":/usr/local/src/your-app -v "$(pwd)/github-changelog-http-cache":/tmp/github-changelog-http-cache githubchangeloggenerator/github-changelog-generator --user my127 --project workspace --exclude-labels "duplicate,question,invalid,wontfix,skip-changelog" --since-tag 0.1.0 --release-branch 0.2.x --future-release 0.2.0-rc.1
  ```
4. Examine the generated CHANGELOG.md. For every entry in the `Merged pull requests` section, examine the Pull Requests
   and assign each pull request either a `enhancement` label for a new feature, `bug` for a bugfix or `deprecated` for
   a deprecation.
5. Re-generate the changelog using step 3 as needed.
6. Commit the resulting changes, push and raise a pull request.
7. Once merged, continue with the release process below.

[humbug/box]: https://github.com/humbug/box
