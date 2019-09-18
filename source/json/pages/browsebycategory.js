'use strict';
module.exports = async function browsebycategory () {

  const appUrl = process.env.APP_URL;
  const grunt = require('grunt');
  const { resolve } = require('path');

  // takes a number (or string) and formats it to be a
  // number with commas. e.g., 1000 will become 1,000
  function toNumberWithCommas (numberWithoutCommas) {
    try {
      return numberWithoutCommas.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }
    catch (error) {
      grunt.fail.warn(error);
    }
  }

  // frontCount
  // This information is cache and save as {ROOT}/json/source/datasources/frontCountId.json
  // on build using grunt-curl.
  async function frontCountFromCache() {
    try {
      // Data and cache directory {ROOT}/json/source/datasources/*.json
      const datasourcesFilepath = resolve(__dirname, '../datasources/frontCountId.json');
      if (grunt.file.isFile(datasourcesFilepath)) {
        const frontCountData = await grunt.file.readJSON(datasourcesFilepath);
        return toNumberWithCommas(frontCountData.response.numFound);
      }
    }
    catch (error) {
      grunt.fail.warn(error);
    }
  }

  // Request categories by language
  // This information is cached as `{ROOT}/json/source/datasources/categoryQuery${language}.json`
  // on build using grunt-curl.
  async function categoriesFromCache(language) {
    const prefixLabel = {
      En: 'books',
      Ar: 'كتب'
    };
    try {
      // Data and cache directory {ROOT}/json/source/datasources/*.json
      const datasourcesFilepath = resolve(__dirname, `../datasources/categoryQuery${language}.json`);
      // check if datasource file exists
      if (grunt.file.isFile(datasourcesFilepath)) {
        const data = await grunt.file.readJSON(datasourcesFilepath);
        // Get Object keys and organize the data in a more meaningful way
        return Object.keys(data.facet_counts.facet_queries).map(category => {
          return {
            category: category,
            // build link
            uri: encodeURI(`${appUrl}/search?category=${category}&scope=matches`),
            // format count number e.g., 1,000 instead of 1000.
            count: toNumberWithCommas(data.facet_counts.facet_queries[category]),
            // label (en/ar) used to prefix the books count
            prefixLabel: prefixLabel[language],
          };
        });
      }
    }
    catch (error) {
      grunt.fail.warn(error);
    }
  }

  try {
    const frontCount = await frontCountFromCache();
    let categoriesEn = await categoriesFromCache('En');
    categoriesEn.unshift({ category: 'All', count: frontCount, uri: `${appUrl}/browse`});
    let categoriesAr = await categoriesFromCache('Ar');
    categoriesAr.unshift({ category: 'الجميع', count: frontCount, uri: `${appUrl}/browse`});

    return {
      htmltitle: 'Browse by Category',
      title: [
        {
          languageCode: 'en',
          languageDir: 'ltr',
          html: 'Browse by Category'
        },
        {
          languageCode: 'ar',
          languageDir: 'rtl',
          html: 'تصفح حسب فئة الموضوع'
        }
      ],
      menu: [
        {
          context: 'navbar',
          label: 'Browse by Category',
          weight: 4
        }
      ],
      route: '/browse-by-category/index.html',
      bodyClass: 'browse-by-category',
      content: {
        categories: [
          {
            text: 'ACO categories follow the  Library of Congress Classification system',
            languageCode: 'en',
            languageDir: 'ltr',
            cssClass: 'col-l',
            data: categoriesEn
          },
          {
            text: 'المجموعات العربية على الانترت تتبع نظام تصنيف مكتبة الكونغرس',
            languageCode: 'ar',
            languageDir: 'rtl',
            cssClass: 'col-r',
            data: categoriesAr
          }
        ]
      }
    };
  }
  catch (error) {
    grunt.fail.warn(error);
  }
};
