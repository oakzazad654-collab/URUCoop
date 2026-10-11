@php
    $statusLabels = [
        'draft' => 'ฉบับร่าง',
        'pending_advisor' => 'รออาจารย์ที่ปรึกษา',
        'pending_officer' => 'รอเจ้าหน้าที่ศูนย์',
        'approved' => 'อนุมัติแล้ว',
        'rejected' => 'ไม่อนุมัติ',
        'revision_requested' => 'ส่งกลับแก้ไข',
    ];
    $statusClasses = [
        'draft' => 'bg-secondary',
        'pending_advisor' => 'bg-warning text-dark',
        'pending_officer' => 'bg-warning text-dark',
        'approved' => 'bg-success',
        'rejected' => 'bg-danger',
        'revision_requested' => 'bg-danger',
    ];
@endphp
<span class="badge {{ $statusClasses[$status] ?? 'bg-secondary' }}">{{ $statusLabels[$status] ?? $status }}</span>
