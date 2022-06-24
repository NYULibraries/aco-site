Arabic Collections Online site
========

## Prerequisites

In order to clone the repository, and build.

You'll need the following tools:

- [Git](https://git-scm.com)
- [Docker](https://www.docker.com/)

Nice to have:

- [Docker Compose](https://docs.docker.com/compose/)

Do not run `npm install`. The file package.json includes a list of scripts to build and deploy.

To build, run any of the script with build-*.

Example:

```
$ npm run build-local
```

If `npm` is not available, see `.scripts` in package.json for commands,

```
$ rm -rf ./build && docker build -o build . --build-arg GA=0 --build-arg VIEWER_SOURCE_URL=https://stage-sites.dlib.nyu.edu/viewer --build-arg APP_URL=http://localhost/aco --build-arg APP_ROOT=/aco --build-arg DISCOVERY_CORE=https://stagediscovery.dlib.nyu.edu/solr/viewer
```

To see/run the site locally:

```
$ docker-compose up -d
```

### Test URL

Open the URL http://localhost
