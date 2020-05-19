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

// Taken from https://stackoverflow.com/questions/10420352/converting-file-size-in-bytes-to-human-readable-string#14919494
function humanFileSize(bytes, si) {
  var thresh = si ? 1000 : 1024;
  if(Math.abs(bytes) < thresh) {
      return bytes + ' B';
  }
  var units = si
      ? ['kB','MB','GB','TB','PB','EB','ZB','YB']
      : ['KiB','MiB','GiB','TiB','PiB','EiB','ZiB','YiB'];
  var u = -1;
  do {
      bytes /= thresh;
      ++u;
  } while(Math.abs(bytes) >= thresh && u < units.length - 1);
  return bytes.toFixed(1) + ' ' + units[u];
}

module.exports = {
  ifempty: ifempty,
  addcommas: addcommas,
  json: json,
  speakingurl: speakingurl,
  humanFileSize: humanFileSize
};