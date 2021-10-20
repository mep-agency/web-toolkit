# MEP Web Toolkit

## :warning: EXPERIMENTAL :warning:

This project is in its early stage of development, stuff may change completely
and BC cannot be guaranteed.

## How to create a new project

All the packages found in this repository won't be released on public
registries until we reach a minimum level of stability.

In the meantime, you can create a new project running:
```shell
composer create-project mep-agency/symfony-web-toolkit-skeleton PROJECT_PATH -n --repository="{\"type\": \"vcs\", \"url\": \"git@github.com:mep-agency/symfony-web-toolkit-skeleton.git\"}" --stability dev
```

Or create a project to manage deployments on a Kubernetes cluster with:
```shell
composer create-project mep-agency/mwt-k8s-cli-skeleton PROJECT_PATH -n --repository="{\"type\": \"vcs\", \"url\": \"git@github.com:mep-agency/mwt-k8s-cli-skeleton.git\"}" --stability dev
```
