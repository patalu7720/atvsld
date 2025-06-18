<?php

namespace App\Http\Controllers;

use App\Mail\SendSolution;
use App\Models\Area;
use App\Models\Atvsld;
use App\Models\Calendar;
use App\Models\CalendarTeam;
use App\Models\ImagesAtvsld;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;

class AtvsldController extends Controller
{
    public function getAreas(Request $request)
    {
        $areas = Area::all()->map(function ($area) {
            return [
                'id' => $area->id,
                'name' => $area->name,
                'plant' => $area->plant,
            ];
        });

        return response()->json($areas);
    }

    public function getEmails()
    {
        $emails = User::where('solution_permission', '1')
            ->get()
            ->map(function ($user) {
                return [
                    'name' => $user->name,
                    'email' => $user->email,
                ];
            });

        return response()->json($emails);
    }

    public function evaluate(Request $request)
    {
        $user = User::findOrFail($request->user_id);
        $now = Carbon::now();

        // 1. Lấy calendar hiện tại
        $calendar = Calendar::where('month', $now->month)
            ->where('year', $now->year)
            ->first();

        // 2. Lấy các team của user
        $userTeamIds = $user->teams->pluck('id');

        // 3. Lấy các plant của team user
        $plantsOfUsers = $user->teams->map(function ($team) {
            return [
                'plant' => $team->plant,
                'plant_description' => $team->plant_description,
            ];
        })
            ->unique(function ($item) {
                return $item['plant'] . '-' . $item['plant_description'];
            })
            ->values();

        // 4. Lấy các team đang làm việc mà user đang thuộc về
        $activeTeamIds = $calendar->teams()
            ->whereIn('teams.id', $userTeamIds)
            ->pluck('teams.id');

        // if ($activeTeamIds->isEmpty()) {
        //     // Không thuộc team nào hoạt động trong tháng → chỉ thấy job của chính mình
        //     return Atvsld::with('areas', 'images', 'users')
        //         ->where('user_id', $user->id)
        //         ->get();
        // }

        // 5. Từ bảng calendar_team, lấy danh sách khu vực team đó đang phụ trách trong tháng này
        $areaIds = DB::table('calendar_team')
            ->where('calendar_id', $calendar->id)
            ->whereIn('team_id', $userTeamIds)
            ->pluck('area_id')
            ->unique();

        $areas = Area::whereIn('id', $areaIds)->get();

        // 5. Truy vấn Atvsld đúng team đang làm việc
        $atvslds = Atvsld::with('areas', 'images', 'users')
            ->whereIn('team_id', $activeTeamIds)
            ->whereBetween('created_at', [
                Carbon::now()->startOfWeek()->format('Y-m-d') . ' 00:00:00',
                Carbon::now()->endOfWeek()->format('Y-m-d') . ' 23:59:59'
            ])
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'plantsOfUsers' => $plantsOfUsers,
            'areas' => $areas,
            'atvslds' => $atvslds
        ]);
    }

    public function store(Request $request)
    {
        $teamId = CalendarTeam::whereHas('calendar', fn($q) => $q
            ->where('month', now()->month)
            ->where('year', now()->year))
            ->where('area_id', $request->area_id)
            ->whereHas('team.users', fn($q) => $q->where('users.id', $request->user_id))
            ->value('team_id');

        $atvsld = new Atvsld();
        $atvsld->user_id = $request->user_id;
        $atvsld->area_id = $request->area_id;
        $atvsld->team_id = $teamId;
        $atvsld->problem_detected = $request->problem_detected;
        $atvsld->risk = $request->risk;
        $atvsld->solution_email = $request->solution_user;
        $atvsld->save();

        $atvsldID = $atvsld->id;

        if ($request->hasFile('images')) {

            foreach ($request->file('images') as $file) {
                $path = $file->store(
                    'uploads/atvsld/' . $atvsldID . '_' . now()->format('Y_m'),
                    'public'
                );

                ImagesAtvsld::create([
                    'atvsld_id' => $atvsldID,
                    'image_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                ]);
            }
        }

        Mail::to($request->solution_user)->queue(new SendSolution());

        return response()->json([
            'message' => 'ATVSLD created successfully',
            'atvsld' => $atvsld,
        ], 201);
    }

    public function update($id, Request $request)
    {
        $atvsld = Atvsld::findOrFail($id);
        $atvsld->area_id = $request->area_id;
        $atvsld->problem_detected = $request->problem_detected;
        $atvsld->risk = $request->risk;
        $atvsld->save();

        $keptImageIds = json_decode($request->input('kept_image_ids'), true) ?? [];

        // Lấy tất cả ảnh hiện có
        $currentImages = $atvsld->images;

        // Lọc ra các ảnh bị xóa (không nằm trong danh sách giữ lại)
        $imagesToDelete = $currentImages->whereNotIn('id', $keptImageIds);

        // Xóa file và bản ghi
        foreach ($imagesToDelete as $image) {
            if ($image->image_path && Storage::disk('public')->exists($image->image_path)) {
                Storage::disk('public')->delete($image->image_path);
            }
            $image->delete();
        }

        // Lưu lại ảnh mới
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $file->store(
                    'uploads/atvsld/' . $id . '_' . now()->format('Y_m'),
                    'public'
                );

                ImagesAtvsld::create([
                    'atvsld_id' => $id,
                    'image_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                ]);
            }
        }

        return response()->json([
            'message' => 'ATVSLD updated successfully',
            'atvsld' => $atvsld,
        ], 200);
    }

    public function destroy($id)
    {
        $atvsld = Atvsld::findOrFail($id);

        // Xóa file vật lý trước khi xóa bản ghi ảnh
        foreach ($atvsld->images as $image) {
            if ($image->image_path && Storage::disk('public')->exists($image->image_path)) {
                Storage::disk('public')->delete($image->image_path);
            }
        }

        $atvsld->images()->delete();
        $atvsld->delete();

        return response()->json(['message' => 'ATVSLD deleted successfully'], 200);
    }

    public function reports(Request $request)
    {
        $query = Atvsld::with(['users', 'areas', 'images']);

        if ($request->has('start') && $request->has('end')) {
            $query->whereBetween('created_at', [
                $request->input('start') . ' 00:00:00',
                $request->input('end') . ' 23:59:59'
            ]);
        }

        $atvslds = $query->get();

        return response()->json($atvslds);
    }

    public function getSolution(Request $request)
    {
        $atvslds = Atvsld::with(['users', 'areas', 'images'])
        ->whereBetween('created_at', [
                Carbon::now()->startOfWeek()->format('Y-m-d') . ' 00:00:00',
                Carbon::now()->endOfWeek()->format('Y-m-d') . ' 23:59:59'
            ])
        ->where('solution_email', $request->user_email)
        ->whereNull('solution')
        ->get();

        return response()->json($atvslds);
    }

    public function submitSolution(Request $request){

        $atvsld = Atvsld::findOrFail($request->id);
        $atvsld->solution = $request->solution;
        $atvsld->start_date = $request->start;
        $atvsld->end_date = $request->end;
        $atvsld->save();

        return response()->json([
            'message' => 'Cập nhật thành công',
        ]);

    }

    public function scheduler(Request $request)
    {
        $year = Carbon::now()->year;

        $calendars = Calendar::where('year', $year)->with([
            'calendarTeams.team.users',
            'calendarTeams.area'
        ])->get();

        return response()->json([
            $year,
            $calendars
        ]);

        $teamData = [];

        foreach ($calendars as $calendar) {
            foreach ($calendar->calendarTeams as $calendarTeam) {
                $team = $calendarTeam->team;
                if (!isset($teamData[$team->id])) {
                    $teamData[$team->id] = [
                        'id' => $team->id,
                        'name' => $team->name,
                        'users' => $team->users->map(fn($u) => ['id' => $u->id, 'name' => $u->name])->toArray(),
                        'months' => [],
                    ];
                }

                $teamData[$team->id]['months'][$calendar->month] = [
                    'area' => ['id' => $calendarTeam->area->id, 'name' => $calendarTeam->area->name]
                ];
            }
        }

        return response()->json([
            'teamData' => array_values($teamData)
        ]);
    }
}
