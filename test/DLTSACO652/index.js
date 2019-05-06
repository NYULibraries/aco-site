const conf = require('../../nightwatch.conf.js');
const path = require('path');

module.exports = {

  'Browse by Category exists' : function (browser) {
    browser
      .url(path.join(process.env.APP_URL, 'browse-by-category'))
      .waitForElementVisible('body', 1000)
      .assert.title('Arabic Collections Online: Browse by Category')
      .saveScreenshot(conf.imgpath(browser) + 'DLTSACO652-01.png')
      .end();
  },

  'Homepage items include "Category" as part of their metadata' : function (browser) {
    browser.url(process.env.APP_URL).waitForElementVisible('body', 1000)
    browser.expect.element('.md_category').to.be.present;
    browser.end();
  },

  'Homepage items include "Call number" as part of their metadata' : function (browser) {
    browser.url(process.env.APP_URL).waitForElementVisible('body', 1000)
    browser.expect.element('.md_call_number').to.be.present;
    browser.end();
  },

  'Seach page items include "Category" as part of their metadata' : function (browser) {
    browser.url(`${process.env.APP_URL}/search/?category=General%20Works&scope=matches`).waitForElementVisible('body', 1000)
    browser.expect.element('.md_call_number').to.be.present;
    browser.end();
  },

  'Seach page items include "Call number" as part of their metadata' : function (browser) {
    browser.url(`${process.env.APP_URL}/search/?category=General%20Works&scope=matches`).waitForElementVisible('body', 1000)
    browser.expect.element('.md_call_number').to.be.present;
    browser.end();
  },

  'Filter by category "General Works" update search form' : function (browser) {
    browser.url(`${process.env.APP_URL}/search/?category=General%20Works&scope=matches`).waitForElementVisible('body', 1000)
    browser.expect.element('.q1').to.be.present;
    browser.expect.element('.q1').to.have.value.that.equals('General Works');
    browser.expect.element('.field-select').to.have.value.that.equals('category');
    browser.end();
  }



};
