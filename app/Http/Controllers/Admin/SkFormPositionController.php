<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WebsiteSetting;
use App\Services\SkFormCatalog;
use Illuminate\Http\Request;

class SkFormPositionController extends Controller
{
    private $skForms;

    public function __construct(SkFormCatalog $skForms)
    {
        $this->skForms = $skForms;
    }

    public function index()
    {
        $forms = $this->skForms->all();
        $items = [];
        foreach ($forms as $type => $definition) {
            $items[] = [
                'type' => $type,
                'title' => $definition['title'],
                'source' => $definition['source'],
                'position_state' => $type === 'sk-01'
                    ? 'ตรวจตำแหน่งเริ่มต้น'
                    : (!empty($definition['positions_configured']) ? 'บันทึกตำแหน่งแล้ว' : 'รอจัดตำแหน่ง'),
                'positions_configured' => !empty($definition['positions_configured']),
            ];
        }

        return view('admin.sk-positions.index', compact('items'));
    }

    public function edit($type = 'sk-01')
    {
        $forms = $this->skForms->all();
        abort_unless(isset($forms[$type]), 404);
        $definition = $forms[$type];
        $positions = json_decode((string) WebsiteSetting::where('key', $this->positionKey($type))->value('value'), true);
        if (!is_array($positions)) {
            $positions = [];
        }

        $fields = $this->editorFields($type, $definition, $positions);

        $sourceUrl = route('public.forms.download', ['file' => $definition['source']]).'?inline=1';

        return view('admin.sk01-positions.edit', compact('fields', 'sourceUrl', 'type', 'forms', 'definition'));
    }

    public function update(Request $request, $type = 'sk-01')
    {
        $forms = $this->skForms->all();
        abort_unless(isset($forms[$type]), 404);
        $validated = $request->validate([
            'positions' => 'required|array',
            'positions.*' => 'required|array:left,top,width,page,row_step',
            'positions.*.left' => 'required|numeric|between:0,100',
            'positions.*.top' => 'required|numeric|between:0,100',
            'positions.*.width' => 'required|numeric|gt:0|lte:100',
            'positions.*.page' => 'nullable|integer|min:1|max:100',
            'positions.*.row_step' => 'required|numeric|gte:0|lte:20',
        ]);

        $definition = $forms[$type];
        $knownFields = [];
        foreach ($this->editorFields($type, $definition, []) as $field) {
            $knownFields[$field['key']] = [
                'type' => $field['type'],
                'layout' => $field['position']['layout'] ?? 'horizontal',
                'mark_option' => !empty($field['position']['mark_option']),
                'option_count' => $this->fieldOptionCount($field),
            ];
        }

        $positions = [];
        foreach ($validated['positions'] as $key => $position) {
            abort_unless(isset($knownFields[$key]), 422, 'พบช่องแบบฟอร์มที่ไม่รู้จัก');
            $field = $knownFields[$key];
            $isMarkerField = in_array($field['type'], ['radio', 'checkboxes', 'weeks', 'months'], true)
                || ($field['type'] === 'select' && $field['mark_option']);
            $lastOptionOffset = $isMarkerField && $field['layout'] === 'horizontal'
                ? max($field['option_count'] - 1, 0) * (float) $position['width']
                : 0;
            $lastVerticalOffset = $isMarkerField && $field['layout'] === 'vertical'
                ? max($field['option_count'] - 1, 0) * (float) $position['width']
                : ($isMarkerField ? 0 : 2.2);
            abort_if((float) $position['left'] + $lastOptionOffset > 100
                || (float) $position['top'] + $lastVerticalOffset > 100
                || (float) $position['top'] + (float) $position['row_step'] > 100,
                422,
                'ตำแหน่งช่องกรอกเกินขอบเอกสาร');
            $positions[$key] = [
                'left' => (float) $position['left'],
                'top' => (float) $position['top'],
                'width' => (float) $position['width'],
                'page' => (int) ($position['page'] ?? 1),
                'row_step' => (float) $position['row_step'],
            ];
        }

        abort_unless(count($positions) === count($knownFields), 422, 'ข้อมูลตำแหน่งช่องกรอกไม่ครบถ้วน');

        WebsiteSetting::updateOrCreate(
            ['key' => $this->positionKey($type)],
            ['value' => json_encode($positions, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)]
        );

        return $this->editorRedirect($type)->with('success', 'บันทึกตำแหน่งช่องกรอก '.strtoupper(str_replace('-', '.', $type)).' แล้ว');
    }

