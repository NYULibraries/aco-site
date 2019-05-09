module.exports = exports = {
  "browsebycategory": {
    "htmltitle": "Browse by Category",
    "title": [{
      "language_code": "en",
      "language_dir": "ltr",
      "html": "Browse by Category"
    }, {
      "language_code": "ar",
      "language_dir": "rtl",
      "html": "Capicola burgdoggen"
    }],
    "menu": [{
      "context": "navbar",
      "label": "Browse by Category",
      "weight": 4
    }],
    "route": "/browse-by-category/index.html",
    "bodyClass": "browse-by-category",
    "content": {
      "categoryEn": [{
        "text": "Pancetta pork chop hamburger short loin ribeye meatloaf picanha.",
        "language": "en",
        "dir": "ltr",
        "id": "categoryQueryEn",
        "widgets": ["categoryQueryEn"]
      }],
      "categoryAr": [{
        "text": "Venison bacon meatloaf ribeye, alcatra jowl turkey salami pastrami.",
        "language": "ar",
        "dir": "rtl",
        "id": "categoryQueryAr",
        "widgets": ["categoryQueryAr"]
      }],
      "frontCount": [
        {
          "id" : "frontCountId",
           "widgets": ["frontCountId"]
        }
      ]
    }
  }
};
