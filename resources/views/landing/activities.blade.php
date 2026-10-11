@extends('layouts.public')

@section('title', 'ภาพกิจกรรมและวิดีโอ | URU Coop-IS')

@section('content')
<header class="public-info-heading"><p class="public-home-eyebrow">ภาพกิจกรรมและสื่อ</p><h1>กิจกรรมสหกิจศึกษา</h1><p>ข่าวสาร ภาพกิจกรรม และวิดีโอแนะนำจากศูนย์สหกิจศึกษา</p></header>
@if($videoId)
    <section class="public-activity-video"><h2>วิดีโอเกี่ยวกับสหกิจศึกษา</h2><div><iframe src="https://www.youtube-nocookie.com/embed/{{ $videoId }}" title="วิดีโอเกี่ยวกับสหกิจศึกษา" loading="lazy" referrerpolicy="strict-origin-when-cross-origin" allow="accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe></div></section>
@endif
<section class="public-activity-gallery"><div class="public-info-section-heading"><div><h2>ภาพกิจกรรม</h2><p>ภาพจากข่าวประชาสัมพันธ์ที่เผยแพร่แล้ว</p></div></div>
    <div class="public-activity-grid">
        @forelse($announcements as $announcement)
            <a class="public-activity-card" href="{{ route('news.show', $announcement) }}"><img src="{{ route('public.news-image', $announcement) }}" alt="{{ $announcement->title }}" loading="lazy"><span><time>{{ optional($announcement->published_at)->format('d/m/Y') }}</time><strong>{{ $announcement->title }}</strong></span></a>
        @empty
            <div class="empty-state"><span class="empty-icon"><i data-lucide="images"></i></span><strong>ยังไม่มีภาพกิจกรรมที่เผยแพร่</strong><span>ภาพที่แนบกับข่าวประชาสัมพันธ์จะปรากฏในหน้านี้</span></div>
        @endforelse
    </div>
    @if($announcements->hasPages())<div class="mt-3">{{ $announcements->links('pagination::bootstrap-4') }}</div>@endif
</section>
@endsection

@push('styles')
<style>.public-info-heading{max-width:850px;margin:0 auto 25px}.public-info-heading h1{margin:0;color:#183528;font-size:28px}.public-info-heading>p:last-child{margin:8px 0 0;color:#64766a;font-size:13px}.public-activity-video{max-width:860px;margin:0 auto 30px}.public-activity-video h2,.public-info-section-heading h2{color:#20392a;font-size:18px}.public-activity-video>div{overflow:hidden;aspect-ratio:16/9;background:#14241a;border-radius:12px}.public-activity-video iframe{width:100%;height:100%;border:0}.public-activity-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px}.public-activity-card{overflow:hidden;background:#fff;border:1px solid #dfe9e1;border-radius:11px;color:#20392a;text-decoration:none}.public-activity-card img{display:block;width:100%;aspect-ratio:4/3;object-fit:cover;background:#e8eee9}.public-activity-card>span{display:block;padding:12px 14px}.public-activity-card time,.public-activity-card strong{display:block}.public-activity-card time{margin-bottom:4px;color:#748677;font-size:10px}.public-activity-card strong{font-size:12px;line-height:1.6}.public-info-section-heading p{margin:3px 0;color:#748677;font-size:11px}@media(max-width:700px){.public-activity-grid{grid-template-columns:repeat(2,minmax(0,1fr)}}@media(max-width:450px){.public-activity-grid{grid-template-columns:1fr}.public-info-heading h1{font-size:24px}}</style>
@endpush
