<?php

namespace App\Providers;

use App\Models\Evaluasi;
use App\Models\Renstra;
use App\Models\RTL;
use App\Models\Submission;
use App\Policies\EvaluasiPolicy;
use App\Policies\RenstraPolicy;
use App\Policies\RTLPolicy;
use App\Policies\SubmissionPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register policies
        Gate::policy(Renstra::class, RenstraPolicy::class);
        Gate::policy(Evaluasi::class, EvaluasiPolicy::class);
        Gate::policy(RTL::class, RTLPolicy::class);
        Gate::policy(Submission::class, SubmissionPolicy::class);

        // Define gates for role-based access
        Gate::define('admin', fn ($user) => $user->isAdmin());
        Gate::define('dekan', fn ($user) => $user->hasRole(['admin', 'dekan']));
        Gate::define('gpm', fn ($user) => $user->hasRole(['admin', 'dekan', 'GPM']));
        Gate::define('gkm', fn ($user) => $user->hasRole(['admin', 'GKM']));
        Gate::define('kaprodi', fn ($user) => $user->hasRole(['admin', 'kaprodi']));
        Gate::define('bpap', fn ($user) => $user->hasRole(['admin', 'BPAP']));
        
        // Management gates
        Gate::define('manage-users', fn ($user) => $user->isAdmin());
        Gate::define('manage-renstra', fn ($user) => $user->hasRole(['admin', 'BPAP']));
        Gate::define('verify-evaluasi', fn ($user) => $user->hasRole(['admin', 'dekan', 'GPM']));
        Gate::define('approve-evaluasi', fn ($user) => $user->hasRole(['admin', 'dekan']));
    }
}
