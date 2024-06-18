async function about () {
  const grunt = require('grunt');
  const { resolve } = require('path');

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
    // Data and cache directory {ROOT}/json/source/datasources/*.json
    const datasourcesFilepath = resolve(__dirname, '../datasources/frontCountId.json');
    if (grunt.file.isFile(datasourcesFilepath)) {
      const frontCountData = await grunt.file.readJSON(datasourcesFilepath);
      return toNumberWithCommas(frontCountData.response.numFound);

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

  let frontCount = await frontCountFromCache();
  let subjectCount = await subjectCountFromCache();

  const content = {
          about: [
            {
              languageClass: 'col-l',
              languageDir: 'ltr',
              languageCode: 'en',
              label: 'What is Arabic Collections Online?',
              body: `<b>Arabic Collections Online </b> (ACO) is a publicly available digital
                     library of public domain Arabic language content. ACO currently provides
                     digital access to <b>${frontCount}</b> volumes across <b>${subjectCount}</b>
                     subjects drawn from rich Arabic collections of distinguished research libraries.
                     Established with support from NYU Abu Dhabi, and currently supported by major
                     grants from Arcadia, a charitable fund of Lisbet Rausing and Peter Baldwin,
                     and Carnegie Corporation of New York, this mass digitization project aims to
                     feature up to 23,000 volumes from the library collections of NYU and partner
                     institutions. These institutions are contributing published books in all
                     fields—literature, business, science, and more—from their Arabic
                     language collections.`
            },
            {
              languageClass: 'col-r',
              languageDir: 'rtl',
              languageCode: 'ar',
              label: 'ما هي المجموعات العربية على الانترنت؟',
              body: `<b>المجموعات العربية على الانترنِت</b> هي عبارة عن مكتبة عامة رقميَّة للكتب المؤلَّفة باللغة العربية
              والتي أصبحت في المجال العام. حالياً، هذا المشروع يوفّر إمكانيّة الولوج الإلكتروني إلى
              <b>${frontCount}</b> كتاباً في اكثر من <b>${subjectCount}</b> موضوعاً مُستَمداً من مجموعات
              قيّمة في مكتبات مميَّزة. تأسست بدعم من جامعة نيويورك أبوظبي وتدعمها حاليًا المنح الكبرى من أركاديا ، وهي
              صندوق خيري لشركة ليسبت راوزينج وبيتر بالدوين ، وشركة كارنيجي في نيويورك. يهدف مشروع الرقمنة هذا إلى عرض
              ما يصل إلى 23,000 مجلد من مجموعات مكتبة جامعة نيويورك والمؤسسات الشريكة. إن هذه المؤسسات تساهم في تقديم
              كتب منشورة في مختلف مجالات الأدب، والأعمال، والعلوم، وغيرها من مقتنياتها من المجموعات العربية.`
            },
          ]
    };

    return {
        "htmltitle": "About",
        "title": [{
            "languageCode": "en",
            "languageDir": "ltr",
            "html": "About"
        }, {
            "languageCode": "ar",
            "languageDir": "rtl",
            "html": "عن هذا المشروع"
        }],
        "menu": [{
            "context": "navbar",
            "label": "About",
            "weight": 1
        }],
        "route": "/about/index.html",
        "bodyClass": "page about",
        "content": {
            about: content.about,
            "main": [
            {
                "language_code": "en",
                "class": "col-l",
                "language_dir": "ltr",
                "html": "<h3>What is the purpose of the project?</h3><p>ACO aims to digitize, preserve, and provide free open access to a wide variety of Arabic language books in subjects such as literature, philosophy, law, religion, and more. Important Arabic language content is not widely available on the web, and ACO aims to ensure global access to a rich Arabic library collection. Many older Arabic books are out-of-print, in fragile condition, and are otherwise rare materials that are in danger of being lost. ACO will ensure that this content will be saved digitally for future generations.</p>"
            }, {
                "language_code": "ar",
                "class": "col-r",
                "language_dir": "rtl",
                "html": "<h3>ما هو هدف هذا المشروع؟ </h3><p>يهدف مشروع المجموعات العربية على الإنترنت إلى رقمنة وحفظ وتوفير الولوج المفتوح لمجموعة واسعة من كتب في اللغة العربية في مواضيع الأدب والفلسفة والقانون والدين وغيرها. حالياً، العديد من المحتويات المهمّة في اللغة العربية ليست متوفِّرة على نطاق واسع على الإنترنت. مشروع المجموعات العربية على الإنترنت يضمن الولوج العالمي لمكتبة رقميّة غنيّة بمجموعتها العربية. العديد من الكتب العربية القديمة النافذة من الطباعة، وسهلة التفتت، أصبحت نادرة ومُعرّ ضة لخطر الضياع. مشروع المجموعات العربية على الإنترنت يضمن حِفظ هذه الكتب رقمياًّ لأجيال المستقبل. </p>"
            }, {
                "language_code": "en",
                "class": "col-l",
                "language_dir": "ltr",
                "sequence": "3",
                "html": "<h3>Which institutions are involved in the project?</h3><p>ACO contributing partners are <a href=\"http://www.nyu.edu/\">New York University</a>, <a href=\"https://www.princeton.edu/main/\">Princeton</a>, <a href=\"https://www.cornell.edu/\">Cornell</a>, <a href=\"http://www.columbia.edu/\">Columbia</a>, <a href=\"http://www.aucegypt.edu/\">American University in Cairo</a>, <a href=\"http://www.aub.edu.lb/main/Pages/index.aspx\">American University of Beirut</a>, <a href=\"https://www.nla.ae/en/\">United Arab Emirates National Library and Archives</a> and <a href=\"https://www.qnl.qa/en\">Qatar National Library</a>.</p>"
            }, {
                "language_code": "ar",
                "class": "col-r",
                "language_dir": "rtl",
                "sequence": "3",
                "html": "<h3>ما هي المؤسسات المشاركة في المشروع؟</h3><p> المساهمون المشاركون في المشروع: <a href=\"http://www.nyu.edu/\">جامعة نيويورك</a>، <a href=\"https://www.princeton.edu/main/\">جامعة پرنستون</a>، <a href=\"https://www.cornell.edu/\">جامعة كورنيل</a>،<a href=\"http://www.columbia.edu/\"> جامعة كولومبيا</a>، <a href=\"http://www.aucegypt.edu/\">الجامعة الأمريكية بالقاهرة</a>، <a href=\"http://www.aub.edu.lb/main/Pages/index.aspx\">الجامعة الأمريكية في بيروت</a>، <a href=\"https://www.nla.ae\">الأرشيف والمكتبة الوطنية بدولة الإمارات العربية المتحدة</a> و <a href=\"https://www.qnl.qa/ar\"> مكتبة قطر الوطنية</a>.</p>"
            }, {
                "language_code": "en",
                "class": "col-l",
                "language_dir": "ltr",
                "sequence": "4",
                "html": "<h3>Who does the project serve?</h3><p>ACO can be used by anyone, anywhere in the world, who is interested in Arabic language works. Specifically, the target audience is for students, scholars, academics, researchers, librarians, and general interest readers.</p>"
            }, {
                "language_code": "ar",
                "class": "col-r",
                "language_dir": "rtl",
                "sequence": "4",
                "html": "<h3>من يخدم هذا المشروع؟</h3><p>باستطاعة أي شخص مهتم  بأعمال في اللغة العربية، أن يستخدم الموقع الإلكتروني للمجموعات العربية على الإنترنت من أي مكان في العالم.  على وجه التحديد، الجمهور المستهدَف هو الطلاب، العلماء، الأكاديميين، الباحثين، أمناء المكتبات، والقراء.</p>"
            }, {
                "language_code": "en",
                "class": "col-l",
                "language_dir": "ltr",
                "sequence": "5",
                "html": "<h3>What are the standards used for digitization?</h3><p>All digital imaging meets the <a href=\"http://www.digitizationguidelines.gov\">Federal Agencies Digital Guidelines Initiative</a> (FADGI), which was developed with wide review and consensus by the cultural heritage community’s digital experts. Image master files must be produced as TIFF-6, uncompressed, sRGB- or RGB-embedded, 24-bit color (3-channels of 8-bit color), 400 ppi for all items.</p>"
            }, {
                "language_code": "ar",
                "class": "col-r",
                "language_dir": "rtl",
                "sequence": "5",
                "html": "<h3>ما هي المواصفات المستخدمة في الرقمنة؟</h3><p>التصوير الرقمي يتوافق مع مبادرة من وكالات فيدرالية لارشادات في حقل الرقمنة (<a href=\"http://www.digitizationguidelines.gov\">FADGI</a>) التي تم تطويرها بمراجعة واسعة وتوافق آراء من قبل خبراء الرقمنة في مجتمع التراث الثقافي.  يجب انتاج ملفات الصور الرئيسية في هيئة تِف-٦، غير مضغوط، أو مدموج إس-آر-جي-بي، أو مدموج آر-جي-بي،٢٤-بِت ألوان (٣ قنوات من٨-بِت لون)، ٤٠٠پي-پي-آي لجميع الصور.</p>"
            }, {
                "language_code": "en",
                "class": "col-l",
                "language_dir": "ltr",
                "sequence": "6",
                "html": "<h3>How are items selected for digitization?</h3><p>All out-of-copyright books from NYU and partner institutions are selected for ACO. These titles, in turn, have been collected over centuries by subject specialists at each respective institution for their academic quality and relevance to intellectual and literary inquiry. </p>"
            }, {
                "language_code": "ar",
                "class": "col-r",
                "language_dir": "rtl",
                "sequence": "6",
                "html": "<h3>كيف تم  اختيار العناوين للرقمنة؟</h3><p>جميع العناوين المختارة لهذا المشروع، من جامعة نيويورك وشركائها، هي خارجة عن حقوق الطبع والنشر.  تم جمع هذه العناوين  عبر العديد من القرون من قبل متخصصين من كل جامعة مشاركة لجودتها الأكاديمية وأهميتها في التحقيق الفكري والأدبي.</p>"
            }, {
                "language_code": "en",
                "class": "col-l",
                "language_dir": "ltr",
                "sequence": "7",
                "html": "<h3>What are ACO’s copyright guidelines?</h3><p>NYU has researched copyright requirements and restrictions for each of the countries of publication and believes the materials displayed on this site are all in the public domain. However, if you believe that you are the copyright owner of any material displayed here, please see our <a href=\"http://dlib.nyu.edu/aco/takedownpolicy/\">takedown policy</a>.</p>"
            }, {
                "language_code": "ar",
                "class": "col-r",
                "language_dir": "rtl",
                "sequence": "7",
                "html": "<h3>ما هي  إرشادات حقوق الطبع والنشر لهذا المشروع ؟</h3><p>لقد بحثت جامعة نيويورك متطلبات حقوق التأليف والنشر، والقيود المفروضة على كل من بلدان النشر. ونعتقد أن جميع المواد التي يتم عرضها على هذا الموقع هي في المجال العام. ومع ذلك، إذا كنت تعتقد أنك صاحب حقوق الطبع والنشر لأي من المواد المعروضة هنا، يرجى الاطلاع على <a href=\"http://dlib.nyu.edu/aco/takedownpolicy/\">سياسة إنهاء الخدمة لدينا</a>.</p>"
            }, {
                "language_code": "en",
                "class": "col-l",
                "language_dir": "ltr",
                "sequence": "8",
                "html": "<h3>Is ACO metadata available?</h3><p>MARC metadata records for all ACO content are available to the public at:<br> <a target=\"_blank\" class=\"ext\" href= \"https://github.com/NYULibraries/aco-karms\">https://github.com/NYULibraries/aco-karms</a>.</p>"
            }, {
                "language_code": "ar",
                "class": "col-r",
                "language_dir": "rtl",
                "sequence": "8",
                "html": "<h3>هل البيانات الوصفية متاحة ؟</h3><p>تتوفر سجلات البيانات الوصفية لجميع محتويات المجموعات العربية على الانترنت للجمهور على العنوان التالي: <br><a target=\"_blank\" class=\"ext\" href= \"https://github.com/NYULibraries/aco-karms\">https://github.com/NYULibraries/aco-karms</a></p>"
            }
            , {
                "language_code": "en",
                "class": "col-l",
                "language_dir": "ltr",
                "sequence": "8",
                "html": "<h3>Have more questions?</h3><p>For more information, please contact us at: <a class=\"email-link\" href=\"mailto:aco-support@nyu.edu\">aco-support@nyu.edu</a></p>"
            }, {
                "language_code": "ar",
                "class": "col-r",
                "language_dir": "rtl",
                "sequence": "8",
                "html": "<h3>هل لديك المزيد من الأسئلة؟</h3><p>لمزيد من المعلومات، يرجى الاتصال بنا على العنوان التالي: <a class=\"email-link\" href=\"mailto:aco-support@nyu.edu\">aco-support@nyu.edu</a></p>"
            }, {
                "language_code": "en",
                "class": "col-l",
                "language_dir": "ltr",
                "sequence": "9",
                "html": "<h3>ACO Advisory Board</h3><ul><li>Marilyn Booth, Khalid bin Abdullah Al Saud Professor of the Contemporary Arab World, Magdalen College, Oxford University </li><li>Virginia Danielson, Former Director of the NYUAD Library (retired)</li><li>Roberta Dougherty, Librarian for Middle East Studies, Yale University</li><li>Beshara Doumani, Joukowsky Family Professor of Modern Middle East History, Brown University</li><li>James L. Gelvin, Professor of History, UCLA</li><li>David Hirsch, Advisor to Mohammed bin Rashid Library, Dubai</li><li>Charles Kurzman, Professor of Sociology, University of North Carolina</li><li>Zachary Lockman, Professor of Middle Eastern and Islamic Studies and History, New York University (ex officio)</li><li>Carol Mandel, Dean Emerita of the NYU Division of Libraries</li></ul>"
            }, {
                "language_code": "ar",
                "class": "col-r",
                "language_dir": "rtl",
                "sequence": "9",
                "html": "<h3>المجلس الاستشاري للموقع</h3><ul><li>مارِلين بوث، أستاذ في قسم خالد بن عبداللّه آل سعود للعالم العربي المعاصر، كليّة ماچدالِن، جامعة أكسفورد</li><li>فيرجينيا دانيلسون ، المدير السابق لمكتبة نيويورك أبو ظبي (متقاعدة)</li><li>روبرتا دُوِرتي، أمين مكتبة دراسات الشرق الأوسط، جامعة ييل</li><li>بشارة دوماني، أستاذ  تاريخ الشرق الأوسط المعاصر في كلّية عائلة جوكَوسكي، جامعة براون</li><li>جيمس ل.چِلڤن، أستاذ التاريخ، جامعة كاليفورنيا</li><li>ديفيد هيرش ، مستشار مكتبة محمد بن راشد ، دبي</li><li>تشارلز كُرزمَن، أستاذ علم الاجتماع، جامعة نورث كارولَينا</li><li>زاكَري لكْمَن، أستاذ دراسات الشرق الأوسط ودراسات وتاريخ الإسلام (بصفة رسمية)، جامعة نيويورك</li><li>كارول مانديل ، العميد السابق لمكتبات جامعة نيويورك</li></ul>"
            }, {
                "language_code": "en",
                "class": "col-l",
                "language_dir": "ltr",
                "sequence": "10",
                "html": "<h3>Project Management</h3><p>ACO is managed by the libraries of NYU Abu Dhabi and NYU New York. ACO technical operations and this site are managed by NYU Libraries Digital Library Technology Services. <a href=\"/aco/team\">Read more about the project team.</a></p>"
            }, {
                "language_code": "ar",
                "class": "col-r",
                "language_dir": "rtl",
                "sequence": "10",
                "html": "<h3>إدارة المشروع</h3><p>يدير مشروع المجموعات العربية على الإنترنت جامعة نيويورك في أبوظبي وجامعة نيويورك في نيويورك . اما افراد طاقم المشروع المسؤولون عن انشاء هذا الموقع الإلكتروني، فهم أعضاء فريق الخدمات التكنولوجية للمكتبات الرقمية في مكتبات جامعة نيويورك. <a href=\"/aco/team\">للمزيد عن فريق المشروع</a></p>"
            },
            {
                "language_code": "en",
                "class": "col-l fontmaker",
                "language_dir": "ltr",
                "sequence": "10",
                "html": "<p>This site uses the <a href=\"http://www.amirifont.org/\">Amiri Arabic font</a>, designed by Dr. Khaled Hosny and distributed under <a href=\"http://scripts.sil.org/OFL\">Open Font License</a>.</p>"
            }, {
                "language_code": "ar",
                "class": "col-r fontmaker",
                "language_dir": "rtl",
                "sequence": "10",
                "html": "<p>يستخدم هذا الموقع الخط <a href=\"http://www.amirifont.org/\">العربي</a> الأميري الذي صممه الدكتور خالد حسني. يوزع الخط الأميري تحت <a href=\"http://scripts.sil.org/OFL\">رخصة الخطوط المفتوحة</a>.</p>"
            }
          ]
        }
    }
};

module.exports = about;
