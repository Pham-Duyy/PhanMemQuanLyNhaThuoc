<?php

namespace App\Http\Controllers;

use App\Models\MedicineCategory;
use Illuminate\Http\Request;

class MedicineCategoryController extends Controller
{
    public function index()
    {
        $categories = MedicineCategory::withCount('medicines')
            ->ordered()->paginate(20);
        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        return view('categories.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'nullable|string|max:20',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $exists = MedicineCategory::where('pharmacy_id', auth()->user()->pharmacy_id)
            ->where('name', $data['name'])->exists();

        if ($exists) {
            return back()->withInput()
                ->withErrors(['name' => 'Tên nhóm thuốc đã tồn tại.']);
        }

        MedicineCategory::create(array_merge($data, [
            'pharmacy_id' => auth()->user()->pharmacy_id,
            'is_active' => true,
            'sort_order' => $data['sort_order'] ?? 0,
        ]));

        return redirect()->route('categories.index')
            ->with('success', 'Thêm nhóm thuốc "' . $data['name'] . '" thành công!');
    }

    public function edit(MedicineCategory $category)
    {
        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, MedicineCategory $category)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'nullable|string|max:20',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $category->update($data);
        return redirect()->route('categories.index')
            ->with('success', 'Cập nhật nhóm thuốc thành công!');
    }

    public function destroy(MedicineCategory $category)
    {
        if ($category->medicines()->exists()) {
            return back()->with(
                'error',
                'Không thể xóa nhóm đang có thuốc. Hãy chuyển thuốc sang nhóm khác trước.'
            );
        }
        $category->delete();
        return back()->with('success', 'Đã xóa nhóm thuốc.');
    }
}