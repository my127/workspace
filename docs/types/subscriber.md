# Type - Subscriber

There are several built-in events, you can also dispatch your own. Subscribers allow you to run scripts in response to these events.

## Declaration

```
on('event.name'):
  env:
    - NAME: Value
  exec: |
    #!interpreter(path:/location)|filter
    script
```

You can also use `before` and `after` which will prefix the event name with before and after respectively.

## Examples

### After harness enable

```
after('harness.install'): |
  #!bash
  echo "The harness is now installed."
```

Note: The event name in the above example would be `after.harness.install`.

### Trigger an event

```
on('my.event'): |
  #!bash
  echo "This is my custom event."
  
command('hi'): |
  #!php
  $ws->trigger('my.event');
```
