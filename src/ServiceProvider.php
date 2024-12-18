<?php

namespace Bento\BentoStatamic;

use Database\Seeders\Seeders\BentoFormEventsSeeder;
use Bento\BentoStatamic\Http\Middleware\BentoJsMiddleware;
use Bento\BentoStatamic\Listeners\FormSubmissionListener;
use Bentonow\BentoLaravel\DataTransferObjects\ImportSubscribersData;
use Bentonow\BentoLaravel\Facades\Bento;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Statamic\Events\SubmissionCreated;
use Statamic\Providers\AddonServiceProvider;
use Illuminate\Support\Facades\Event;
use Statamic\Events\UserCreated;
use Statamic\Facades\CP\Nav;


class ServiceProvider extends AddonServiceProvider
{
    protected $routes = [
        'cp' => __DIR__ . '/../routes/cp.php'
    ];

    protected $config = [
        'bento' => __DIR__.'/../config/bento.php'
    ];

    protected $vite = [
        'input' => [
            'resources/js/addon.js',
            'resources/css/addon.css',
        ],
        'publicDirectory' => 'resources/dist',
    ];

    protected $listen = [
        SubmissionCreated::class => [
            FormSubmissionListener::class,
        ],
    ];

    public function bootAddon()
    {
        // Merge configuration
        $this->mergeConfigFrom(__DIR__.'/../config/bento.php', 'bento');

        // Load migrations from the package
        $this->loadMigrationsFrom(__DIR__.'/../Database/migrations');

        // Publish seeders
        $this->publishes([
            __DIR__.'/../Database/Seeders' => database_path('seeders/Bento'),
        ], 'bento-statamic-seeders');

        // Publish configuration
        $this->publishes([
            __DIR__.'/../config/bento.php' => config_path('bento.php'),
        ], 'bento-config');

        // Publish built assets to the parent application's public directory
        $this->publishes([
            __DIR__.'/../public/vendor/bento-statamic-sdk' => public_path('vendor/bento-statamic-sdk'),
        ], 'bento-statamic-assets');

        // Publish images
        $this->publishes([
            __DIR__.'/../resources/images' => public_path('vendor/bento-statamic-sdk/images'),
        ], 'bento-statamic-assets');
        // Publish js tracker
        $this->publishes([
            __DIR__.'/../resources/views/partials' => resource_path('views/vendor/bento-statamic/partials'),
        ], 'bento-statamic-assets');
        // Publish Tailwind config
        $this->publishes([
            __DIR__.'/../tailwind.config.js' => base_path('tailwind.config.bento.js'),
        ], 'bento-statamic-assets');

        // Register the middleware
        $this->app['router']->pushMiddlewareToGroup('web', BentoJsMiddleware::class);

        // Register CP nav item
        Nav::extend(function ($nav) {
            $nav->tools('Bento')
                ->route('bento.index')
                ->icon('<svg viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">
                            <g id="Group">
                                <path id="Path" fill="currentColor" stroke="none" d="M 10.561872 5.256845 L 10.654293 5.162672 C 10.708149 5.107793 10.805887 5.117958 10.849102 5.162672 L 11.341118 5.664022 C 11.394976 5.718901 11.394976 5.807652 11.341118 5.862527 L 11.248697 5.9567 C 11.194841 6.011579 11.097102 6.001416 11.053889 5.9567 L 10.561872 5.455349 C 10.508015 5.40047 10.508015 5.311719 10.561872 5.256845 Z"/>
                                <path id="path1" fill="currentColor" stroke="none" d="M 9.612426 6.228293 L 10.104443 5.726942 C 10.158299 5.672063 10.256038 5.682227 10.299252 5.726942 L 10.391672 5.821115 C 10.445528 5.875995 10.445528 5.964746 10.391672 6.01962 L 9.899654 6.520971 C 9.845797 6.575849 9.74806 6.565686 9.704845 6.520971 L 9.612426 6.426798 C 9.558568 6.371918 9.558568 6.283165 9.612426 6.228293 Z"/>
                                <path id="path2" fill="currentColor" stroke="none" d="M 9.182809 5.379469 L 9.674826 4.878118 C 9.728682 4.823239 9.826421 4.833404 9.869634 4.878118 L 9.962054 4.972291 C 10.015911 5.02717 10.015911 5.115923 9.962054 5.170795 L 9.470037 5.672146 C 9.416181 5.727026 9.318442 5.716862 9.275229 5.672146 L 9.182809 5.577972 C 9.128952 5.523093 9.128952 5.434342 9.182809 5.379469 Z"/>
                                <path id="path3" fill="currentColor" stroke="none" d="M 13.041873 4.560997 C 11.668203 2.866558 9.622297 1.054179 8 1.054179 C 6.377702 1.054179 4.331744 2.865084 2.958127 4.560997 C 1.158979 6.779143 0.085106 9.188423 0.085106 11.002103 C 0.085106 11.772427 0.371013 12.448573 0.932851 13.012293 C 2.929447 15.012249 7.799234 14.947902 7.994638 14.944607 L 8.125622 14.944607 C 8.947427 14.944607 13.225367 14.854499 15.065197 13.011078 C 15.628362 12.447392 15.912942 11.771248 15.912942 11.000889 C 15.912942 9.186516 14.839137 6.777235 13.03992 4.559089 Z M 14.710093 12.641647 C 14.013958 13.338797 12.897669 13.763607 11.770519 14.0224 L 11.770519 10.092211 C 11.770519 9.59967 11.378893 9.199264 10.894197 9.199264 L 5.106962 9.199264 C 4.623591 9.199264 4.230638 9.598318 4.230638 10.092211 L 4.230638 14.0224 C 3.105634 13.762238 1.987234 13.337443 1.291064 12.641647 C 0.822979 12.173493 0.596919 11.637576 0.596919 11.002762 C 0.596919 9.330679 1.652102 6.989146 3.352834 4.89241 C 4.987149 2.876843 6.811557 1.574642 8.001175 1.574642 C 9.190673 1.574642 11.015813 2.877485 12.649515 4.89241 C 14.349651 6.98932 15.405429 9.330784 15.405429 11.002762 C 15.405429 11.637593 15.179371 12.173493 14.711286 12.641647 Z"/>
                            </g>
                </svg>')
                ->can('manage bento')
                ->children([
                    'Advanced Settings' => cp_route('bento.advanced')
                ]);
        });

        // Configure Bento mail if enabled
        if (config('bento.enabled', false)) {
            $this->configureBentoMail();
        }

        // Listen for user creation if auto sync is enabled
        if (config('bento.auto_user_sync', true)) {
            Event::listen(UserCreated::class, function ($event) {
                if (config('bento.enabled', false)) {
                    $this->createBentoSubscriber($event->user);
                }
            });
        }

        // Add JS injection if enabled
        if (config('bento.inject_js', false)) {
            View::composer('*', function ($view) {
                if (config('bento.enabled', false) && config('bento.site_uuid')) {
                    $view->with('bento_site_uuid', config('bento.site_uuid'));
                }
            });
        }
    }

    protected function configureBentoMail()
    {
        config([
            'mail.default' => 'bento',
            'mail.mailers.bento' => [
                'transport' => 'bento'
            ]
        ]);
    }

    protected function createBentoSubscriber($user)
    {
        try {
            // Get name from user
            $fullName = $user->get('name', '');

            // Split name into first and last
            $nameParts = $this->splitName($fullName);

            // Create subscriber data
            $subscriberData = new ImportSubscribersData(
                email: $user->email(),
                firstName: $nameParts['first_name'],
                lastName: $nameParts['last_name'],
                tags: [],
                removeTags: [],
                fields: []
            );

            Bento::importSubscribers(collect([$subscriberData]));
        } catch (\Exception $e) {
            Log::error('Failed to create Bento subscriber: ' . $e->getMessage());
        }
    }

    protected function splitName(string $fullName): array
    {
        $fullName = trim($fullName);

        if (empty($fullName)) {
            return [
                'first_name' => '',
                'last_name' => ''
            ];
        }

        $parts = explode(' ', $fullName);

        if (count($parts) === 1) {
            return [
                'first_name' => $parts[0],
                'last_name' => ''
            ];
        }

        $lastName = array_pop($parts);
        $firstName = implode(' ', $parts);

        return [
            'first_name' => trim($firstName),
            'last_name' => trim($lastName)
        ];
    }

}
