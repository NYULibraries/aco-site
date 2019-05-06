Arabic Collections Online site
========

### Setup

Make sure you have http://nodejs.org and http://gruntjs.com

### Installation

```bash
$ git clone https://github.com/NYULibraries/aco-site.git
```

### Install dependencies

```bash
$ cd aco-site && npm install
```

### Getting started

#### Adding a page

All the pages are represented in the configuration file (**conf.json**). To add a new page include it in the pages object. title and route are require (note that the route is relative to the **appRoot**). In this file you also declare mustache variables.

##### conf.json

```javascript
"myNewPage" : {
        "title" : "My new page"
      , "route" : "/mynewpage/index.html"
}
```

##### myNewPagePartial.mustache

```mustache
<ul>
  <li>I'm a list</li>
</ul>
```

##### myNewPage.mustache

```mustache
<!doctype html>
<html>
<head>{{> head }}</head>
<body data-app="{{ appUrl }}" data-appRoot="{{ appRoot }}">
  {{> header }}
  <a href="{{ appUrl }}">Home</a> &gt; {{ title }}
  <h1>{{ title }}</h1>
  {{> myNewPagePartial }}
  {{> footer }}
</body>
</html>
```

#### Make changes

All the changes are made to the source files **./source**. Depending in your server configuration
you will need to include the **.htaccess** file in each directory or make modifications to the one
inside build directory.

#### Build site

```bash
$ grunt
```
  If you want to watch the source files.

```bash
$ grunt watch
```

## Test

We are introducing [Nightwatch.js](http://nightwatchjs.org/) as this project test suite.

[Nightwatch.js](http://nightwatchjs.org/) is an easy to use Node.js based End-to-End (E2E) testing solution for browser based apps and websites. It uses the powerful W3C WebDriver API to perform commands and assertions on DOM elements.

A good introduction to learn [Nightwatch.js](http://nightwatchjs.org/) can be found at [Learn how to use Nightwatch.js](https://github.com/dwyl/learn-nightwatch)

### Quick start with Nightwatch.js

1) Install [Nightwatch.js](http://nightwatchjs.org/) globally in your machine.

```
npm install nightwatch -g
```

2) Add enviorment information file

```
cp sample.env .env
```

3) Add your enviorment information. E.g.,

```
export APP_URL=http://localhost/aco
```

NOTE: *At the moment we only have an enviormental variable for the site URL that we want to test*

4) Inside the same folder that has the nightwatch.conf.js type:

```
npm test
```

NOTE: *You need to run `npm install` if you happend to have this repository around and willl resume work on this project.


### DLTSACO652

DLTSACO652 introduces 2 new source information and curl.json need to be updated to work.

