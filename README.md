Arabic Collections Online site
========

Setup

Make sure you have nodejs.org and gruntjs.com

Installation

```bash
$ git clone https://github.com/dismorfo/aco-site.git
```

Install dependencies

```bash
$ cd aco-site && npm install
```

Getting started
  
Adding a page
  
All the pages are represented in the configuration file (cong.json). To add a new page include it in the pages object.     title and route are require (note that the route is relative to the appRoot". In this file you also declare mustache       variables.
  
```javascript
"myNewPage" : {
        "title" : "My new page"
      , "route" : "/mynewpage/index.html"
}
```  

Make changes

All the changes are made to the source files ./source. Depending in your server configuration
you will need to include the .htaccess file in each directory or make modifications to the one
inside build directory.

Build site

```bash
$ grunt
```
  If you want to watch the source files

```bash
$ grunt watch
```
