@extends('layouts.public')

@section('title', 'ข่าวสาร | URU Coop-IS')

@section('content')
<header class="news-page-heading">
    <div><p class="page-eyebrow">URU Coop-IS</p><h1>ข่าวสารและประกาศ</h1><p class="news-page-subtitle">ประกาศจากศูนย์สหกิจศึกษา มหาวิทยาลัยราชภัฏอุตรดิตถ์</p></div>
    @auth
        @if(auth()->user()->role === 'admin')<a class="btn btn-outline-primary" href="{{ route('admin.news.index') }}"><i data-lucide="newspaper" class="me-1" style="width:16px;height:16px"></i>จัดการข่าว</a>@endif
    @endauth
</header>

@if($announcements->isNotEmpty())
    <section class="news-list" aria-label="ข่าวสารที่เผยแพร่">
        @foreach($announcements as $announcement)
            <article class="news-item">
                @if($announcement->image_path)
                    <a class="news-item-image" href="{{ route('news.show', $announcement) }}" tabindex="-1" aria-hidden="true"><img src="{{ route('public.news-image', $announcement) }}" alt=""></a>
                @endif
                <div class="news-item-copy">
                    <time datetime="{{ $announcement->published_at->toIso8601String() }}">{{ $announcement->published_at->format('d/m/Y') }}</time>
                    <h2><a href="{{ route('news.show', $announcement) }}">{{ $announcement->title }}</a></h2>
                    @if($announcement->excerpt)<p>{{ $announcement->excerpt }}</p>@else<p>{{ \Illuminate\Support\Str::limit(strip_tags($announcement->body), 220) }}</p>@endif
                    <a class="news-read-link" href="{{ route('news.show', $announcement) }}">อ่านรายละเอียด <i data-lucide="arrow-right" style="width:15px;height:15px"></i></a>
                </div>
            </article>
        @endforeach
    </section>
    @if($announcements->hasPages())<div class="news-pagination">{{ $announcements->links() }}</div>@endif
@else
    <section class="news-empty"><span class="news-empty-mark"><i data-lucide="newspaper"></i></span><h2>ยังไม่มีข่าวที่เผยแพร่</h2><p>ข่าวสารและประกาศใหม่จะแสดงที่หน้านี้</p></section>
@endif
@endsection

@push('styles')
<style>
    .news-page-heading{display:flex;justify-content:space-between;align-items:end;gap:20px;margin-bottom:26px;padding-bottom:20px;border-bottom:1px solid #d8e3da}
    .news-page-heading h1{margin:0;color:#183528;font-size:28px;font-weight:600;line-height:1.35;letter-spacing:0}.news-page-subtitle{margin:8px 0 0;color:#64766a;font-size:13px}
    .news-list{display:grid;gap:14px}.news-item{display:grid;grid-template-columns:220px minmax(0,1fr);overflow:hidden;border:1px solid #dce7de;border-radius:8px;background:#fff}
    .news-item:not(:has(.news-item-image)){grid-template-columns:minmax(0,1fr)}.news-item-image{display:block;min-height:170px;background:#e5eee7}.news-item-image img{width:100%;height:100%;min-height:170px;object-fit:cover}
    .news-item-copy{padding:20px 22px}.news-item-copy time{color:#66806d;font-size:12px}.news-item-copy h2{margin:6px 0 8px;font-size:19px;line-height:1.5;letter-spacing:0}.news-item-copy h2 a{color:#183528;text-decoration:none}.news-item-copy h2 a:hover{color:#166534}.news-item-copy p{margin:0 0 14px;color:#52665a;font-size:13px;line-height:1.8;white-space:pre-line}
    .news-read-link{display:inline-flex;align-items:center;gap:6px;color:#166534;font-size:12px;font-weight:600;text-decoration:none}.news-read-link:hover{text-decoration:underline}
    .news-empty{padding:70px 20px;text-align:center;border:1px dashed #cbd9ce;border-radius:8px;background:#fff}.news-empty-mark{display:grid;width:46px;height:46px;place-items:center;margin:0 auto 14px;border-radius:8px;background:#e8f2ea;color:#166534}.news-empty h2{font-size:18px;letter-spacing:0}.news-empty p{margin:0;color:#718176;font-size:13px}
    .news-pagination{margin-top:22px}
    @media(max-width:700px){.news-page-heading{align-items:start;flex-direction:column}.news-page-heading h1{font-size:24px}.news-item{grid-template-columns:minmax(0,1fr)}.news-item-image img{max-height:220px}.news-item-copy{padding:17px}}
</style>
@endpush