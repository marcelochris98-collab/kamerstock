<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * Update the user's notification preferences.
     */
    public function updateNotifications(Request $request): RedirectResponse
    {
        $request->validate([
            'sound_volume' => 'required|integer|min:0|max:100',
            'categories'   => 'nullable|array',
            'categories.*' => 'string|in:messaging,sales,stock,finance,admin',
        ]);

        $user = $request->user();
        $user->update([
            'notifications_enabled'   => $request->has('notifications_enabled'),
            'sounds_enabled'          => $request->has('sounds_enabled'),
            'sound_volume'            => $request->sound_volume,
            'notification_categories' => $request->categories ?? [],
        ]);

        return redirect()->route('profile.edit')->with('success', 'Vos préférences de notification ont été mises à jour.');
    }
}
