@extends('layouts.public')

@section('title', 'URU Coop-IS | สหกิจศึกษา')

@section('content')
<section class="public-home-hero" aria-labelledby="homeHeading" @if($backgroundImage) style="--public-home-background-image: url('{{ $backgroundImage }}')" @endif>
    <div class="public-home-hero-content">
        <div class="public-home-hero-copy">
            <p class="public-home-eyebrow"><span></span> ระบบสารสนเทศสหกิจศึกษา</p>
            <h1 id="homeHeading">ก้าวสู่โลกการทำงาน<br><span>ไปกับ URU Coop-IS</span></h1>
            <p class="public-home-lead">ศูนย์กลางการดำเนินงานสหกิจศึกษา ตั้งแต่สมัคร ส่งเอกสาร ติดตามผล ไปจนถึงการนิเทศงาน</p>
            <div class="public-home-actions">
                <a class="btn btn-primary" href="{{ route('login') }}"><i data-lucide="log-in" class="me-1" style="width:16px;height:16px"></i>เข้าสู่ระบบ</a>
                <a class="btn btn-outline-light" href="{{ route('register') }}"><i data-lucide="user-round-plus" class="me-1" style="width:16px;height:16px"></i>สมัครสมาชิก</a>
                <a class="public-home-text-link" href="{{ route('public.guide') }}">ดูขั้นตอนสหกิจศึกษา <i data-lucide="arrow-right" style="width:16px;height:16px"></i></a>
            </div>
        </div>
        <aside class="public-home-hero-note" aria-label="จุดเด่นของระบบ">
            <span class="public-home-hero-note-icon"><i data-lucide="workflow"></i></span>
            <span class="public-home-hero-note-label">ทุกขั้นตอนในที่เดียว</span>
            <strong>จากวันสมัคร<br>ถึงวันเริ่มงาน</strong>
            <span class="public-home-hero-note-divider"></span>
            <span class="public-home-hero-note-foot"><i data-lucide="landmark"></i> มหาวิทยาลัยราชภัฏอุตรดิตถ์</span>
        </aside>
    </div>
    <a class="public-home-scroll" href="#coopJourney"><span>สำรวจระบบ</span><i data-lucide="arrow-down"></i></a>
</section>

<section class="public-home-path" id="coopJourney" aria-labelledby="journeyHeading">
    <div class="public-home-section-heading">
        <div><p class="public-home-eyebrow">เรียบง่ายในทุกขั้นตอน</p><h2 id="journeyHeading">เส้นทางสหกิจศึกษา</h2></div>
        <span class="public-home-section-caption">ตั้งแต่เตรียมตัวจนจบการนิเทศ</span>
    </div>
    <div class="public-home-path-grid">
        <article class="public-home-step">
            <span class="public-home-step-icon"><i data-lucide="file-user"></i></span>
            <span class="public-home-step-number">ขั้นตอนที่ 01</span>
            <strong>สมัครและตรวจคุณสมบัติ</strong>
            <span>ยื่นใบสมัคร พร้อมติดตามผลการพิจารณาได้ในระบบ</span>
        </article>
        <article class="public-home-step">
            <span class="public-home-step-icon"><i data-lucide="building-2"></i></span>
            <span class="public-home-step-number">ขั้นตอนที่ 02</span>
            <strong>เตรียมเอกสารและสถานที่ฝึก</strong>
            <span>จัดการหนังสือ ข้อมูลสถานประกอบการ และแผนปฏิบัติงาน</span>
        </article>
        <article class="public-home-step">
            <span class="public-home-step-icon"><i data-lucide="map-pinned"></i></span>
            <span class="public-home-step-number">ขั้นตอนที่ 03</span>
            <strong>วางแผนและรับการนิเทศ</strong>
            <span>ส่งโครงร่างรายงาน พร้อมติดตามกำหนดการและผลนิเทศ</span>
        </article>
    </div>
</section>

@if($youtubeVideoId)
<section class="public-home-video" aria-labelledby="homeVideoHeading">
    <div class="public-home-section-heading">
        <div><p class="public-home-eyebrow">รู้จักสหกิจศึกษาให้มากขึ้น</p><h2 id="homeVideoHeading">วิดีโอแนะนำ</h2></div>
        <span class="public-home-section-caption">เรื่องราวและข้อมูลจากศูนย์สหกิจศึกษา</span>
    </div>
    <div class="public-home-video-frame">
        <iframe src="https://www.youtube-nocookie.com/embed/{{ $youtubeVideoId }}" title="วิดีโอแนะนำระบบสหกิจศึกษา" loading="lazy" referrerpolicy="strict-origin-when-cross-origin" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
    </div>