```json
    "categoryQueryEn" : {
      "src" : "http://stagediscovery.dlib.nyu.edu:8983/solr/viewer/select?wt=json&json.nl=arrmap&q=sm_collection_code:aco&facet=true&rows=0&facet.query=%7B!key=%22General%20Works%22%7Dss_call_number:A*&facet.query=%7B!key=%22Philosophy.%20Psychology.%20Religion%22%7Dss_call_number:B*&facet.query=%7B!key=%22Auxiliary%20Sciences%20of%20History%22%7Dss_call_number:C*&facet.query=%7B!key=%22World%20History%20and%20History%20of%20Europe,%20Asia,%20Africa,%20Australia,%20New%20Zealand,%20etc..%22%7Dss_call_number:D*&facet.query=%7B!key=%22History%20of%20the%20Americas%22%7Dss_call_number:(E*%20OR%20F*)&facet.query=%7B!key=%22Geography,%20Anthropology,%20and%20Recreation%22%7Dss_call_number:G*&facet.query=%7B!key=%22Social%20Sciences%22%7Dss_call_number:H*&facet.query=%7B!key=%22Political%20Science%22%7Dss_call_number:J*&facet.query=%7B!key=%22Law%22%7Dss_call_number:K*&facet.query=%7B!key=%22Education%22%7Dss_call_number:L*&facet.query=%7B!key=%22Music%22%7Dss_call_number:M*&facet.query=%7B!key=%22Fine%20Arts%22%7Dss_call_number:N*&facet.query=%7B!key=%22Language%20and%20Literature%22%7Dss_call_number:P*&facet.query=%7B!key=%22Science%22%7Dss_call_number:Q*&facet.query=%7B!key=%22Medicine%22%7Dss_call_number:R*&facet.query=%7B!key=%22Agriculture%22%7Dss_call_number:S*&facet.query=%7B!key=%22Technology%22%7Dss_call_number:T*&facet.query=%7B!key=%22Military%20Science%22%7Dss_call_number:U*&facet.query=%7B!key=%22Naval%20Science%22%7Dss_call_number:V*&facet.query=%7B!key=%22Bibliography,%20Library%20Science,%20and%20General%20Information%20Resources%22%7Dss_call_number:Z*",
      "dest" : "source/json/datasources/categoryQueryEn.json"
    },
    "categoryQueryAr" : {
      "src" : "http://stagediscovery.dlib.nyu.edu:8983/solr/viewer/select?wt=json&json.nl=arrmap&wt=json&json.nl=arrmap&q=sm_collection_code:aco&facet=true&rows=0&facet.query=%7B!key=%22%D8%A7%D9%84%D9%85%D8%B9%D8%A7%D8%B1%D9%81%20%D8%A7%D9%84%D8%B9%D8%A7%D9%85%D8%A9%22%7Dss_call_number:A*&facet.query=%7B!key=%22%D8%A7%D9%84%D9%81%D9%84%D8%B3%D9%81%D8%A9%20%D9%88%D8%B9%D9%84%D9%85%20%D8%A7%D9%84%D9%86%D9%81%D8%B3%20%D9%88%D8%A7%D9%84%D8%AF%D9%8A%D9%86%22%7Dss_call_number:B*&facet.query=%7B!key=%22%D8%A7%D9%84%D8%B9%D9%84%D9%88%D9%85%20%D8%A7%D9%84%D9%81%D8%B1%D8%B9%D9%8A%D8%A9%20%D9%84%D9%84%D8%AA%D8%A7%D8%B1%D9%8A%D8%AE%22%7Dss_call_number:C*&facet.query=%7B!key=%22%D8%AA%D8%A7%D8%B1%D9%8A%D8%AE%20%D8%A7%D9%84%D8%B9%D8%A7%D9%84%D9%85%20%D9%88%D8%AA%D8%A7%D8%B1%D9%8A%D8%AE%20%D8%A3%D9%88%D8%B1%D9%88%D8%A8%D8%A7%20%D9%88%D8%A2%D8%B3%D9%8A%D8%A7%20%D9%88%D8%A3%D9%81%D8%B1%D9%8A%D9%82%D9%8A%D8%A7%22%7Dss_call_number:D*&facet.query=%7B!key=%22%D8%AA%D8%A7%D8%B1%D9%8A%D8%AE%20%D8%A3%D9%85%D8%B1%D9%8A%D9%83%D8%A7%22%7Dss_call_number:(E*%20OR%20F*)&facet.query=%7B!key=%22%D8%A7%D9%84%D8%AC%D8%BA%D8%B1%D8%A7%D9%81%D9%8A%D8%A7%20%D9%88%D8%A7%D9%84%D8%A3%D9%86%D8%AB%D8%B1%D8%A8%D9%88%D9%84%D9%88%D8%AC%D9%8A%D8%A7%20%D9%88%D8%A7%D9%84%D8%AA%D8%B1%D9%81%D9%8A%D9%87%22%7Dss_call_number:G*&facet.query=%7B!key=%22%D8%A7%D9%84%D8%B9%D9%84%D9%88%D9%85%20%D8%A7%D9%84%D8%A7%D8%AC%D8%AA%D9%85%D8%A7%D8%B9%D9%8A%D8%A9%22%7Dss_call_number:H*&facet.query=%7B!key=%22%D8%A7%D9%84%D8%B9%D9%84%D9%88%D9%85%20%D8%A7%D9%84%D8%B3%D9%8A%D8%A7%D8%B3%D9%8A%D8%A9%22%7Dss_call_number:J*&facet.query=%7B!key=%22%D8%A7%D9%84%D9%82%D8%A7%D9%86%D9%88%D9%86%22%7Dss_call_number:K*&facet.query=%7B!key=%22%D8%A7%D9%84%D8%AA%D8%B9%D9%84%D9%8A%D9%85%22%7Dss_call_number:L*&facet.query=%7B!key=%22%D8%A7%D9%84%D9%85%D9%88%D8%B3%D9%8A%D9%82%D9%89%22%7Dss_call_number:M*&facet.query=%7B!key=%22%D8%A7%D9%84%D9%81%D9%86%D9%88%D9%86%20%D8%A7%D9%84%D8%AC%D9%85%D9%8A%D9%84%D8%A9%22%7Dss_call_number:N*&facet.query=%7B!key=%22%D8%A7%D9%84%D9%84%D8%BA%D8%A7%D8%AA%20%D9%88%D8%A7%D9%84%D8%A2%D8%AF%D8%A7%D8%A8%22%7Dss_call_number:P*&facet.query=%7B!key=%22%D8%A7%D9%84%D8%B9%D9%84%D9%88%D9%85%22%7Dss_call_number:Q*&facet.query=%7B!key=%22%D8%A7%D9%84%D8%B7%D8%A8%22%7Dss_call_number:R*&facet.query=%7B!key=%22%D8%A7%D9%84%D8%B2%D8%B1%D8%A7%D8%B9%D8%A9%22%7Dss_call_number:S*&facet.query=%7B!key=%22%D8%A7%D9%84%D8%AA%D9%83%D9%86%D9%88%D9%84%D9%88%D8%AC%D9%8A%D8%A7%22%7Dss_call_number:T*&facet.query=%7B!key=%22%D8%A7%D9%84%D8%B9%D9%84%D9%88%D9%85%20%D8%A7%D9%84%D8%B9%D8%B3%D9%83%D8%B1%D9%8A%D8%A9%22%7Dss_call_number:U*&facet.query=%7B!key=%22%D8%A7%D9%84%D8%B9%D9%84%D9%88%D9%85%20%D8%A7%D9%84%D8%A8%D8%AD%D8%B1%D9%8A%D8%A9%22%7Dss_call_number:V*&facet.query=%7B!key=%22%D8%A7%D9%84%D8%A8%D8%A8%D9%84%D9%8A%D9%88%D8%BA%D8%B1%D8%A7%D9%81%D9%8A%D8%A7%20%D8%8C%20%D9%88%D8%B9%D9%84%D9%88%D9%85%20%D8%A7%D9%84%D9%85%D9%83%D8%AA%D8%A8%D8%A7%D8%AA%20%D8%8C%20%D9%88%D8%A7%D9%84%D9%85%D8%B9%D9%84%D9%88%D9%85%D8%A7%D8%AA%20%D8%A7%D9%84%D8%B9%D8%A7%D9%85%D8%A9%22%7Dss_call_number:Z*",
      "dest" : "source/json/datasources/categoryQueryAr.json"
    }
    ```