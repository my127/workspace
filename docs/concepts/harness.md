
## Content of an harness
This is a list of recommended files or directory to create your own harnesses:

```
harness-sample
├── LICENSE                   # The licence file with which to release your harness
├── README.md                 # The main documentation file for your harness
├── _twig/                    # The TWIG templated collection for any file that required to be rendered dynamically
├── .ci/                      # The collection of semple files Continuous Integration
├── application/              # The default collection of files for a new project application
├── docker/                   # The default collection of files for the docker images build for a new application
├── docker-compose.yml.twig   # the TWIG template for the docker-compose file to run a new application
├── docs/                     # The directory for the harness extended documentation
├── harness/                  # The directory for the extended harness configuration
│   ├── attributes/
│   ├── config/
│   └── scripts/
├── harness.yml               # The main harness configuration file
├── helm/                     # The directory containing the TWIG templates to render a new application K8s' Helm Chart dynamically
│   ├── app/
│   └── qa/
└── mutagen.yml.twig          # The TWIG template for the Mutagen file
```

### TWIG templates

```
├── _twig/
├── docker-compose.yml.twig
└── mutagen.yml.twig
```
### Continuous Integrations samples
```
├── .ci/
```
### Application
```
├── application/
│   ├── overlay
│   ├── skeleton
│   └── static
```

### Docker images default definitions

```
├── docker/
│   └── image/
```

### Harness documentation
```
├── docs/
```
### Harness default configuration

```
├── harness/
│   ├── attributes/
│   │   ├── common.yml
│   │   ├── docker-base.yml
│   │   ├── docker.yml
│   │   └── environment/
│   ├── config/
│   │   ├── cleanup.yml
│   │   ├── commands.yml
│   │   ├── confd.yml
│   │   ├── events.yml
│   │   ├── external-images.yml
│   │   ├── functions.yml
│   │   ├── mutagen.yml
│   │   ├── pipeline.yml
│   │   ├── secrets.yml
│   └── scripts/
│       ├── destroy.sh
│       ├── disable.sh
│       ├── enable.sh
│       ├── latest-mutagen-release.php
│       ├── mutagen.sh
│       ├── rebuild.sh
├── harness.yml
```
### HELM templates
```
├── helm/
│   ├── app/
│   │   ├── Chart.yaml.twig
│   │   ├── _twig
│   │   ├── templates
│   │   ├── values-preview.yaml.twig
│   │   ├── values-production.yaml.twig
│   │   └── values.yaml.twig
│   └── qa/
│       ├── Chart.yaml.twig
│       ├── requirements.yaml.twig
│       └── values.yaml.twig
```
