function HandlebarsHelpers() {

  function json(context, options) {
    return options.fn(JSON.parse(context));
  }

  function speakingurl() {
    return window.getSlug(this.label);
  }

  function ifempty(fieldtocheck, defaultvalue) {
    if (fieldtocheck) {
      return;
    } else {
      return defaultvalue;
    }
  }

  function hasFileSize(bytes) {
    return parseInt(bytes, 10) > 0 ? true : false;
  }

  function humanFileSize(bytes) {
    var thresh = 1000;
    if (Math.abs(bytes) < thresh) {
      return bytes + ' B';
    }
    var units = ['kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
    var u = -1;
    do {
      bytes /= thresh;
      ++u;
    } while (Math.abs(bytes) >= thresh && u < units.length - 1);
      return bytes.toFixed(1) + ' ' + units[u];
    }

    return {
      ifempty: ifempty,
      json: json,
      speakingurl: speakingurl,
      humanFileSize: humanFileSize,
      hasFileSize: hasFileSize
     };
   
   }