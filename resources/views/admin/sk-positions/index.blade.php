@extends('layouts.app')

@section('title', 'ปรับตำแหน่งแบบฟอร์ม สก. | URU Coop-IS')
@section('breadcrumb', 'ปรับตำแหน่งแบบฟอร์ม สก.')

@section('content')
<header class="page-heading">
    <div><p class="page-eyebrow">จัดการแบบฟอร์ม · ผู้ดูแลระบบ</p><h1>ปรับตำแหน่งแบบฟอร์ม สก.</h1><p class="page-subtitle">จัดช่องให้ตรงกับแบบ PDF แล้วบันทึก ตำแหน่งนี้จะใช้กับหน้ากรอกข้อมูลและ PDF ที่ส่งออก</p></div>
    <a class="btn btn-outline-secondary" href="{{ route('admin.appearance.edit') }}">กลับหน้าจัดการเว็บไซต์</a>
</header>

<div class="row g-3">
    @foreach($items as $item)
        <div class="col-md-6 col-xl-4">
            <section class="card h-100">
                <div class="card-body d-flex flex-column">
                    <h2 class="h5">{{ strtoupper(str_replace('-', '.', $item['type'])) }}</h2>
                    <p class="text-muted flex-grow-1">{{ $item['title'] }}</p>
                    <p class="small"><span class="badge {{ $item['positions_configured'] ? 'bg-success' : 'bg-warning text-dark' }}">{{ $item['position_state'] }}</span></p>
                    <a class="btn btn-outline-primary align-self-start" href="{{ route('admin.sk-positions.edit', $item['type']) }}">ปรับตำแหน่ง</a>
                </div>
            </section>
        </div>
    @endforeach
</div>
@endsection
