<?php

namespace App\Services\Hoster\Users;

use App\Models\profile as Profile;


class ProfileServices
{
    /* public function handle_profile_hoster($request,$user)
    {
        Profile::create([
            'user_id' => $user->id,
            'firstname' => $request->name,
            'lastname' => $request->lastname,
            'phone' => $request->prefix.' '.$request->phone,
            'work_position' => $request->work_position,
        ]);
    } */

    public function handleProfileHoster($request, $user)
    {
        $profile = Profile::where('user_id', $user->id)->first();

        if ($profile) {
            // Actualizar los datos del perfil
            $profile->update([
                'firstname' => $request->name,
                'lastname' => $request->lastname,
                'phone' => $request->prefix . ' ' . $request->phone,
                'work_position_id' => $request->work_position_id,
            ]);
        } else {

            $profile = Profile::create([
                'user_id' => $user->id,
                'firstname' => $request->name,
                'lastname' => $request->lastname,
                'phone' => $request->prefix . ' ' . $request->phone,
                'work_position_id' => $request->work_position_id,
            ]);
        }

        return $profile;
    }

    public function handleUpdateProfileHoster($request, $user)
    {
        $profile = Profile::where('user_id', $user->id)->first();

        /* if (!$profile) {
            $profile = new Profile();
            $profile->user_id = $user->id;
        } */

        $profile->firstname = $request->name;
        $profile->lastname = $request->lastname;
        $profile->phone = $request->prefix . ' ' . $request->phone;
        $profile->save();

        return $profile;
    }

}
