<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class AdminProfileController extends Controller
{
    public function index()
    {
        return view('admin.profile');
    }

    public function profile_submit(Request $request)
    {
        $admin_data = Admin::where('email', Auth::guard('admin')->user()->email)->first();

        // Basic validation
        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email'
        ]);

        // Handle password change if provided
        if ($request->filled('password')) {
            $request->validate([
                'password'         => 'required|min:6',
                'retype_password'  => 'required|same:password'
            ]);
            $admin_data->password = Hash::make($request->password);
        }

        // Handle profile photo
        if ($request->hasFile('photo')) {
            $request->validate([
                'photo' => 'image|mimes:jpg,jpeg,png,gif|max:2048'
            ]);

            // Delete old photo if exists
            if (!empty($admin_data->photo) && File::exists(public_path('uploads/'.$admin_data->photo))) {
                File::delete(public_path('uploads/'.$admin_data->photo));
            }

            // Create unique file name
            $ext = $request->file('photo')->extension();
            $final_name = 'admin_'.time().'.'.$ext;

            // Move file
            $request->file('photo')->move(public_path('uploads/'), $final_name);

            // Save filename in DB
            $admin_data->photo = $final_name;
        }

        // Update other fields
        $admin_data->name  = $request->name;
        $admin_data->email = $request->email;
        $admin_data->save();

        return redirect()->back()->with('success', 'Profile information saved successfully.');
    }
}
