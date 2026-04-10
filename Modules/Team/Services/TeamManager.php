<?php

namespace Modules\Team\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Team\Models\TeamDepartment;
use Modules\Team\Models\TeamMember;

class TeamManager
{
    public function departments(bool $activeOnly = true): Collection
    {
        return TeamDepartment::query()
            ->when($activeOnly, fn ($query) => $query->active())
            ->withCount(['members' => fn ($query) => $query->active()])
            ->ordered()
            ->get();
    }

    public function frontendMembers(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        return TeamMember::query()
            ->with('department')
            ->active()
            ->when($filters['department'] ?? null, function ($query, string $departmentSlug) {
                $query->whereHas('department', fn ($departmentQuery) => $departmentQuery->where('slug', $departmentSlug));
            })
            ->ordered()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function featured(int $limit = 6): Collection
    {
        return TeamMember::query()
            ->with('department')
            ->active()
            ->featured()
            ->ordered()
            ->limit($limit)
            ->get();
    }

    public function findActiveBySlug(string $slug): TeamMember
    {
        return TeamMember::query()
            ->with('department')
            ->active()
            ->where('slug', $slug)
            ->firstOrFail();
    }

    public function related(TeamMember $member, int $limit = 3): Collection
    {
        return TeamMember::query()
            ->with('department')
            ->active()
            ->whereKeyNot($member->id)
            ->when($member->department_id, fn ($query) => $query->where('department_id', $member->department_id))
            ->ordered()
            ->limit($limit)
            ->get();
    }

    public function createMember(array $data, ?UploadedFile $image = null): TeamMember
    {
        return DB::transaction(function () use ($data, $image) {
            $member = TeamMember::create($this->memberPayload($data));

            if ($image) {
                $member->update([
                    'image' => $image->storeAs('team', $this->datedOriginalFilename($image), 'public'),
                ]);
            }

            return $member;
        }, 3);
    }

    public function updateMember(TeamMember $member, array $data, ?UploadedFile $image = null): TeamMember
    {
        return DB::transaction(function () use ($member, $data, $image) {
            $member->update($this->memberPayload($data, $member));

            if ($image) {
                if ($member->image) {
                    Storage::disk('public')->delete($member->image);
                }

                $member->update([
                    'image' => $image->storeAs('team', $this->datedOriginalFilename($image), 'public'),
                ]);
            }

            return $member->fresh('department');
        }, 3);
    }

    public function deleteMember(TeamMember $member): void
    {
        if ($member->image) {
            Storage::disk('public')->delete($member->image);
        }

        $member->delete();
    }

    public function createDepartment(array $data): TeamDepartment
    {
        return TeamDepartment::create($this->departmentPayload($data));
    }

    public function updateDepartment(TeamDepartment $department, array $data): TeamDepartment
    {
        $department->update($this->departmentPayload($data, $department));

        return $department->fresh();
    }

    protected function memberPayload(array $data, ?TeamMember $member = null): array
    {
        return [
            'department_id' => $data['department_id'] ?? null,
            'name' => $data['name'],
            'slug' => $this->uniqueSlug(TeamMember::class, $data['slug'] ?? $data['name'], $member?->id),
            'designation' => $data['designation'],
            'short_bio' => $data['short_bio'] ?? null,
            'description' => $data['description'] ?? null,
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'social_links' => collect($data['social_links'] ?? [])->map(fn ($value) => is_string($value) ? trim($value) : $value)->filter()->all(),
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'is_featured' => (bool) ($data['is_featured'] ?? false),
            'status' => (bool) ($data['status'] ?? false),
            'order' => $data['order'] ?? $member?->order ?? ((int) TeamMember::max('order') + 1),
        ];
    }

    protected function departmentPayload(array $data, ?TeamDepartment $department = null): array
    {
        return [
            'name' => $data['name'],
            'slug' => $this->uniqueSlug(TeamDepartment::class, $data['slug'] ?? $data['name'], $department?->id),
            'order' => $data['order'] ?? $department?->order ?? ((int) TeamDepartment::max('order') + 1),
            'status' => (bool) ($data['status'] ?? false),
        ];
    }

    protected function uniqueSlug(string $modelClass, string $source, ?int $ignoreId = null): string
    {
        $base = Str::slug($source) ?: Str::random(8);
        $slug = $base;
        $counter = 2;

        while ($modelClass::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists()) {
            $slug = $base . '-' . $counter++;
        }

        return $slug;
    }

    protected function datedOriginalFilename(UploadedFile $file): string
    {
        $name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();
        $safeName = Str::slug($name) ?: 'team-member';

        return $safeName . '-' . now()->format('Y-m-d-His') . ($extension ? '.' . strtolower($extension) : '');
    }
}
