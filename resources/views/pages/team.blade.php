@extends('layouts.app')

@section('pagetitle', $pagetitle)

@section('body_class', $body_class)

@section('content')
    <main class="main container-fluid" role="main">

        <h2 class="page-title" lang="en" dir="ltr"><span>About the project team</span></h2>
        <h2 class="page-title" lang="ar" dir="rtl"><span>عن فريق المشروع</span></h2>
        <div class="col col-l" lang="en" dir="ltr">
            <h3 class="projectsponsors">Project Sponsors:</h3>
            <ul>
                <li>Virginia Danielson</li>
                <li>Carol Mandel</li>
                <li>David Millman</li>
                <li>Michael Stoller</li>
            </ul>
        </div>
        <div class="col col-r" lang="ar" dir="rtl">
            <h3 class="projectsponsors">رعاة المشروع</h3>
            <ul>
                <li> فيرجينيا دانيلسون</li>
                <li>كارول ماندِل</li>
                <li>ديفيد ميلمان</li>
                <li>مايكل ستولر</li>
            </ul>
        </div>
        <div class="col col-l" lang="en" dir="ltr">
            <h3>Project Manager:</h3>
            <ul>
                <li>Claudia Suleiman</li>
            </ul>
        </div>
        <div class="col col-r" lang="ar" dir="rtl">
            <h3 class="projectmanager">مدير المشروع</h3>
            <ul>
                <li>كلوديا سليمان </li>
            </ul>
        </div>
        <div class="col col-l" lang="en" dir="ltr">
            <h3>Project Team:</h3>
            <ul>
                <li>Adham Alok</li>
                <li>Guy Burak</li>
                <li>Melitte Buchman</li>
                <li>Heidi Frank</li>
                <li>Laura Henze</li>
                <li>Alysa Hornick</li>
                <li>Carol Kassel</li>
                <li>Alberto Ortiz Flores</li>
                <li>Justin Parrott</li>
                <li>Joseph Pawletko</li>
                <li>Ekaterina Pechekhonova</li>
                <li>Rasan Rasch</li>
                <li>Michael Stasiak</li>
                <li>Jonathan Ahrens</li>
                <li>Damon Chu</li>
            </ul>
        </div>
        <div class="col col-r" lang="ar" dir="rtl">
            <h3 class="projectteam">أفراد طاقم المشروع</h3>
            <ul>
                <li>أدهم العك</li>
                <li>جاي بوراك</li>
                <li>ميليت بكمان</li>
                <li>هايدي فرانك</li>
                <li>لورا هينز</li>
                <li>أليسا هورنيك</li>
                <li>كارول كاسيل</li>
                <li>البيرتواورتيزفلوريس</li>
                <li class="jp">جوستين پاروت</li>
                <li>جوزيف باولتيكو</li>
                <li>ايكتارينا بيكانوفا</li>
                <li>رازان راسش</li>
                <li>مايكل ستاسيك</li>
            </ul>
        </div>
        <div class="col col-l" lang="en" dir="ltr">
            <p class="special-thanks">Special thanks to Nadaleen Templeman-Kluit, Houda El-Mimouni, and others in the UX
                department at NYU Libraries for their guidance.</p>
        </div>
        <div class="col col-r" lang="ar" dir="rtl">
            <p class="special-thanks">شكر خاص لمساعدة نادالين تمبلمان-كلوت، هدى الميموني وغيرهم من فريق ' تجربة المستخدم
                ' في مكتبات جامعة نيويورك.</p>
        </div>


    </main>
@endsection
