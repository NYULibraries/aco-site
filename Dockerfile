# Usage example
# 1) Build: $ docker build -o build .

FROM node:10.15.1 as build

WORKDIR /usr/src/app

COPY . .

ARG GA='1'
ARG VIEWER_SOURCE_URL='https://sites.dlib.nyu.edu/viewer'
ARG APP_URL='https://dlib.nyu.edu/aco'
ARG APP_ROOT='/aco'
ARG DISCOVERY_CORE='https://discovery1.dlib.nyu.edu/solr/viewer'

RUN apt-get update -qq \
  && apt-get install -y build-essential ruby-full \
  && gem install compass \
  && npm install -g grunt-cli \
  && npm install \
  && GA=${GA} VIEWER_SOURCE_URL=${VIEWER_SOURCE_URL} APP_URL=${APP_URL} APP_ROOT=${APP_ROOT} DISCOVERY_CORE=${DISCOVERY_CORE} grunt

FROM scratch AS export-stage

COPY --from=build /usr/src/app/build /
