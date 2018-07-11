const conf = require('../../nightwatch.conf.js');
const path = require('path');

module.exports = {

  'Meaningful titles for book pages' : function (browser) {
      browser
        .url(path.join(process.env.APP_URL, 'book/princeton_aco000380/3'))
        .waitForElementVisible('body', 2000)
        .assert.title('ʻUṣārat qalb: Arabic Collections Online')
        .saveScreenshot(conf.imgpath(browser) + 'ACCESSIBIL-22.png')
        .end();
  }

};
