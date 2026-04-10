<?php

namespace Modules\Team\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Modules\Team\Models\TeamDepartment;
use Modules\Team\Models\TeamMember;
use Modules\Team\Requests\TeamMemberRequest;
use Modules\Team\Services\TeamManager;

class TeamMemberController extends Controller
{
    public function __construct(protected TeamManager $manager)
    {
    }

    public function index(Request $request)
    {
        $members = TeamMember::query()
            ->with('department')
            ->when($request->filled('department'), fn ($query) => $query->where('department_id', $request->integer('department')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status') === 'active'))
            ->ordered()
            ->paginate(15)
            ->withQueryString();

        return view('team::admin.members.index', [
            'members' => $members,
            'departments' => TeamDepartment::query()->ordered()->get(),
        ]);
    }

    public function create()
    {
        return view('team::admin.members.form', [
            'member' => new TeamMember([
                'status' => true,
                'order' => (int) TeamMember::max('order') + 1,
                'social_links' => [],
            ]),
            'departments' => TeamDepartment::query()->ordered()->get(),
            'isEdit' => false,
        ]);
    }

    public function store(TeamMemberRequest $request)
    {
        $this->manager->createMember($request->validated(), $request->file('image'));

        return redirect()->route('admin.team-members.index')->with('success', 'Team member created successfully.');
    }

    public function edit(TeamMember $team_member)
    {
        return view('team::admin.members.form', [
            'member' => $team_member,
            'departments' => TeamDepartment::query()->ordered()->get(),
            'isEdit' => true,
        ]);
    }

    public function update(TeamMemberRequest $request, TeamMember $team_member)
    {
        $this->manager->updateMember($team_member, $request->validated(), $request->file('image'));

        return redirect()->route('admin.team-members.index')->with('success', 'Team member updated successfully.');
    }

    public function destroy(TeamMember $team_member)
    {
        $this->manager->deleteMember($team_member);

        return redirect()->route('admin.team-members.index')->with('success', 'Team member deleted successfully.');
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['integer', Rule::exists('team_members', 'id')],
        ]);

        DB::transaction(function () use ($validated) {
            foreach (array_values($validated['order']) as $index => $id) {
                TeamMember::query()->whereKey($id)->update(['order' => $index]);
            }
        });

        return response()->json(['message' => 'Member order saved.']);
    }
}
