@extends('layouts.app')

@section('title', 'ประวัติระบบ | URU Coop-IS')
@section('breadcrumb', 'ประวัติระบบ')

@section('content')
<header class="page-heading"><div><p class="page-eyebrow">การตรวจสอบย้อนหลัง</p><h1>ประวัติการดำเนินการ</h1><p class="page-subtitle">บันทึกการส่ง อนุมัติ ส่งกลับแก้ไข และปฏิเสธเอกสารทั้งหมด</p></div></header>
<section class="card"><div class="card-header bg-white"><h2 class="h6 mb-0">Audit log</h2></div><div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>เวลา</th><th>เอกสาร</th><th>การดำเนินการ</th><th>ผู้ดำเนินการ</th><th>ความเห็น</th></tr></thead><tbody>
@forelse($histories as $history)<tr data-search-row><td class="text-nowrap">{{ $history->created_at->format('d/m/Y H:i:s') }}</td><td>{{ strtoupper(str_replace('-', ' ', $history->document_type)) }} #{{ $history->document_id }}</td><td><span class="role-tag">{{ str_replace('_', ' ', $history->action_type) }}</span></td><td>{{ $history->actor->name ?? 'บัญชีที่ถูกลบ' }}</td><td>{{ $history->comment ?: '—' }}</td></tr>@empty<tr><td colspan="5"><div class="empty-state"><span class="empty-icon"><i data-lucide="history"></i></span><strong>ยังไม่มีประวัติ</strong></div></td></tr>@endforelse
</tbody></table></div><div class="card-footer bg-white">{{ $histories->links() }}</div></section>
@endsection
