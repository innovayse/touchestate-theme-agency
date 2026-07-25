<?php $page = 'faq'; ?>
@section('title')
    FAQ
@endsection

@extends('layout.mainlayout')
@section('content')

    @php
        // Contact block — messengers come as raw numbers (see footer pattern)
        $faqWa    = !empty($workspace['messengers']['whatsApp']) ? preg_replace('/\D+/', '', $workspace['messengers']['whatsApp']) : null;
        $faqViber = !empty($workspace['messengers']['viber'])    ? preg_replace('/\D+/', '', $workspace['messengers']['viber'])    : null;
        $faqTel   = !empty($workspace['phone']) ? preg_replace('/[^\d+]/', '', $workspace['phone']) : null;
    @endphp

    <!-- ========================
        Start Page Content
    ========================= -->

    <div class="page-wrapper">

        <!-- Breadcrumb -->
        @component('components.breadcrumb')
            @slot('title')
                {{ __('common.faq') }}
            @endslot
            @slot('li_1')
                {{ __('common.home') }}
            @endslot
            @slot('li_2')
                {{ __('common.faq') }}
            @endslot
        @endcomponent

        <!-- Start Content -->
        <div class="content faq-page">

            <div class="container">

                <!-- FAQ header + search -->
                <div class="faq-head">
                    <h3>{{ __('faq.heading') }}</h3>
                    <p class="faq-sub">{{ __('faq.subheading') }}</p>
                    <div class="faq-search">
                        <i class="ti ti-search"></i>
                        <input type="text" id="faqSearch" placeholder="{{ __('faq.search_placeholder') }}" autocomplete="off">
                    </div>
                    <p class="faq-no-results" id="faqNoResults" hidden>{{ __('faq.no_results') }}</p>
                </div>

                <!-- FAQ body: table of contents + accordions -->
                <div class="row" id="cart-wrap">
                    <div class="col-lg-12 mx-auto">

                        <div class="cart-item-wrap">
                            <div class="row row-gap-3">
                                <!-- Table of Contents (sticky sidebar nav) -->
                                <div class="col-lg-3" style="padding-top: 45px;">
                                    <div class="card faq-sidebar mb-lg-0">
                                        <div class="card-body">
                                            <h5 class="mb-3">{{ __('faq.table_of_contents') }}</h5>
                                            <ul class="faq-sidebar">
                                                <li><a href="#general" class="nav-link">{{ __('faq.general') }}</a></li>
                                                <li><a href="#buying" class="nav-link">{{ __('faq.buying') }}</a></li>
                                                <li><a href="#selling" class="nav-link">{{ __('faq.selling') }}</a></li>
                                                <li><a href="#renting" class="nav-link">{{ __('faq.renting') }}</a></li>
                                                <li><a href="#legal" class="nav-link">{{ __('faq.legal') }}</a></li>
                                                <li><a href="#financial" class="nav-link">{{ __('faq.financial') }}</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div><!-- end col -->
                                <!-- Questions grouped by category -->
                                <div class="col-lg-9">
                                    <div>
                                        <!-- Category: General -->
                                        <div class="mb-4 faq-cat" id="general">
                                            <h4 class="mb-3"><span class="faq-cat-ic"><i class="ti ti-help-circle"></i></span>{{ __('faq.general') }}</h4>
                                            <div class="accordion accordion-bordered accordion-custom-icon accordion-arrow-none" id="CustomIconaccordionExample">
                                                <div class="accordion-item">
                                                    <h6 class="accordion-header" id="CustomIconheading1">
                                                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#CustomIconcollapse1" aria-expanded="true" aria-controls="CustomIconcollapse1">
                                                            {{ __('faq.q1') }}
                                                            <i class="ti ti-plus accordion-icon accordion-icon-on"></i>
                                                            <i class="ti ti-minus accordion-icon accordion-icon-off"></i>
                                                        </button>
                                                    </h6>
                                                    <div id="CustomIconcollapse1" class="accordion-collapse collapse show" aria-labelledby="CustomIconheading1" data-bs-parent="#CustomIconaccordionExample">
                                                        <div class="accordion-body">{{ __('faq.a1') }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="accordion accordion-bordered accordion-custom-icon accordion-arrow-none" id="CustomIconaccordionExample2">
                                                <div class="accordion-item">
                                                    <h6 class="accordion-header" id="CustomIconheading2">
                                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#CustomIconcollapse2" aria-expanded="false" aria-controls="CustomIconcollapse2">
                                                            {{ __('faq.q2') }}
                                                            <i class="ti ti-plus accordion-icon accordion-icon-on"></i>
                                                            <i class="ti ti-minus accordion-icon accordion-icon-off"></i>
                                                        </button>
                                                    </h6>
                                                    <div id="CustomIconcollapse2" class="accordion-collapse collapse" aria-labelledby="CustomIconheading2" data-bs-parent="#CustomIconaccordionExample2">
                                                        <div class="accordion-body">{{ __('faq.a2') }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="accordion accordion-bordered accordion-custom-icon accordion-arrow-none" id="CustomIconaccordionExample3">
                                                <div class="accordion-item">
                                                    <h6 class="accordion-header" id="CustomIconheading3">
                                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#CustomIconcollapse3" aria-expanded="false" aria-controls="CustomIconcollapse3">
                                                            {{ __('faq.q3') }}
                                                            <i class="ti ti-plus accordion-icon accordion-icon-on"></i>
                                                            <i class="ti ti-minus accordion-icon accordion-icon-off"></i>
                                                        </button>
                                                    </h6>
                                                    <div id="CustomIconcollapse3" class="accordion-collapse collapse" aria-labelledby="CustomIconheading3" data-bs-parent="#CustomIconaccordionExample3">
                                                        <div class="accordion-body">{{ __('faq.a3') }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <hr class="my-4">

                                        <!-- Category: Buying -->
                                        <div class="mb-4 faq-cat" id="buying">
                                            <h4 class="mb-3"><span class="faq-cat-ic"><i class="ti ti-home"></i></span>{{ __('faq.buying') }}</h4>
                                            <div class="accordion accordion-bordered accordion-custom-icon accordion-arrow-none" id="CustomIconaccordionExample4">
                                                <div class="accordion-item">
                                                    <h6 class="accordion-header" id="CustomIconheading4">
                                                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#CustomIconcollapse4" aria-expanded="true" aria-controls="CustomIconcollapse4">
                                                            {{ __('faq.q4') }}
                                                            <i class="ti ti-plus accordion-icon accordion-icon-on"></i>
                                                            <i class="ti ti-minus accordion-icon accordion-icon-off"></i>
                                                        </button>
                                                    </h6>
                                                    <div id="CustomIconcollapse4" class="accordion-collapse collapse show" aria-labelledby="CustomIconheading4" data-bs-parent="#CustomIconaccordionExample4">
                                                        <div class="accordion-body">{{ __('faq.a4') }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="accordion accordion-bordered accordion-custom-icon accordion-arrow-none" id="CustomIconaccordionExample5">
                                                <div class="accordion-item">
                                                    <h6 class="accordion-header" id="CustomIconheading5">
                                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#CustomIconcollapse5" aria-expanded="false" aria-controls="CustomIconcollapse5">
                                                            {{ __('faq.q5') }}
                                                            <i class="ti ti-plus accordion-icon accordion-icon-on"></i>
                                                            <i class="ti ti-minus accordion-icon accordion-icon-off"></i>
                                                        </button>
                                                    </h6>
                                                    <div id="CustomIconcollapse5" class="accordion-collapse collapse" aria-labelledby="CustomIconheading5" data-bs-parent="#CustomIconaccordionExample5">
                                                        <div class="accordion-body">{{ __('faq.a5') }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="accordion accordion-bordered accordion-custom-icon accordion-arrow-none" id="CustomIconaccordionExample6">
                                                <div class="accordion-item">
                                                    <h6 class="accordion-header" id="CustomIconheading6">
                                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#CustomIconcollapse6" aria-expanded="false" aria-controls="CustomIconcollapse6">
                                                            {{ __('faq.q6') }}
                                                            <i class="ti ti-plus accordion-icon accordion-icon-on"></i>
                                                            <i class="ti ti-minus accordion-icon accordion-icon-off"></i>
                                                        </button>
                                                    </h6>
                                                    <div id="CustomIconcollapse6" class="accordion-collapse collapse" aria-labelledby="CustomIconheading6" data-bs-parent="#CustomIconaccordionExample6">
                                                        <div class="accordion-body">{{ __('faq.a6') }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <hr class="my-4">

                                        <!-- Category: Selling -->
                                        <div class="mb-4 faq-cat" id="selling">
                                            <h4 class="mb-3"><span class="faq-cat-ic"><i class="ti ti-tag"></i></span>{{ __('faq.selling') }}</h4>
                                            <div class="accordion accordion-bordered accordion-custom-icon accordion-arrow-none" id="CustomIconaccordionExample7">
                                                <div class="accordion-item">
                                                    <h6 class="accordion-header" id="CustomIconheading7">
                                                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#CustomIconcollapse7" aria-expanded="true" aria-controls="CustomIconcollapse7">
                                                            {{ __('faq.q7') }}
                                                            <i class="ti ti-plus accordion-icon accordion-icon-on"></i>
                                                            <i class="ti ti-minus accordion-icon accordion-icon-off"></i>
                                                        </button>
                                                    </h6>
                                                    <div id="CustomIconcollapse7" class="accordion-collapse collapse show" aria-labelledby="CustomIconheading7" data-bs-parent="#CustomIconaccordionExample7">
                                                        <div class="accordion-body">{{ __('faq.a7') }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="accordion accordion-bordered accordion-custom-icon accordion-arrow-none" id="CustomIconaccordionExample8">
                                                <div class="accordion-item">
                                                    <h6 class="accordion-header" id="CustomIconheading8">
                                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#CustomIconcollapse8" aria-expanded="false" aria-controls="CustomIconcollapse8">
                                                            {{ __('faq.q8') }}
                                                            <i class="ti ti-plus accordion-icon accordion-icon-on"></i>
                                                            <i class="ti ti-minus accordion-icon accordion-icon-off"></i>
                                                        </button>
                                                    </h6>
                                                    <div id="CustomIconcollapse8" class="accordion-collapse collapse" aria-labelledby="CustomIconheading8" data-bs-parent="#CustomIconaccordionExample8">
                                                        <div class="accordion-body">{{ __('faq.a8') }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="accordion accordion-bordered accordion-custom-icon accordion-arrow-none" id="CustomIconaccordionExample9">
                                                <div class="accordion-item">
                                                    <h6 class="accordion-header" id="CustomIconheading9">
                                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#CustomIconcollapse9" aria-expanded="false" aria-controls="CustomIconcollapse9">
                                                            {{ __('faq.q9') }}
                                                            <i class="ti ti-plus accordion-icon accordion-icon-on"></i>
                                                            <i class="ti ti-minus accordion-icon accordion-icon-off"></i>
                                                        </button>
                                                    </h6>
                                                    <div id="CustomIconcollapse9" class="accordion-collapse collapse" aria-labelledby="CustomIconheading9" data-bs-parent="#CustomIconaccordionExample9">
                                                        <div class="accordion-body">{{ __('faq.a9') }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <hr class="my-4">

                                        <!-- Category: Renting -->
                                        <div class="mb-4 faq-cat" id="renting">
                                            <h4 class="mb-3"><span class="faq-cat-ic"><i class="ti ti-key"></i></span>{{ __('faq.renting') }}</h4>
                                            <div class="accordion accordion-bordered accordion-custom-icon accordion-arrow-none" id="CustomIconaccordionExample10">
                                                <div class="accordion-item">
                                                    <h6 class="accordion-header" id="CustomIconheading10">
                                                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#CustomIconcollapse10" aria-expanded="true" aria-controls="CustomIconcollapse10">
                                                            {{ __('faq.q10') }}
                                                            <i class="ti ti-plus accordion-icon accordion-icon-on"></i>
                                                            <i class="ti ti-minus accordion-icon accordion-icon-off"></i>
                                                        </button>
                                                    </h6>
                                                    <div id="CustomIconcollapse10" class="accordion-collapse collapse show" aria-labelledby="CustomIconheading10" data-bs-parent="#CustomIconaccordionExample10">
                                                        <div class="accordion-body">{{ __('faq.a10') }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="accordion accordion-bordered accordion-custom-icon accordion-arrow-none" id="CustomIconaccordionExample11">
                                                <div class="accordion-item">
                                                    <h6 class="accordion-header" id="CustomIconheading11">
                                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#CustomIconcollapse11" aria-expanded="false" aria-controls="CustomIconcollapse11">
                                                            {{ __('faq.q11') }}
                                                            <i class="ti ti-plus accordion-icon accordion-icon-on"></i>
                                                            <i class="ti ti-minus accordion-icon accordion-icon-off"></i>
                                                        </button>
                                                    </h6>
                                                    <div id="CustomIconcollapse11" class="accordion-collapse collapse" aria-labelledby="CustomIconheading11" data-bs-parent="#CustomIconaccordionExample11">
                                                        <div class="accordion-body">{{ __('faq.a11') }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="accordion accordion-bordered accordion-custom-icon accordion-arrow-none" id="CustomIconaccordionExample12">
                                                <div class="accordion-item">
                                                    <h6 class="accordion-header" id="CustomIconheading12">
                                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#CustomIconcollapse12" aria-expanded="false" aria-controls="CustomIconcollapse12">
                                                            {{ __('faq.q12') }}
                                                            <i class="ti ti-plus accordion-icon accordion-icon-on"></i>
                                                            <i class="ti ti-minus accordion-icon accordion-icon-off"></i>
                                                        </button>
                                                    </h6>
                                                    <div id="CustomIconcollapse12" class="accordion-collapse collapse" aria-labelledby="CustomIconheading12" data-bs-parent="#CustomIconaccordionExample12">
                                                        <div class="accordion-body">{{ __('faq.a12') }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <hr class="my-4">

                                        <!-- Category: Legal -->
                                        <div class="mb-4 faq-cat" id="legal">
                                            <h4 class="mb-3"><span class="faq-cat-ic"><i class="ti ti-scale"></i></span>{{ __('faq.legal') }}</h4>
                                            <div class="accordion accordion-bordered accordion-custom-icon accordion-arrow-none" id="CustomIconaccordionExample13">
                                                <div class="accordion-item">
                                                    <h6 class="accordion-header" id="CustomIconheading13">
                                                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#CustomIconcollapse13" aria-expanded="true" aria-controls="CustomIconcollapse13">
                                                            {{ __('faq.q13') }}
                                                            <i class="ti ti-plus accordion-icon accordion-icon-on"></i>
                                                            <i class="ti ti-minus accordion-icon accordion-icon-off"></i>
                                                        </button>
                                                    </h6>
                                                    <div id="CustomIconcollapse13" class="accordion-collapse collapse show" aria-labelledby="CustomIconheading13" data-bs-parent="#CustomIconaccordionExample13">
                                                        <div class="accordion-body">{{ __('faq.a13') }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="accordion accordion-bordered accordion-custom-icon accordion-arrow-none" id="CustomIconaccordionExample14">
                                                <div class="accordion-item">
                                                    <h6 class="accordion-header" id="CustomIconheading14">
                                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#CustomIconcollapse14" aria-expanded="false" aria-controls="CustomIconcollapse14">
                                                            {{ __('faq.q14') }}
                                                            <i class="ti ti-plus accordion-icon accordion-icon-on"></i>
                                                            <i class="ti ti-minus accordion-icon accordion-icon-off"></i>
                                                        </button>
                                                    </h6>
                                                    <div id="CustomIconcollapse14" class="accordion-collapse collapse" aria-labelledby="CustomIconheading14" data-bs-parent="#CustomIconaccordionExample14">
                                                        <div class="accordion-body">{{ __('faq.a14') }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="accordion accordion-bordered accordion-custom-icon accordion-arrow-none" id="CustomIconaccordionExample15">
                                                <div class="accordion-item">
                                                    <h6 class="accordion-header" id="CustomIconheading15">
                                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#CustomIconcollapse15" aria-expanded="false" aria-controls="CustomIconcollapse15">
                                                            {{ __('faq.q15') }}
                                                            <i class="ti ti-plus accordion-icon accordion-icon-on"></i>
                                                            <i class="ti ti-minus accordion-icon accordion-icon-off"></i>
                                                        </button>
                                                    </h6>
                                                    <div id="CustomIconcollapse15" class="accordion-collapse collapse" aria-labelledby="CustomIconheading15" data-bs-parent="#CustomIconaccordionExample15">
                                                        <div class="accordion-body">{{ __('faq.a15') }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <hr class="my-4">

                                        <!-- Category: Financial -->
                                        <div class="mb-0 faq-cat" id="financial">
                                            <h4 class="mb-3"><span class="faq-cat-ic"><i class="ti ti-coin"></i></span>{{ __('faq.financial') }}</h4>
                                            <div class="accordion accordion-bordered accordion-custom-icon accordion-arrow-none" id="CustomIconaccordionExample16">
                                                <div class="accordion-item">
                                                    <h6 class="accordion-header" id="CustomIconheading16">
                                                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#CustomIconcollapse16" aria-expanded="true" aria-controls="CustomIconcollapse16">
                                                            {{ __('faq.q16') }}
                                                            <i class="ti ti-plus accordion-icon accordion-icon-on"></i>
                                                            <i class="ti ti-minus accordion-icon accordion-icon-off"></i>
                                                        </button>
                                                    </h6>
                                                    <div id="CustomIconcollapse16" class="accordion-collapse collapse show" aria-labelledby="CustomIconheading16" data-bs-parent="#CustomIconaccordionExample16">
                                                        <div class="accordion-body">{{ __('faq.a16') }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="accordion accordion-bordered accordion-custom-icon accordion-arrow-none" id="CustomIconaccordionExample17">
                                                <div class="accordion-item">
                                                    <h6 class="accordion-header" id="CustomIconheading17">
                                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#CustomIconcollapse17" aria-expanded="false" aria-controls="CustomIconcollapse17">
                                                            {{ __('faq.q17') }}
                                                            <i class="ti ti-plus accordion-icon accordion-icon-on"></i>
                                                            <i class="ti ti-minus accordion-icon accordion-icon-off"></i>
                                                        </button>
                                                    </h6>
                                                    <div id="CustomIconcollapse17" class="accordion-collapse collapse" aria-labelledby="CustomIconheading17" data-bs-parent="#CustomIconaccordionExample17">
                                                        <div class="accordion-body">{{ __('faq.a17') }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="accordion accordion-bordered accordion-custom-icon accordion-arrow-none" id="CustomIconaccordionExample18">
                                                <div class="accordion-item mb-0">
                                                    <h6 class="accordion-header" id="CustomIconheading18">
                                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#CustomIconcollapse18" aria-expanded="false" aria-controls="CustomIconcollapse18">
                                                            {{ __('faq.q18') }}
                                                            <i class="ti ti-plus accordion-icon accordion-icon-on"></i>
                                                            <i class="ti ti-minus accordion-icon accordion-icon-off"></i>
                                                        </button>
                                                    </h6>
                                                    <div id="CustomIconcollapse18" class="accordion-collapse collapse" aria-labelledby="CustomIconheading18" data-bs-parent="#CustomIconaccordionExample18">
                                                        <div class="accordion-body">{{ __('faq.a18') }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div><!-- end col -->
                            </div>
                            <!-- end row -->
                        </div>

                    </div><!-- end col -->
                </div>
                <!-- end row -->

                <!-- Still have questions? Contact block -->
                <div class="faq-contact">
                    <h4>{{ __('faq.still_questions') }}</h4>
                    <p>{{ __('faq.contact_desc') }}</p>
                    <div class="faq-contact-actions">
                        @if($faqTel)
                        <a href="tel:{{ $faqTel }}" class="faq-c"><i class="ti ti-phone"></i>{{ $workspace['phone'] }}</a>
                        @endif
                        @if($faqWa)
                        <a href="https://wa.me/{{ $faqWa }}" target="_blank" rel="noopener" class="faq-c"><i class="ti ti-brand-whatsapp"></i>WhatsApp</a>
                        @endif
                        @if($faqViber)
                        <a href="viber://chat?number=+{{ $faqViber }}" class="faq-c"><i class="ti ti-brand-viber"></i>Viber</a>
                        @endif
                        <a href="/{{ app()->getLocale() }}/contact-us" class="btn btn-primary">{{ __('faq.contact_btn') }}</a>
                    </div>
                </div>

            </div>

        </div>
        <!-- End Content -->

    </div>

    <!-- ========================
        End Page Content
    ========================= -->

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Sidebar scroll-spy + smooth scroll for the table of contents
    var links = document.querySelectorAll('ul.faq-sidebar .nav-link');
    var sections = [];
    links.forEach(function (link) {
        var id = link.getAttribute('href').replace('#', '');
        var el = document.getElementById(id);
        if (el) sections.push({ id: id, el: el, link: link });
    });
    if (!sections.length) return;

    // Set first as active initially
    sections[0].link.classList.add('active');

    function onScroll() {
        var midScreen = window.scrollY + window.innerHeight / 2;
        var current = sections[0];
        for (var i = 0; i < sections.length; i++) {
            if (sections[i].el.offsetTop <= midScreen) {
                current = sections[i];
            }
        }
        links.forEach(function (l) { l.classList.remove('active'); });
        current.link.classList.add('active');
    }

    window.addEventListener('scroll', onScroll, { passive: true });

    // Smooth scroll on click with header offset
    links.forEach(function (link) {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            var id = this.getAttribute('href').replace('#', '');
            var target = document.getElementById(id);
            if (target) {
                window.scrollTo({ top: target.offsetTop - window.innerHeight / 2, behavior: 'smooth' });
            }
        });
    });

    // Live search filter over questions
    var search = document.getElementById('faqSearch');
    if (search) {
        var cats = Array.prototype.slice.call(document.querySelectorAll('.faq-page .faq-cat'));
        var seps = Array.prototype.slice.call(document.querySelectorAll('.faq-page hr'));
        var noRes = document.getElementById('faqNoResults');
        search.addEventListener('input', function () {
            var q = this.value.trim().toLowerCase();
            var words = q.split(/\s+/).filter(Boolean);
            var anyVisible = false;
            cats.forEach(function (cat) {
                var heading = cat.querySelector('h4');
                var catTitle = heading ? heading.textContent : '';
                var items = cat.querySelectorAll('.accordion');
                var catVisible = false;
                items.forEach(function (it) {
                    // search across category title + question + answer, by words (any order, gaps ok)
                    var text = (catTitle + ' ' + it.textContent).toLowerCase();
                    var match = !words.length || words.every(function (w) { return text.indexOf(w) !== -1; });
                    it.style.display = match ? '' : 'none';
                    if (match) catVisible = true;
                });
                cat.style.display = catVisible ? '' : 'none';
                if (catVisible) anyVisible = true;
            });
            seps.forEach(function (hr) { hr.style.display = q ? 'none' : ''; });
            if (noRes) noRes.hidden = anyVisible;
        });
    }
});
</script>

@endsection
