@extends('layouts.public')

@section('title', 'กระดานข่าวสหกิจศึกษา | URU Coop-IS')

@section('content')
<header class="public-info-heading"><p class="public-home-eyebrow">กระดานข่าวประชาสัมพันธ์</p><h1>ข่าวและประกาศสหกิจศึกษา</h1><p>กระดานประกาศสำหรับกำหนดการ ข่าวสาร และข้อมูลที่เผยแพร่โดยผู้ดูแลเว็บไซต์</p></header>
<section class="public-board-list">
    @forelse($announcements as $announcement)
        <article class="public-board-item"><span class="public-board-icon"><i data-lucide="megaphone"></i></span><div><time>{{ optional($announcement->published_at)->format('d/m/Y') }}</time><h2><a href="{{ route('news.show', $announcement) }}">{{ $announcement->title }}</a></h2><p>{{ $announcement->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($announcement->body), 180) }}</p></div><a class="public-board-arrow" href="{{ route('news.show', $announcement) }}" aria-label="อ่าน {{ $announcement->title }}"><i data-lucide="arrow-up-right"></i></a></article>
    @empty
        <div class="empty-state"><span class="empty-icon"><i data-lucide="message-square-text"></i></span><strong>ยังไม่มีประกาศบนกระดาน</strong><span>ประกาศที่ผู้ดูแลเผยแพร่จะแสดงที่นี่</span></div>
    @endforelse
    @if($announcements->hasPages())<div class="mt-3">{{ $announcements->links('pagination::bootstrap-4') }}</div>@endif
</section>
@endsection

@push('styles')
<style>.public-info-heading{max-width:850px;margin:0 auto 25px}.public-info-heading h1{margin:0;color:#183528;font-size:28px}.public-info-heading>p:last-child{margin:8px 0 0;color:#64766a;font-size:13px}.public-board-list{max-width:900px;margin:auto;border-top:1px solid #dfe9e1}.public-board-item{display:flex;align-items:flex-start;gap:14px;padding:19px 8px;border-bottom:1px solid #dfe9e1}.public-board-icon{width:38px;height:38px;display:grid;place-items:center;flex:0 0 auto;border-radius:9px;background:#eaf4ec;color:#27824f}.public-board-icon svg,.public-board-arrow svg{width:17px;height:17px}.public-board-item>div{flex:1}.public-board-item time{color:#77877c;font-size:10px}.public-board-item h2{margin:3px 0 5px;font-size:14px}.public-board-item h2 a{color:#20392a;text-decoration:none}.public-board-item p{margin:0;color:#63766a;font-size:11px;line-height:1.8}.public-board-arrow{color:#578065}.public-info-heading h1{font-size:26px}@media(max-width:550px){.public-info-heading h1{font-size:22px}.public-board-item{gap:10px;padding:15px 2px}.public-board-icon{width:32px;height:32px}}</style>
@endpush
