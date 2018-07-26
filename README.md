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