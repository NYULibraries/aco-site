require('env2')('.env'); // optionally store your environment variables in .env
const seleniumServer = require("selenium-server");
const chromedriver = require("chromedriver");
const PKG = require('./package.json'); // so we can get the version of the project
const SCREENSHOT_PATH = "./node_modules/nightwatch/screenshots/" + PKG.version + "/";
const config = { // we use a nightwatch.conf.js file so we can include comments and helper functions
  "src_folders": [
    "test" // we use '/test' as the name of our test directory by default.
  ],
  "output_folder": "./node_modules/nightwatch/reports", // reports (test outcome) output by Nightwatch
  "selenium": {
    "start_process": true,
    "server_path": seleniumServer.path,
    "log_path": "",
    "host": "127.0.0.1",
    "port": 4444,
    "cli_args": {
      "webdriver.chrome.driver" : chromedriver.path
    }
  },
  "test_workers" : {"enabled" : true, "workers" : "auto"}, // perform tests in parallel where possible
  "test_settings": {
    "default": {
      "launch_url": "http://localhost",
      "selenium_port": 4444,
      "selenium_host": "127.0.0.1",
      "silent": true,
      "screenshots": {
        "enabled": true, // save screenshots taken here
        "path": SCREENSHOT_PATH
      }, // this allows us to control the
      "globals": {
        "waitForConditionTimeout": 15000 // on localhost sometimes internet is slow so wait...
      },
      "desiredCapabilities": {
        "browserName": "chrome",
        "chromeOptions": {
          "args": [
            `Mozilla/5.0 (iPhone; CPU iPhone OS 5_0 like Mac OS X) AppleWebKit/534.46
            (KHTML, like Gecko) Version/5.1 Mobile/9A334 Safari/7534.48.3`,
            "--window-size=640,1136" // iphone 5
          ]
        },
        "javascriptEnabled": true,
        "acceptSslCerts": true
      }
    },
    "chrome": { // your local Chrome browser (chromedriver)
      "desiredCapabilities": {
        "browserName": "chrome",
        "javascriptEnabled": true,
        "acceptSslCerts": true
      }
    }
  }
}
module.exports = config;

function padLeft (count) { // theregister.co.uk/2016/03/23/npm_left_pad_chaos/
  return count < 10 ? '0' + count : count.toString();
}

var FILECOUNT = 0; // "global" screenshot file count

/**
 * The default is to save screenshots to the root of your project even though
 * there is a screenshots path in the config object above! ... so we need a
 * function that returns the correct path for storing our screenshots.
 * While we're at it, we are adding some meta-data to the filename, specifically
 * the Platform/Browser where the test was run and the test (file) name.
 */
function imgpath (browser) {
  var a = browser.options.desiredCapabilities;
  var meta = [a.platform];
  meta.push(a.browserName ? a.browserName : 'any');
  meta.push(a.version ? a.version : 'any');
  meta.push(a.name); // this is the test filename so always exists.
  var metadata = meta.join('~').toLowerCase().replace(/ /g, '');
  return SCREENSHOT_PATH + metadata + '_' + padLeft(FILECOUNT++) + '_';
}

module.exports.imgpath = imgpath;

module.exports.SCREENSHOT_PATH = SCREENSHOT_PATH;
