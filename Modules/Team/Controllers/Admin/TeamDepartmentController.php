<?php

namespace Modules\Team\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Modules\Team\Models\TeamDepartment;
use Modules\Team\Requests\TeamDepartmentRequest;
use Modules\Team\Services\TeamManager;

class TeamDepartmentController extends Controller
{
    public function __construct(protected TeamManager $manager)
    {
    }

    public function index()
    {
        return view('team::admin.departments.index', [
            'departments' => TeamDepartment::query()->withCount('members')->ordered()->paginate(15),
        ]);
    }

    public function create()
    {
        return view('team::admin.departments.form', [
            'department' => new TeamDepartment([
                'status' => true,
                'order' => (int) TeamDepartment::max('order') + 1,
            ]),
            'isEdit' => false,
        ]);
    }

    public function store(TeamDepartmentRequest $request)
    {
        $department = $this->manager->createDepartment($request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Department created successfully.',
                'department' => [
                    'id' => $department->id,
                    'name' => $department->name,
                ],
            ], 201);
        }

        return redirect()->route('admin.team-departments.index')->with('success', 'Department created successfully.');
    }

    public function edit(TeamDepartment $team_department)
    {
        return view('team::admin.departments.form', [
            'department' => $team_department,
            'isEdit' => true,
        ]);
    }

    public function update(TeamDepartmentRequest $request, TeamDepartment $team_department)
    {
        $this->manager->updateDepartment($team_department, $request->validated());

        return redirect()->route('admin.team-departments.index')->with('success', 'Department updated successfully.');
    }

    public function destroy(TeamDepartment $team_department)
    {
        $team_department->delete();

        return redirect()->route('admin.team-departments.index')->with('success', 'Department deleted successfully.');
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['integer', Rule::exists('team_departments', 'id')],
        ]);

        DB::transaction(function () use ($validated) {
            foreach (array_values($validated['order']) as $index => $id) {
                TeamDepartment::query()->whereKey($id)->update(['order' => $index]);
            }
        });

        return response()->json(['message' => 'Department order saved.']);
    }
}
