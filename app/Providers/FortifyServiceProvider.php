<?php

namespace App\Providers;

use App\Actions\Fortify\AuthenticateUser;
use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\CreateNewAdmin;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Contracts\LogoutResponse;
use Laravel\Fortify\Contracts\LoginResponse;
use App\Providers\RouteServiceProvider;


class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $request = request();
        if ($request->is('admin/*')) {
            Config::set('fortify.guard', 'admin');
            Config::set('fortify.passwords', 'admins');
            Config::set('fortify.prefix', 'admin');
            Config::set('fortify.home', 'admin/dashboard');
        }

        $this->app->instance(LoginResponse::class, new class implements LoginResponse
        {
            public function toResponse($request)
            {
                if ($request->user('admin')) {
                    // dd('admin');
                    return redirect('admin/dashboard');
                }
                // dd('user');
                return redirect('/');
            }
        });

        $this->app->instance(LogoutResponse::class, new class implements LogoutResponse
        {
            public function toResponse($request)
            {
                return redirect('/');
            }
        });

        // $this->app->instance(RegisterResponse::class, new class implements RegisterResponse
        // {
        //     public function toResponse($request)
        //     {
        //         if ($request->user('web')) {
        //             return redirect('/');
        //         }
        //     }
        // });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (Config('fortify.prefix', 'admin')) {
            // dd('admin');
            Fortify::createUsersUsing(CreateNewAdmin::class);
            redirect('admin/dashboard');
        } else {
            Fortify::createUsersUsing(CreateNewUser::class);
            redirect('admin/login');
        }
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->email;

            return Limit::perMinute(5)->by($email . $request->ip());
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        if (Config::get('fortify.guard') == 'admin') {
            Fortify::authenticateUsing([new AuthenticateUser, 'authenticate']);
            Fortify::viewPrefix('auth.');
        } else {
            Fortify::viewPrefix('front.auth.');
        }


        // Fortify::loginView(function () {
        //     if (Config::get('fortify.guard') == 'admin') {
        //         return view('front.auth.login');
        //     }
        //     return view('auth.login');
        // });
        // Fortify::loginView('auth.login');
        // Fortify::registerView(function () {
        //     return view('auth.register');
        // });
    }
}
