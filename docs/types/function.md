# Type - Function

Functions can be used anywhere expressions are used and when using the PHP interpreter for a command script. 

## Declaration

```
function('name', [arg1, arg2]):
  env:
    - NAME: Value
  exec: |
    #!interpreter(path:/location)|filter
    script line 1
    script line 2
    = return expression
```

Note: If you wish to return a value the last line of your script should start with `=` followed by an expression.

## Examples

### Simple addition in bash
Arguments are declared as variables in the header above the declared script.
```
function('add', [v1, v2]): |
  #!bash
  ="$((v1+v2))"
  
command('add'): |
  #!php
  echo $ws->add(2, 2) + 2;
```

### Using environment variables
In addition to arguments you can also make environment variables available.
```
function('hello', [v1]):
  env:
    MESSAGE: Hello
  exec: |
    #!bash
    ="${MESSAGE} ${v1}"

command('hi'): |
  #!bash|=
  echo "={ hello('World') }"
```
