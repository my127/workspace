# Type - Command

Create simple commands to help manage your workspace.

## Declaration

```
command('usage pattern', 'help page'):
  description:
    Optional short description, if given, will replace the default "ws <command>" on the help pages
  env:
    - NAME: Value
  exec: |
    #!interpreter(path:/location)|filter
    script
```

## Options and arguments (usage pattern)

`input.*` can also be used in expression filtered exec blocks (`|=`, see below). 

### Arguments
```
command('cmd <required> [<optional>]'):
  env:
    REQUIRED: = input.argument('required')
    OPTIONAL: = input.argument('optional')
  exec: |
    #!bash
    echo "'${REQUIRED}'"
    echo "'${OPTIONAL}'" 
```
Defines arguments. The command will not be recognized without required arguments.

```
command('cmd %'):
  env:
    ALL: = input.argument('%')
  exec: |
    #!bash
    echo "'${ALL}'"
```

The special token `%` describes an argument that contains
the rest of the input, it can only be the last character in the command.

### Arguments with select values

```
command('cmd [on|off]'):
  env:
    OPTIONAL_ARG: = input.command(1)
```
or
```
command('cmd (on|off])'):
  env:
    REQUIRED_ARG: = input.command(1)
```

Defines arguments that can only have select values, these only work correctly
as the last part in the command and must be read using `input.command(index)`, 0-based.

### Boolean options
```
command('cmd [--option]'):
  env:
    VERBATIM: = input.option('option')
    YES_OR_NO: = input.option('option') ? 'yes' : 'no'
  exec: |
    #!bash
    echo "'${VERBATIM}'"
    echo "'${YES_OR_NO}'" 
```

For a boolean option, `input.option` will read a `'1'` if given, `''` (empty string) otherwise.
The Inviqa base docker harness contains a convenient `boolToString()` function that provides
the yes/no conversion done here manually.  

```
command('cmd [-iou]'):
  env:
    OPT_I: = input.option('i')
    OPT_O: = input.option('o')
    OPT_U: = input.option('u')
```

Sequence shortcut for defining multiple bool options.

### Value options
```
command('cmd [--option=<value>]'):
  env:
    AS_OPTION: = input.option('option')
  ...
```

### Mutually exclusive options
```
command('cmd [-b|--bool] [-s=<value>|--string=<value>]'):
  env:
    BOOL_OPTION: = input.option('bool') || input.option('b')
    VALUE_OPTION: = input.option('string') ?: input.option('s')
  exec: |
    #!bash
    echo "'${BOOL_OPTION}'"
    echo "'${VALUE_OPTION}'"
```

You can use these to define mutually exclusive options including long/shortform alternatives.
Be aware these options are technically considered different options, so you have to read both if
you're using them for long/short alternatives.

### Technicalities

The console parser builder is capable of building loops like this:
```
command('cmd [-v=<value>]... (on|off)... <arg>...'):
```

However, in all of these cases, either workspace or the console itself errors out because arrays are passed
where strings are expected.

## Examples

### Hello world in bash

```
command('hello world'): |
  #!bash
  echo "Hello World"
```

Only the usage pattern and body of the script are required, everything else is optional. Note the pipe after the header declaration to signify a multi-line YAML string.

### Using environment variables

```
command('hello from environment'):
  env:
    MESSAGE: Hello World
  exec: |
    #!bash
    echo "$MESSAGE"
```

### Ensure the script is run from a specific path

```
command('ls'): |
  #!bash(workspace:/)
  ls
```

This command will always be run from the root of the workspace, you can also use `cwd:` and harness `harness:` prefixes.

### Leveraging attributes with the attribute filter

```
attribute('aws'):
  s3: s3://bucket
  id: my-id-here
  key: my-key-here

command('assets download'):
  env:
    AWS_ID:  =@('aws.id')
    AWS_KEY: =@('aws.key')
  exec: |
    #!interpreter(workspace:/)|@
    passthru ws-aws s3 sync @('aws.s3') assets/development
```

This is a more complex example showing how the attribute filter `|@` can be used to place values directly into a script before it is executed.

### Using expressions with the expression filter

```
attribute('aws'):
  s3: s3://bucket/path
  id: my-id-here
  key: my-key-here

command('assets download'):
  env:
    AWS_ID:  =@('aws.id')
    AWS_KEY: =@('aws.key')
  exec: |
    #!interpreter(workspace:/)|=
    passthru ws-aws s3 sync ={ @('aws.s3') ~ '/assets/development' } assets/development
```

There may be times when you need more complex logic than a simple replacement, in these cases the expression filter `|=` can be used.
