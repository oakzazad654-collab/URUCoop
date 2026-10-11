@extends('layouts.public')

@section('title', $announcement->title.' | ข่าวสาร URU Coop-IS')

@section('content')
<article class="news-article">
    <a class="news-back-link" href="{{ route('news.index') }}"><i data-lucide="arrow-left" style="width:15px;height:15px"></i>กลับไปหน้าข่าวสาร</a>
    <header class="news-article-heading">
        <time datetime="{{ $announcement->published_at->toIso8601String() }}">{{ $announcement->published_at->format('d/m/Y') }}</time>
        <h1>{{ $announcement->title }}</h1>
        @if($announcement->excerpt)<p>{{ $announcement->excerpt }}</p>@endif
    </header>
    @if($announcement->image_path)<img class="news-article-image" src="{{ route('public.news-image', $announcement) }}" alt="ภาพประกอบข่าว: {{ $announcement->title }}">@endif
    <div class="news-article-body">{{ $announcement->body }}</div>
</article>
@endsection

@push('styles')
<style>
    .news-article{max-width:820px;margin:0 auto}.news-back-link{display:inline-flex;align-items:center;gap:7px;margin-bottom:24px;color:#166534;font-size:13px;text-decoration:none}.news-article-heading{padding-bottom:22px;border-bottom:1px solid #d8e3da}.news-article-heading time{color:#66806d;font-size:12px}.news-article-heading h1{margin:8px 0;color:#183528;font-size:30px;line-height:1.45;letter-spacing:0}.news-article-heading p{margin:0;color:#64766a;font-size:15px;line-height:1.8}.news-article-image{display:block;width:100%;height:auto;max-height:min(70vh,720px);margin:24px 0;object-fit:contain;object-position:center;background:#e8efe9;border-radius:8px}.news-article-body{padding:12px 0 36px;color:#344b3c;font-size:15px;line-height:2;white-space:pre-line;overflow-wrap:anywhere}@media(max-width:700px){.news-article-heading h1{font-size:24px}.news-article-body{font-size:14px}}
</style>
@endpush