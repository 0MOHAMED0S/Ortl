<?php

namespace App\Http\Controllers\web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Track\StoreTrackRequest;
use App\Http\Requests\Admin\Track\UpdateTrackRequest;
use App\Models\Track;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TrackController extends Controller
{
public function index()
{
    try {
        $tracks = Track::latest()->get();

        return view('dashboard.tracks', compact('tracks'));

    } catch (\Throwable $e) {

        Log::error('Tracks Index Error', [
            'error' => $e->getMessage(),
        ]);

        return back()->with('error', 'حدث خطأ أثناء تحميل المسارات');
    }
}

public function store(StoreTrackRequest $request)
{
    try {
        DB::beginTransaction();

        Track::create($request->validated());

        DB::commit();

        return back()->with('success', 'تم إضافة المسار بنجاح');

    } catch (\Throwable $e) {

        DB::rollBack();

        Log::error('Track Store Error', [
            'data'  => $request->validated(),
            'error' => $e->getMessage(),
        ]);

        return back()->with('error', 'حدث خطأ أثناء إضافة المسار');
    }
}


public function update(UpdateTrackRequest $request, Track $track)
{
    try {
        DB::beginTransaction();

        $track->update($request->validated());

        DB::commit();

        return back()->with('success', 'تم تعديل المسار بنجاح');

    } catch (\Throwable $e) {

        DB::rollBack();

        Log::error('Track Update Error', [
            'track_id' => $track->id,
            'data'     => $request->validated(),
            'error'    => $e->getMessage(),
        ]);

        return back()->with('error', 'حدث خطأ أثناء تعديل المسار');
    }
}

}
