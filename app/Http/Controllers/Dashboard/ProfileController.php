<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\Intl\Languages;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Intl\Locales;


class ProfileController extends Controller
{
    //
    public function edit()
    {
        $user = Auth::user();
        // dd($user->id);

        return view('dashboard.profile.edit', [
            'user' => $user,
            'countries' => Countries::getNames(),
            'locales' => Locales::getNames(),
        ]);
    }

    public function update(Request $request)
    {

        $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'birthday' => ['nullable', 'date', 'before:today'],
            'gender' => ['in:male,female'],
            'country' => ['required', 'string', 'size:2'],
        ]);

        $user = $request->user();

        // if that user have a profile
        // $user->profile->update($request->all());

        //fill method make OverWrite the model (we can send request data an edite old or insert new data)
        $user->profile->fill($request->all())->save();

        return redirect()->route('dashboard.profile.edit')
            ->with('success', 'Profile updated!');


        // $profile = $user->profile;
        // if ($profile->first_name) {
        //     $profile->update($request->all());
        // } else {
        //     // $request->merge([
        //     //     'user_id' => $user->id,
        //     // ]);
        //     // Profile::create($request->all());

        //     $user->profile()->create($request->all());
        // }
    }
}
