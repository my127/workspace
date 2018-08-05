# Workspace [![Build Status](https://travis-ci.org/my127/workspace.svg?branch=0.1.x)](https://travis-ci.org/my127/workspace)

Workspace is a tool to orchestrate and bring consistency to your project environments. 

## Documentation

### Getting Started

#### Installation
```
wget https://github.com/my127/workspace/releases/download/0.1.0-alpha.2/my127ws.phar \
   && chmod +x my127ws.phar \
   && sudo mv my127ws.phar /usr/local/bin/ws
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