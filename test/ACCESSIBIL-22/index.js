module.exports = {
  'Meaningful titles for book pages': function (browser) {
    browser
      .url(`${process.env.APP_URL}/book/princeton_aco000380/3`)
      .waitForElementVisible('body', 2000)
      .assert.title('ʻUṣārat qalb: Arabic Collections Online')
      .end();
  }
};
