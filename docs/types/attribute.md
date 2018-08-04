# Type - Attribute

Any values you wish to use in confd templates or expanded in scripts should be set as attributes.

 - [Examples](#examples)
 - [Precedence](#precedence)

## Examples

### Standard attribute

```
attribute('my.example.message'): Hello World

command('speak'): |
  #!bash|@
  echo "@('my.example.message')"

```

### Standard attribute specified using a root object
```
attributes:
  my:
    example:
       message: Hello World

command('speak'): |
  #!bash|@
  echo "@('my.example.message')"

```

### Attribute expressions

```
attribute('db'):
  driver: mysql
  host: localhost
  name: application
  
attribute('db.dsn'): = @('db.driver') ~ ':host=' ~ @('db.host') ~ ';dbname=' ~ @('db.name')
```

Any attribute value starting with `=` is treated as an symfony expression.

### Overriding an attribute

```
attribute.override('db.password'): password
```

### Overriding attributes using a root object

```
attributes.override:
  db:
    password: password
```


## Precedence

You can set the priority by suffixing the attribute declaration with `default` or `override`, attributes with no priority set will default to `normal`. The scope is determined by the location of the file within which the attribute was declared.

Attributes of the same key and precedence are merged in the order they are loaded.

```
Scope       Priority    Precedence
---------   ---------   ----------
Harness     Default         1
Workspace   Default         2
Global      Default         3
Harness     Normal          4
Workspace   Normal          5
Global      Normal          6
Harness     Override        7
Workspace   Override        8
Global      Override        9
```