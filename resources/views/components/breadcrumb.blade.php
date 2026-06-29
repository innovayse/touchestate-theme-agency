<!-- Start Breadscrumb -->
<div class="breadcrumb-bar">
    <img src="{{URL::asset('build/img/bg/breadcrumb-bg-01.png')}}" alt="" class="breadcrumb-bg-01 d-none d-lg-block">
    <img src="{{URL::asset('build/img/bg/breadcrumb-bg-02.png')}}" alt="" class="breadcrumb-bg-02 d-none d-lg-block">
    <img src="{{URL::asset('build/img/bg/breadcrumb-bg-03.png')}}" alt="" class="breadcrumb-bg-03">
    <div class="row align-items-center text-center position-relative z-1">
        <div class="col-md-12 col-12 breadcrumb-arrow">
            <h1 class="breadcrumb-title">{{ $title }}</h1>
            <nav aria-label="breadcrumb" class="page-breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{url('/' . app()->getLocale())}}"><span><i class="material-icons-outlined me-1">home</i></span>{{ $li_1 }}</a></li>
                    @if(isset($li_3))
                    <li class="breadcrumb-item"><a href="{{ $li_2_url ?? '#' }}">{{ $li_2 }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $li_3 }}</li>
                    @else
                    <li class="breadcrumb-item active" aria-current="page">{{ $li_2 }}</li>
                    @endif
                </ol>
            </nav>
        </div>
    </div>
</div>
<!-- End Breadscrumb -->