</section>
@endif

<section class="public-home-news" aria-labelledby="latestNewsHeading">
    <div class="public-home-section-heading">
        <div><p class="public-home-eyebrow">ประกาศจากศูนย์สหกิจศึกษา</p><h2 id="latestNewsHeading">ข่าวสารล่าสุด</h2></div>
        <a class="public-home-section-link" href="{{ route('news.index') }}">ดูข่าวทั้งหมด <i data-lucide="arrow-up-right"></i></a>
    </div>
    @if($latestAnnouncements->isNotEmpty())
        <div class="public-home-news-grid">
            @foreach($latestAnnouncements as $announcement)
                <article class="public-home-news-item">
                    <a class="public-home-news-image {{ $announcement->image_path ? '' : 'public-home-news-image-empty' }}" href="{{ route('news.show', $announcement) }}" tabindex="-1" aria-hidden="true">
                        @if($announcement->image_path)
                            <img src="{{ route('public.news-image', $announcement) }}" alt="" loading="lazy">
                        @else
                            <span><i data-lucide="newspaper"></i></span>
                        @endif
                        <span class="public-home-news-image-tag">ข่าวประชาสัมพันธ์</span>
                    </a>
                    <div class="public-home-news-copy">
                        <time datetime="{{ $announcement->published_at->toIso8601String() }}"><i data-lucide="calendar-days"></i>{{ $announcement->published_at->format('d/m/Y') }}</time>
                        <h3><a href="{{ route('news.show', $announcement) }}">{{ $announcement->title }}</a></h3>
                        <p>{{ $announcement->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($announcement->body), 130) }}</p>
                        <a class="public-home-read-link" href="{{ route('news.show', $announcement) }}">อ่านรายละเอียด <i data-lucide="arrow-right"></i></a>
                    </div>
                </article>
            @endforeach
        </div>
    @else
        <div class="public-home-empty"><i data-lucide="newspaper"></i><span>ยังไม่มีประกาศที่เผยแพร่</span></div>
    @endif
</section>
@endsection

