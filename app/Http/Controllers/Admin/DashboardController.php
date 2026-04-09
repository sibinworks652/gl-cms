<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Gallery\Models\GalleryAlbum;

class DashboardController extends Controller
{
    public function index()
    {
        if(class_exists(GalleryAlbum::class)) {
            $galleryCount = GalleryAlbum::where('is_active', true)->count();
            $galleryImageCount = GalleryAlbum::where('is_active', true)->withCount('images')->get()->sum('images_count');
        } else {
            $galleryCount = 0;
            $galleryImageCount = 0;
        }
        $galleryCount = GalleryAlbum::where('is_active', true)->count();
        $galleryImageCount = GalleryAlbum::where('is_active', true)->withCount('images')->get()->sum('images_count');
        // dd($galleryCount, $galleryImageCount);
        return view('admin.dashboard', compact('galleryCount', 'galleryImageCount'));
    }
}
