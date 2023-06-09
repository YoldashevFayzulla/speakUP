<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UserCantroller extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $managers = User::role('manager')->orderby('created_at')->get();
        $teachers= User::role('teacher')->orderby('created_at')->get();
        return view('admin.teachers.index', compact('managers','teachers'));
    }
    /**
     * Display a listing of the resource.
     */

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.teachers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'tel' => 'required|string',
            'password' => 'required|string|min:8',
            'email' => 'required|email',
        ]);
        if ($request->hasFile('image')){
            $name = $request->file('image')->getClientOriginalName();
            $path = $request->file('image')->storeAs('Photo',$name);
        }

        User::create([
            'name'=> $request->name,
            'email'=> $request->email,
            'email_verified_at' => now(),
            'password'=> bcrypt($request->password),
            'tel'=> $request->tel,
            'desc'=> $request->desc,
            'image'=> $path ?? null,
        ])->assignRole('manager');

        return redirect()->route('dashboard.index')->with('success','User created');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $u_id)
    {
            $user = User::find($u_id);

            if(!$user->hasRole('admin')) {
                return view('admin.teachers.edit', compact('user'));
            }
            else
                return abort(419);
//        }
//        return redirect()->back();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string',
            'tel' => 'required|string',
//            'password' => 'required|string|min:8',
            'email' => 'required|email',
        ]);

        $user=User::find($id);

        if ($request->hasFile('image')){
            if (isset($user->image)){
                Storage::delete($user->image);
            }
            $name = $request->file('image')->getClientOriginalName();
            $path = $request->file('image')->storeAs('Photo',$name);
        }


        $user->update([
            'name' => $request->name,
            'tel' => $request->tel,
            'password' => bcrypt($request->password) ??  $user->password,
            'email' => $request->email,
            'image' => $path ?? $user->image,
        ]);
        return redirect()->route('dashboard.index')->with('success','Data updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
//        dd('salom');
        if (isset($user->image)) {
            Storage::delete($user->image);
        }
        $user->delete();
        return redirect()->back()->with('success','User deleted');
    }
}
