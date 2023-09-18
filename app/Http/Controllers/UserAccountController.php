<?php

namespace App\Http\Controllers;

use App\Models\Gender;
use App\Models\JobModality;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Storage;

class UserAccountController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $jobModalities = JobModality::all();
        $genders = Gender::all();
        $skills = Skill::all();
        $userSkills = $user->skill()->get();

        return inertia('UserAccount/Index', [
            'jobModalities' => $jobModalities,
            'genders' => $genders,
            'skills' => $skills,
            'userSkills' => $userSkills
        ]);
    }

    public function create()
    {
        return inertia('UserAccount/Create');
    }

    public function store(Request $request)
    {
        $name = $request->input('name');
        $lastName = $request->input('last_name');
        $baseUsername = $name . $lastName;
        $username = $baseUsername . rand(1, 9999);

        while (User::where('username', $username)->exists()) {
            $username = $baseUsername . rand(1, 9999);
        }

        $validatedData = $request->validate(
            [
                'name' => 'required|string',
                'last_name' => 'required|string',
                'username' => 'string|unique:users',
                'slug' => 'string|unique:users',
                'email' => 'required|string|email|unique:users',
                'password' => 'required|min:8|confirmed|string'
            ]
        );

        $username = strtolower(str_replace(' ', '', $username));
        $slug = $username;

        $user = User::make($validatedData);
        $user->username = $username;
        $user->slug = $slug;
        $user->save();

        Auth::login($user);

        return redirect('/');
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validatedData = $request->validate([
            'name' => 'required|string|min:3',
            'last_name' => 'required|string|min:3',
            'username' => 'string|min:3|unique:users,username,' . $user->id,
            'slug' => 'required|min:3|alpha_dash|unique:users,slug,' . $user->id,
            'email' => 'required|string|email|unique:users,email,' . $user->id,
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'birthdate' => 'required|date|before:15 years ago',
            'about_me' => 'nullable|max:2000|string',
            'job_title' => 'nullable|max:100',
            'phone' => 'nullable|max:10|min:10',
            'linkedin' => 'nullable|url',
            'cv' => 'nullable|max:2048',
            'job_modality_id' => 'nullable|exists:job_modalities,id',
            'gender_id' => 'nullable|exists:genders,id',
            'looking_for_job' => 'nullable|boolean'
        ]);

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $filename = $file->getClientOriginalName();

            $filePath = 'avatars/' . $user->id . '/' . $filename;
            Storage::disk('s3')->put($filePath, file_get_contents($file), 'public');

            $user->avatar = Storage::disk('s3')->url($filePath);
        }

        if ($request->hasFile('cv')) {
            $file = $request->file('cv');
            $filename = $file->getClientOriginalName();

            $filePath = 'cvs/' . $user->id . '/' . $filename;
            Storage::disk('s3')->put($filePath, file_get_contents($file), 'public');

            $user->cv = Storage::disk('s3')->url($filePath);
        }

        $user->name = $validatedData['name'];
        $user->last_name = $validatedData['last_name'];
        $user->username = $validatedData['username'];
        $user->email = $validatedData['email'];
        $user->birthdate = $validatedData['birthdate'];
        $user->about_me = $validatedData['about_me'];
        $user->slug = $validatedData['slug'];
        $user->job_title = $validatedData['job_title'];
        $user->phone = $validatedData['phone'];
        $user->linkedin = $validatedData['linkedin'];
        $user->job_modality_id = $validatedData['job_modality_id'];
        $user->gender_id = $validatedData['gender_id'];
        $user->looking_for_job = $validatedData['looking_for_job'];

        $user->save();

        return redirect()->back()->with('success', 'Profile updated');
    }

    public function deleteItem($itemToDelete)
    {
        $user = Auth::user();

        if ($itemToDelete == 'avatar') {
            $avatarUrl = $user->avatar;

            if ($avatarUrl) {
                $fileParts = pathinfo($avatarUrl);
                $filename = $fileParts['basename'];
                Storage::disk('s3')->delete('avatars/' . $user->id . '/' . $filename);
            }

            $user->avatar = null;
            $user->save();

            return redirect()->route('user-account.index')->with('success', 'Image deleted');
        }

        if ($itemToDelete == 'cv') {
            $cvUrl = $user->cv;

            if ($cvUrl) {
                $fileParts = pathinfo($cvUrl);
                $filename = $fileParts['basename'];
                Storage::disk('s3')->delete('cvs/' . $user->id . '/' . $filename);
            }

            $user->cv = null;
            $user->save();

            return redirect()->route('user-account.index')->with('success', 'Resume deleted');
        }
    }
}