    public function reset($type = 'sk-01')
    {
        abort_unless(isset($this->skForms->all()[$type]), 404);
        WebsiteSetting::where('key', $this->positionKey($type))->delete();

        return $this->editorRedirect($type)->with('success', 'คืนค่าตำแหน่งช่องกรอก '.strtoupper(str_replace('-', '.', $type)).' เป็นค่าเริ่มต้นแล้ว');
    }

    private function editorRedirect($type)
    {
        if (request()->routeIs('admin.sk01-positions.*')) {
            return redirect()->route('admin.sk01-positions.edit');
        }

        return redirect()->route('admin.sk-positions.edit', $type);
    }

    private function positionKey($type)
    {
        return 'sk_'.str_replace(['-', '.'], '_', substr($type, 3)).'_field_positions';
    }

    private function fieldOptionCount($field)
    {
        if (($field['type'] ?? '') === 'weeks') {
            return 16;
        }
        if (($field['type'] ?? '') === 'months') {
            return 4;
        }
        return count($field['options'] ?? []);
    }

    private function defaultPosition($index, $field)
    {
        $columns = 4;
        $column = $index % $columns;
        $row = intdiv($index, $columns);

        return [
            'left' => 7 + ($column * 23),
            'top' => min(8 + ($row * 8), 92),
            'width' => ($field['type'] ?? '') === 'weeks' ? 1.5
                : (in_array(($field['type'] ?? ''), ['radio', 'checkboxes', 'months'], true) ? 4 : 19),
            'page' => 1,
            'row_step' => 0,
        ];
    }

    private function editorFields($type, $definition, $positions)
    {
        $fields = [];
        $index = 0;
        foreach ($definition['sections'] as $sectionIndex => $section) {
            foreach ($section['fields'] as $field) {
                if ($type === 'sk-01' && empty($field['position'])) {
                    continue;
                }
                $key = $type === 'sk-01' ? $field['key'] : 'field_'.$field['key'];
                $defaultPosition = $field['position'] ?? $this->defaultPosition($index, $field);
                $legacyKey = 'field_'.$index;
                if ($type === 'sk-10' && $field['key'] === 'approver_name') {
                    $legacyKey = 'field_10';
                }
                $savedPosition = $positions[$key] ?? ($positions[$legacyKey] ?? []);
                $fieldPosition = array_merge($defaultPosition, is_array($savedPosition) ? $savedPosition : []);
                $page = filter_var($fieldPosition['page'] ?? null, FILTER_VALIDATE_INT);
                if ($page === false || $page === null || $page < 1) {
                    $fieldPosition['page'] = 1;
                }
                $fields[] = [
                    'key' => $key,
                    'label' => $field['label'],
                    'type' => $field['type'] ?? 'text',
                    'options' => $field['options'] ?? [],
                    'option_count' => $this->fieldOptionCount($field),
                    'position' => $fieldPosition,
                    'auto_position' => $type !== 'sk-01' && empty($savedPosition) && empty($field['position']),
                ];
                $index++;
            }
        }

        if ($type !== 'sk-01') {
            foreach ($definition['repeaters'] ?? [] as $repeaterIndex => $repeater) {
                foreach ($repeater['fields'] as $fieldIndex => $field) {
                    $key = 'repeater_'.$repeaterIndex.'_field_'.$fieldIndex;
                    $savedPosition = $positions[$key] ?? [];
                    $defaultPosition = $this->defaultPosition($index, $field);
                    $defaultPosition['row_step'] = 2.8;
                    $fieldPosition = array_merge($defaultPosition, is_array($savedPosition) ? $savedPosition : []);
                    $page = filter_var($fieldPosition['page'] ?? null, FILTER_VALIDATE_INT);
                    if ($page === false || $page === null || $page < 1) {
                        $fieldPosition['page'] = 1;
                    }
                    $fields[] = [
                        'key' => $key,
                        'label' => $repeater['label'].' — '.$field['label'],
                        'type' => $field['type'] ?? 'text',
                        'options' => $field['options'] ?? [],
                        'option_count' => $this->fieldOptionCount($field),
                        'position' => $fieldPosition,
                        'auto_position' => empty($savedPosition) && empty($field['position']),
                    ];
                    $index++;
                }
            }
        }

        return $fields;
    }
}