@push('styles')
<style>
    .public-home-hero{position:relative;isolation:isolate;overflow:hidden;min-height:430px;padding:clamp(32px,6vw,68px);display:flex;align-items:center;background:#123d2c;background-image:linear-gradient(90deg,rgba(9,43,29,.98) 0%,rgba(12,53,35,.92) 46%,rgba(12,53,35,.55) 100%),var(--public-home-background-image,radial-gradient(ellipse at 85% 20%,#35704b,#123d2c 65%));background-position:center;background-size:cover;color:#fff;border-radius:16px;box-shadow:0 20px 50px rgba(17,56,35,.17)}
    .public-home-hero:before,.public-home-hero:after{position:absolute;z-index:-1;border:1px solid rgba(255,255,255,.13);border-radius:50%;content:"";pointer-events:none}.public-home-hero:before{width:460px;height:460px;right:9%;top:-270px;box-shadow:0 0 0 42px rgba(255,255,255,.035),0 0 0 84px rgba(255,255,255,.025)}.public-home-hero:after{width:210px;height:210px;right:26%;bottom:-150px;background:rgba(255,255,255,.035)}
    .public-home-hero-content{position:relative;z-index:1;display:grid;width:100%;grid-template-columns:minmax(0,1fr) 245px;align-items:center;gap:32px}.public-home-hero-copy{max-width:650px;animation:home-rise .55s ease-out both}.public-home-eyebrow{display:flex;align-items:center;gap:9px;margin:0 0 12px;color:#558064;font-size:11px;font-weight:600;letter-spacing:.04em}.public-home-eyebrow>span{width:20px;height:2px;background:#72c58c}
    .public-home-hero .public-home-eyebrow{color:#c1dfca}.public-home-hero h1{margin:0;color:#fff;font-size:clamp(32px,4.3vw,52px);line-height:1.3;font-weight:600;letter-spacing:0}.public-home-hero h1 span{color:#b5dfbf}.public-home-lead{max-width:570px;margin:16px 0 28px;color:#e0eee4;font-size:15px;line-height:1.9}.public-home-actions{display:flex;flex-wrap:wrap;align-items:center;gap:20px}.public-home-actions .btn{min-height:46px;padding:11px 19px;background:#36a464;border-color:#36a464;border-radius:9px;box-shadow:0 8px 20px rgba(5,25,15,.2);font-size:13px;font-weight:500}.public-home-actions .btn:hover{background:#2b9256;border-color:#2b9256;transform:translateY(-1px)}.public-home-text-link{display:inline-flex;align-items:center;gap:7px;color:#166534;font-size:12px;font-weight:600;text-decoration:none}.public-home-text-link:hover{color:#0d482b}.public-home-hero .public-home-text-link{color:#fff}.public-home-hero .public-home-text-link:hover{color:#b5dfbf}
    .public-home-hero-note{display:flex;flex-direction:column;align-items:flex-start;gap:12px;padding:24px 22px;color:#fff;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.2);border-radius:14px;box-shadow:0 14px 34px rgba(0,0,0,.12);backdrop-filter:blur(10px)}.public-home-hero-note-icon{display:grid;width:42px;height:42px;place-items:center;color:#d8f3df;background:rgba(111,195,137,.2);border:1px solid rgba(182,235,194,.25);border-radius:12px}.public-home-hero-note-icon svg{width:21px;height:21px}.public-home-hero-note-label{color:#d3e8d8;font-size:10px}.public-home-hero-note strong{font-size:20px;line-height:1.55;font-weight:500}.public-home-hero-note-divider{width:100%;height:1px;background:rgba(255,255,255,.2)}.public-home-hero-note-foot{display:flex;align-items:center;gap:8px;color:#e2efe5;font-size:10px;line-height:1.6}.public-home-hero-note-foot svg{width:15px;height:15px;flex:0 0 15px;color:#b7dfc2}
    .public-home-scroll{position:absolute;right:clamp(32px,6vw,68px);bottom:20px;display:flex;align-items:center;gap:8px;color:#d8e8dc;text-decoration:none;font-size:10px}.public-home-scroll svg{width:15px;height:15px}.public-home-scroll:hover{color:#fff}
    .public-home-path{margin:70px 0 66px}.public-home-section-heading{display:flex;align-items:end;justify-content:space-between;gap:20px;margin-bottom:22px}.public-home-section-heading .public-home-eyebrow{margin-bottom:5px}.public-home-section-heading h2{margin:0;color:#183528;font-size:25px;font-weight:600;letter-spacing:0}.public-home-section-caption{padding-bottom:3px;color:#75847a;font-size:11px}.public-home-path-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px}.public-home-step{display:flex;min-height:206px;flex-direction:column;align-items:flex-start;padding:22px;background:#fff;border:1px solid #e0e9e1;border-radius:13px;box-shadow:0 8px 24px rgba(22,55,35,.035);transition:transform .18s ease,box-shadow .18s ease,border-color .18s ease}.public-home-step:hover{transform:translateY(-3px);border-color:#c3ddca;box-shadow:0 14px 30px rgba(22,55,35,.08)}.public-home-step-icon{display:grid;width:44px;height:44px;place-items:center;margin-bottom:20px;color:#247849;background:#edf6ef;border:1px solid #e1eee3;border-radius:12px}.public-home-step-icon svg{width:21px;height:21px}.public-home-step-number{margin-bottom:4px;color:#398057;font-size:10px;font-weight:600;letter-spacing:.04em}.public-home-step strong{color:#20392a;font-size:14px;font-weight:600}.public-home-step>span:last-child{margin-top:7px;color:#718176;font-size:11px;line-height:1.8}
    .public-home-video{margin:0 0 68px}.public-home-video-frame{max-width:860px;aspect-ratio:16/9;margin:0 auto;overflow:hidden;border:1px solid #dfe9e1;border-radius:14px;background:#10251a;box-shadow:0 16px 38px rgba(20,50,32,.12)}.public-home-video-frame iframe{width:100%;height:100%;border:0}
    .public-home-news{padding:0 0 28px}.public-home-section-link{display:inline-flex;align-items:center;gap:7px;padding:9px 13px;color:#216a3e;background:#fff;border:1px solid #dce8de;border-radius:9px;text-decoration:none;font-size:11px;font-weight:500}.public-home-section-link svg{width:15px;height:15px}.public-home-section-link:hover{color:#15552f;background:#f5faf6;border-color:#b9d7c0}.public-home-news-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px}.public-home-news-item{display:flex;overflow:hidden;min-width:0;flex-direction:column;background:#fff;border:1px solid #e0e9e1;border-radius:13px;box-shadow:0 8px 24px rgba(22,55,35,.035);transition:transform .18s ease,box-shadow .18s ease}.public-home-news-item:hover{transform:translateY(-3px);box-shadow:0 14px 30px rgba(22,55,35,.08)}.public-home-news-image{position:relative;display:grid;height:178px;place-items:center;overflow:hidden;background:#e8f1e9;text-decoration:none}.public-home-news-image img{width:100%;height:100%;object-fit:cover;transition:transform .35s ease}.public-home-news-item:hover .public-home-news-image img{transform:scale(1.04)}.public-home-news-image-empty{background:radial-gradient(circle at 80% 15%,rgba(91,155,107,.18),transparent 38%),linear-gradient(145deg,#edf5ee,#dfece1)}.public-home-news-image-empty>span:first-child{display:grid;width:60px;height:60px;place-items:center;color:#287a49;background:rgba(255,255,255,.75);border:1px solid rgba(255,255,255,.9);border-radius:18px}.public-home-news-image-empty>span:first-child svg{width:27px;height:27px}.public-home-news-image-tag{position:absolute;left:13px;bottom:12px;padding:5px 9px;color:#fff;background:rgba(20,67,39,.8);border:1px solid rgba(255,255,255,.18);border-radius:99px;font-size:9px;backdrop-filter:blur(6px)}.public-home-news-copy{display:flex;flex:1;flex-direction:column;padding:17px}.public-home-news-copy time{display:flex;align-items:center;gap:6px;color:#718176;font-size:10px}.public-home-news-copy time svg{width:13px;height:13px;color:#518461}.public-home-news-copy h3{margin:10px 0 7px;font-size:14px;line-height:1.65;letter-spacing:0}.public-home-news-copy h3 a{color:#183528;text-decoration:none}.public-home-news-copy h3 a:hover{color:#166534}.public-home-news-copy p{display:-webkit-box;overflow:hidden;min-height:44px;margin:0 0 15px;color:#65766a;font-size:11px;line-height:1.8;-webkit-box-orient:vertical;-webkit-line-clamp:2}.public-home-read-link{display:inline-flex;align-items:center;gap:6px;margin-top:auto;color:#216a3e;font-size:10px;font-weight:600;text-decoration:none}.public-home-read-link svg{width:14px;height:14px;transition:transform .16s ease}.public-home-read-link:hover{color:#15552f}.public-home-read-link:hover svg{transform:translateX(3px)}.public-home-empty{display:flex;align-items:center;gap:12px;padding:25px;border:1px dashed #cbd9ce;border-radius:12px;background:rgba(255,255,255,.72);color:#718176;font-size:12px}.public-home-empty svg{width:19px;height:19px;color:#398057}
    @keyframes home-rise{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}
    @media(prefers-reduced-motion:reduce){.public-home-hero-copy,.public-home-step,.public-home-news-item,.public-home-news-image img{animation:none;transition:none}}
    @media(max-width:760px){.public-home-hero{min-height:450px;padding:34px 26px 55px;border-radius:13px;background-image:linear-gradient(90deg,rgba(9,43,29,.97),rgba(12,53,35,.82)),var(--public-home-background-image,radial-gradient(ellipse at 85% 20%,#35704b,#123d2c 65%))}.public-home-hero-content{grid-template-columns:1fr;gap:22px}.public-home-hero-copy{max-width:100%}.public-home-hero h1{font-size:34px}.public-home-lead{max-width:520px;font-size:13px}.public-home-hero-note{max-width:360px;display:grid;grid-template-columns:42px 1fr;column-gap:12px;row-gap:2px;padding:13px 15px}.public-home-hero-note-icon{grid-row:span 2}.public-home-hero-note-label{align-self:end}.public-home-hero-note strong{font-size:14px;line-height:1.4}.public-home-hero-note-divider,.public-home-hero-note-foot{display:none}.public-home-scroll{right:25px;bottom:16px}.public-home-path{margin:50px 0}.public-home-path-grid,.public-home-news-grid{grid-template-columns:1fr;gap:12px}.public-home-step{min-height:0;display:grid;grid-template-columns:44px minmax(0,1fr);column-gap:13px;padding:17px}.public-home-step-icon{grid-row:span 3;margin:0}.public-home-step-number{align-self:end}.public-home-step>span:last-child{margin-top:3px}.public-home-news-image{height:210px}.public-home-section-heading{align-items:flex-start}.public-home-section-heading h2{font-size:22px}}
    @media(min-width:560px) and (max-width:760px){.public-home-news-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @media(max-width:520px){.public-home-hero h1{font-size:29px}.public-home-hero-copy .public-home-eyebrow{font-size:10px}.public-home-actions{gap:13px}.public-home-actions .btn{min-height:42px;padding:9px 13px}.public-home-text-link{font-size:10px}.public-home-section-heading{gap:12px}.public-home-section-caption{display:none}.public-home-news .public-home-section-heading{align-items:center;flex-direction:row}.public-home-news .public-home-section-link{padding:7px 9px;font-size:9px}.public-home-news-image{height:190px}.public-home-video{margin-bottom:48px}.public-home-video-frame{border-radius:10px}}
</style>
@endpush