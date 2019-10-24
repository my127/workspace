# Workspace [![Build Status](https://travis-ci.org/my127/workspace.svg?branch=0.1.x)](https://travis-ci.org/my127/workspace)

Workspace is a tool to orchestrate and bring consistency to your project environments. 

## Documentation

### Getting Started

#### Requirements

 - `PHP-7.2+`
 - `sodium` php extension installed and activated in php.ini if it's not enabled by default 
 - `docker 17.04.0+`
 - `docker-compose (compose file version 3.1+)`

#### Installation

Download the `ws` file from the [Latest Release](https://github.com/my127/workspace/releases/latest) make executable and 
move to a location in your PATH, eg.

```
chmod +x ws && sudo mv ws /usr/local/bin/ws
```

#### Key Concepts

##### Workspace
 
`workspace` describes both the CLI tool `ws` and the project environment itself. It is dependent on a harness definition.
 
##### Harness
 
 A harness is a "flavour" of workspace which defines the blueprint to which a project environment will adhere. It  
 incorporates two distinct elements:
 
 1. *Skeleton* - a project stub applied when the workspace is first created with a given harness 
 2. *Overlay* - concrete files that are always applied to the workspace
 
 We currently have harnesses to support the following frameworks: 
 
 * Magento 1 & 2
 * Drupal 8
 * Spryker
 * Akeneo
 * Node
 * Wordpress
 
## Commands

By default a workspace project ships with the following commands:

| Command                                    | Description                                             |
|---                                         |---                                                      |
|`ws install`                                |Install project                                          |
|`ws enable`                                 |Start a previously disabled environment                  |
|`ws disable`                                |Shutdown environment                                     |
|`ws destroy`                                |Back to the drawing board                                |
|`ws console`                                |Start an interactive console in the environment          |
|`ws exec {drush, composer... etc}`          |Run a commands in the environment (eg. composer)         |

Commands vary per harness and the actions triggered by these commands are configurable per project.

## Anatomy of workspace

Workspace is a PHP command line, [Symfony](https://symfony.com/) based tool. It utilises the [Twig](https://twig.symfony.com/) 
templating language to facilitate applying customisations required by each harness.

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
