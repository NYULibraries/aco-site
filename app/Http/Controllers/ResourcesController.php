<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ResourcesController extends Controller
{
    public function index()
    {

      $content = [];

      $content['resources'] = [
        'en' => [
          'language' => [
            'class' => 'col col-l',
            'lang' => 'en',
            'dir' => 'ltr',
          ],
          'links' => [
            [
              'href' => 'https://corpus.quran.com/',
              'label' => 'The Quranic Arabic Corpus',
            ],
            [
              'href' => 'https://www.altafsir.com/',
              'label' => 'Altafsir',
            ],
            [
              'href' => 'https://waqfeya.net/',
              'label' => 'Almaktaba alwaqfiyya',
            ],
            [
              'href' => 'https://www.nlm.nih.gov/hmd/arabic/welcome.html',
              'label' => 'Islamic Medical Manuscripts at the National Library of Medicine',
             ],
            [
              'href' => 'https://shamela.ws/',
              'label' => 'Almaktaba alshamila',
            ],
            [
              'href' => 'https://www.alwaraq.net/',
              'label' => 'Alwaraq',
            ],
            [
              'href' => 'https://guides.nyu.edu/mideast',
              'label' => 'The Middle Eastern and Islamic Studies Collection at NYU',
            ],
            [
              'href' => 'https://libguides.princeton.edu/c.php?g=1258622',
              'label' => 'The Near East Collections at Princeton',
            ],
            [
              'href' => 'https://www.library.cornell.edu/colldev/mideast',
              'label' => 'The Middle East and Islamic Studies Collection at Cornell',
            ],
            [
              'href' => 'https://library.columbia.edu/locations/global/mideast.html',
              'label' => 'The Middle East and Islamic Studies Collection at Columbia',
            ],
            [
              'href' => 'https://www.aub.edu.lb/libraries/Pages/default.aspx',
              'label' => 'AUB (American University of Beirut) Libraries',
            ],
            [
              'href' => 'https://www.nla.ae/en/',
              'label' => 'United Arab Emirates National Library and Archives',
            ],
            [
              'href' => 'https://ocp.hul.harvard.edu/ihp/',
              'label' => 'Islamic Heritage project',
            ],
            [
              'href' => 'https://www.familysearch.org/en/library/MENA/',
              'label' => 'FamilySearch: Arabic Genealogy Books',
            ],
          ],
        ],
        'ar' => [
          'language' => [
            'class' => 'col col-r',
            'lang' => 'ar',
            'dir' => 'rtl',
          ],
          'links' => [
            [
              'href' => 'https://corpus.quran.com/',
              'label' => 'مورد لغوي يشرح قواعد اللغة العربية والنحو والصرف لكل كلمة في القرآن',
            ],
            [
              'href' => 'https://www.altafsir.com/',
              'label' => 'تعليقات عربية وانجليزية من وجهات نظر مختلفة',
            ],
            [
              'href' => 'https://waqfeya.net/',
              'label' => 'مكتبة من الملفات العربية الممسوحة ضوئيا',
            ],
            [
              'href' => 'https://www.nlm.nih.gov/hmd/arabic/welcome.html',
              'label' => 'الطب الإسلامي والعلوم في العصور الوسطى والدور الهام الذي لعبته في تاريخ أوروبا',
            ],
            [
              'href' => 'https://shamela.ws/',
              'label' => 'مكتبة من النصوص العربية للتنزيل',
            ],
            [
              'href' => 'https://www.alwaraq.net/',
              'label' => 'بحث في النصوص العربية الفصحى',
            ],
            [
              'href' => 'https://guides.nyu.edu/mideast',
              'label' => 'مجموعة دراسات الشرق الاوسط الاسلامية في جامعة نيويورك',
            ],
            [
              'href' => 'https://libguides.princeton.edu/c.php?g=1258622',
              'label' => 'مجموعة الشرق الأدنى في جامعة برنستون',
            ],
            [
              'href' => 'https://www.library.cornell.edu/colldev/mideast',
              'label' => 'الشرق الأوسط ومجموعة الدراسات الأسلامية في جامعة كورنيل',
            ],
            [
              'href' => 'https://library.columbia.edu/locations/global/mideast.html',
              'label' => 'الشرق الأوسط و مجموعة الدراسات الأسلامية في جامعة كولومبيا',
            ],
            [
              'href' => 'https://www.aub.edu.lb/libraries/Pages/default.aspx',
              'label' => 'مكتبات الجامعة الأمريكية في لبنان',
            ],
            [
              'href' => 'https://www.nla.ae/',
              'label' => 'الأرشيف والمكتبة الوطنية بدولة الإمارات العربية المتحدة',
            ],
            [
              'href' => 'https://ocp.hul.harvard.edu/ihp/',
              'label' => 'التراث الاسلامي',
            ],
            [
              'href' => 'https://www.familysearch.org/en/library/MENA/',
              'label' => 'المجموعات العربية على الانترنيت: دراسة أنساب الكتب',
            ],
          ]
        ]
      ];

      $data = [
          'pagetitle' => 'Other Resources',
          'body_class' => 'page resources',
          'title' => [
            'en' => [
              'label' => 'Other Resources',
              'language' => [
                'code' => 'en',
                'dir' => 'ltr',
                ],
              ],
            'ar' => [
              'label' => 'المواقع الأخرى',
              'language' => [
                'code' => 'ar',
                'dir' => 'rtl',
                ],
              ],
            ],
          'content' => $content,
      ];

      return view('pages.resources', $data);

    }
}
