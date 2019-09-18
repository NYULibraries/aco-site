async function front () {

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

  // subjectCount
  // This information is cache and save as {ROOT}/json/source/datasources/subjectCount.json
  // on build using grunt-curl.
  async function subjectCountFromCache() {
    // Data and cache directory {ROOT}/json/source/datasources/*.json
    const datasourcesFilepath = resolve(__dirname, '../datasources/subjectCount.json');
    if (grunt.file.isFile(datasourcesFilepath)) {
      const subjectCountData = await grunt.file.readJSON(datasourcesFilepath);
      return toNumberWithCommas(subjectCountData.facet_counts.facet_fields.im_field_subject.length + 1);
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

  try {

    const frontCount = await frontCountFromCache();
    const subjectCount = await subjectCountFromCache();

    const content = {
      main: [
        {
          languageCode: 'en',
          languageDir: 'ltr',
          cssClass: 'col-l',
          text: `<b>Arabic Collections Online</b> (ACO) is a publicly available digital library of public domain Arabic language
                 content. ACO currently provides digital access to <b>${frontCount}</b> volumes across
                 <b>${subjectCount}</b> subjects drawn from rich Arabic collections of distinguished research libraries.
                 Established with support from NYU Abu Dhabi, and currently supported by major grants from Arcadia, a charitable
                 fund of Lisbet Rausing and Peter Baldwin, and Carnegie Corporation of New York, this mass digitization project
                 aims to feature up to 23,000 volumes from the library collections of NYU and partner institutions.&nbsp;
                 <a href="${appUrl}/about/" aria-label="read more about Arabic Collections Online" class="readmore">READ&nbsp;MORE…</a>`
        },
        {
          languageCode: 'ar',
          languageDir: 'rtl',
          cssClass: 'col-r',
          text: `<b>المجموعات العربية على الانترنِت</b> هي عبارة عن مكتبة عامة رقميَّة للكتب المؤلَّفة باللغة العربية والتي
                 أصبحت في المجال العام. حالياً، هذا المشروع يوفّر إمكانيّة الولوج الإلكتروني إلى <b>${frontCount}</b>
                 كتاباً في اكثر من <b>${subjectCount}</b> موضوعاً مُستَمداً من مجموعات قيّمة في مكتبات مميَّزة. تأسست بدعم
                 من جامعة نيويورك أبوظبي وتدعمها حاليًا المنح الكبرى من أركاديا ، وهي صندوق خيري لشركة ليسبت راوزينج وبيتر
                 بالدوين ، وشركة كارنيجي في نيويورك. يهدف مشروع الرقمنة هذا إلى عرض ما يصل إلى 23,000 مجلد من مجموعات مكتبة جامعة
                 نيويورك والمؤسسات الشريكة.&nbsp;
                 <a aria-label="read more about Arabic Collections Online" href="${appUrl}/about/" class="readmore">المزيد...</a>`
        }
      ],
      featured: [
        {
          id: "featuredTitlesAll",
          widgets: ["featuredTitlesAll"]
        }
      ]
    }

    return {
      title: [ { "html": "Browse Home" } ],
      menu: [
        {
          context: "navbar",
          label: "Home",
          weight: 0
        }
      ],
      route: '/index.html',
      bodyClass: 'front',
      content: content
    }
  }
  catch (error) {
    grunt.fail.warn(error);
  }
}

module.exports = front;
