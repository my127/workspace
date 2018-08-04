# Type - Command

Create simple commands to help manage your workspace.

## Declaration

```
command('usage pattern', 'help page'):
  env:
    - NAME: Value
  exec: |
    #!interpreter(path:/location')|filter
    script
```

## Examples

### Hello world in bash

```
command('hello world'): |
  #!bash
  echo "Hello World"
```

Only the usage pattern and body of the script are required, everything else is optional. Note the pipe after the header declaration to signify a multi-line string.

### Using environment variables

```
command('hello from environment'):
  env:
    - MESSAGE: Hello World
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
    - AWS_ID:  =@('aws.id')
    - AWS_KEY: =@('aws.key') 
  exec: |
    #!interpreter(workspace:/)|@
    passthru ws.aws s3 sync @('aws.s3') assets/development
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
    - AWS_ID:  =@('aws.id')
    - AWS_KEY: =@('aws.key') 
  exec: |
    #!interpreter(workspace:/)|=
    passthru ws.aws s3 sync ={ @('aws.s3') ~ '/assets/development' } assets/development
```

Thre may be times when you need more complex logic than a simple replacement, in these cases the expression filter `|=` can be used. 