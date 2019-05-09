# Usage example
# 1) Build container image
# $ docker build -t nyudlts/aco:latest .
# 2) Run container
# $ docker run -d --name=aco -p 8000:80 nyudlts/aco:latest

# Stage 1
FROM node:8 as node

RUN apt-get update -qq \
  && apt-get install -y build-essential ruby-full \
  && gem install compass

WORKDIR /usr/src/app

COPY . .

RUN npm install -g grunt-cli \
  && npm install \
  && npm run-script build-docker

# Stage 2
FROM httpd:2.4-alpine

RUN sed -i '/LoadModule rewrite_module/s/^#//g' /usr/local/apache2/conf/httpd.conf

RUN { \
  echo 'IncludeOptional conf.d/*.conf'; \
} >> /usr/local/apache2/conf/httpd.conf \
  && mkdir /usr/local/apache2/conf.d

COPY --from=node /usr/src/app/build /usr/local/apache2/htdocs/aco

COPY --from=node /usr/src/app/source/robots.txt /usr/local/apache2/htdocs/robots.txt

COPY ./httpd.conf /usr/local/apache2/conf.d/aco.conf
