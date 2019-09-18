function json (context, options) {
  return options.fn(JSON.parse(context));
}

function speakingurl (context, options) {
  const getSlug = require('speakingurl');
  return getSlug(this.label);
}

function addcommas (string, options) {
  x = string.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
  return x;
}

function ifempty (fieldtocheck, defaultvalue) {
  if (fieldtocheck) {
    return;
  } else {
    return defaultvalue;
  }
}

module.exports = {
  ifempty: ifempty,
  addcommas: addcommas,
  json: json,
  speakingurl: speakingurl
};