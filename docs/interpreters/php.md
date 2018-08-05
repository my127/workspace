# Interpreter - PHP
The PHP interpreter can be used within Command, Subscriber and Function types to form the script to be run.

## Helper

A helper is made available which can access attributes, commands and functions.

|  Usage                      |  Notes                                                |
|-----------------------------|-------------------------------------------------------|
| `$ws['sample.key.name']`    | ArrayAccess interface for interacting with attributes |
| `$ws('command input here')` | __invoke for calling declared commands                |
| `$ws->function('arg')`      | __call for calling declared functions                 |

## Examples

#### Hello world
```
command('hello'): |
  #!php
  echo "Hello World";
  
>>> ws hello
Hello World
```

#### Accessing attributes
```
attribute('message'): Hello World

command('hello'): |
  #!php
  echo $ws['message'];
  
>>> ws hello
Hello World
```

#### Declaring and calling functions
```
function('greet', [name]): |
  #!php
  = "Hello {$name}.";

command('hello'): |
  #!php
  echo $ws->greet('Guest');

>>> ws hello
Hello Guest
```
When declaring a function if you wish to return a value use `=` on the last line of the script followed by an expression.

#### Running other commands
```
command('hello'): |
  #!php
  echo "Hello World";

command('say hello'): |
  #!php
  $ws('hello');

>>> ws say hello
Hello World
```

#### Running the script from a specific directory
```
command('get my cwd'): |
  #!php(workspace:/sample/path/here)
  echo getcwd();

>>> mkdir -p sample/path/here
>>> ws get my cwd
/path/to/workspace/sample/path/here
```

`workspace:`, `harness:` and `cwd`: are the path prefixes available by default.