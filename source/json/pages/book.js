async function book () {
  const sourceUrl = process.env.VIEWER_SOURCE_URL;
  return {
    "htmltitle": "Book",
    "title": "Book",
    "bodyClass": "book",
    "sourceUrlDev": sourceUrl,
    "sourceUrl": sourceUrl,
    "route": "/book/index.html"
  };
}

module.exports = book;
