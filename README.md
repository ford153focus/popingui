# popingui

Multihost ping tool

## Configuration

The pinged hosts live in `config.json`, next to the executable (for `dotnet run`
that is the project directory, where a `config.json` can also be placed):

```json
[
    { "address": "192.168.1.1", "description": "router" },
    { "address": "8.8.8.8",     "description": "dns.google", "timeout": 4444 },
    { "address": "192.168.1.20", "description": "Emily's Galaxy A20", "enabled": false }
]
```

| field         | required | default | meaning                                      |
| ------------- | -------- | ------- | -------------------------------------------- |
| `address`     | yes      |         | IP address or host name to ping              |
| `description` | no       | `""`    | free text shown in the `description` column  |
| `timeout`     | no       | `4444`  | ping timeout in milliseconds                 |
| `enabled`     | no       | `true`  | set to `false` to keep a host but skip it    |

A different config file can be passed as the first command line argument:

```sh
dotnet run -- /path/to/other-config.json
```
