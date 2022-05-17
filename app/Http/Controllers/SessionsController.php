<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Nette\Schema\ValidationException;

class SessionsController extends Controller
{
    public function create(): Factory|View|Application
    {
        return view('sessions.create');
    }

    public function store(): Redirector|Application|RedirectResponse
    {
        $attributes = request()->validate([
            'email'=>'required|email',
            'password'=>'required'
        ]);

        //attempt to authenticate and log in the user
        //based on the provided credentials

        if (! auth()->attempt($attributes)){
            return back()
                ->withInput()
                ->withErrors(['email'=>'Your provided credentials could not be verified.']);
        }
        session()->regenerate();
        //redirect with a success flash message
        return redirect('/')->with('success', 'Welcome back!');

        //auth failed
//        throw ValidationException::withMessages([
//            'email'=>'Your provided credentials could not be verified.'
//        ]);

    }

    public function destroy(): Redirector|Application|RedirectResponse
    {
        auth()->logout();

        return redirect('/')->with('success', 'Goodbye!');
    }
}
