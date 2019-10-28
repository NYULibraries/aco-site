async function notfound () {
  return {
    "htmltitle": "Page Not Found",
    "title": [{
    "language_code": "en",
    "language_dir": "ltr",
    "html": "Page Not Found"
  }, {
    "language_code": "ar",
    "language_dir": "rtl",
    "html": "&nbsp;"
  }],
  "route": "/404.html",
  "bodyClass": "notfound",
  "content": {
    "main": [{
      "language_code": "en",
      "class": "col-l",
      "language_dir": "ltr",
      "html": "<p>Sorry, you have requested a page or file that does not exist or has moved.</p><ul><li>Start from the beginning; <a href=\"/aco/\">visit the homepage</a></li><li>Utilize the search tool</li><li>Contact us at <a href=\"mailto:ACO-contact-group@nyu.edu\">ACO-contact-group@nyu.edu</a></li><ul></p>"
    }, {
      "language_code": "ar",
      "class": "col-r",
      "language_dir": "rtl",
      "html": "<p>&nbsp;</p>"
    }]
  }
  }
}

module.exports = notfound;
