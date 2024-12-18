@extends('statamic::layout')

@section('title', $title)

@section('content')
    <div class="max-w-3xl mt-2 mx-auto">
        <form method="POST" action="{{ cp_route('bento.advanced.update') }}" id="advancedSettingsForm">
            @csrf

            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    {{ session('error') }}
                </div>
            @endif

            <div class="card p-4">
                <div class="mb-6">
                    <!-- Auto User Sync -->
                    <div class="pb-1 mb-2">
                        <div class="flex items-center justify-between">
                            <label class="font-bold">Automatic User Sync</label>
                            <input type="hidden" name="auto_user_sync" value="{{ config('bento.auto_user_sync') ? '1' : '0' }}">
                            <div
                                data-react-component="bento-switch"
                                data-props='{
                                    "id": "auto_user_sync",
                                    "checked": {{ config('bento.auto_user_sync') ? 'true' : 'false' }},
                                    "label": "Enable Auto User Sync"
                                }'
                            ></div>
                        </div>
                    </div>
                    <div class="border-b pb-4 mb-4">
                        <p class="text-gray-700 dark:text-gray-600">
                            When enabled, new Statamic users will automatically be added as subscribers in your Bento account.
                            Their name will be split into first and last name, and their email will be used to create the
                            subscriber record. Disable this if you want to manage subscriber creation manually.
                        </p>
                    </div>
                </div>

                <div class="mb-6">
                    <!-- JS Injection -->
                    <div class="pb-1 mb-2">
                        <div class="flex items-center justify-between">
                            <label class="font-bold">Inject Bento JS</label>
                            <input type="hidden" name="inject_js" value="{{ config('bento.inject_js') ? '1' : '0' }}">
                            <div
                                data-react-component="bento-switch"
                                data-props='{
                                    "id": "inject_js",
                                    "checked": {{ config('bento.inject_js') ? 'true' : 'false' }},
                                    "label": "Enable JS Injection"
                                }'
                            ></div>
                        </div>
                    </div>
                    <div class="border-b pb-4 mb-4">
                        <p class="text-gray-700 dark:text-gray-600">
                            When enabled, Bento's JavaScript tracking code will be automatically injected into all front-end
                            pages of your site. This allows for automatic visitor tracking and event logging. Disable this if
                            you prefer to add the tracking code manually or don't need front-end tracking.
                        </p>
                    </div>
                </div>

                <div class="flex justify-end mt-4">
                    <button type="submit" class="btn-primary">Save Settings</button>
                </div>
            </div>
        </form>

        <!-- Add Bento Events Component -->
        <div id="bento-events" class="mt-8"></div>

        <!-- Add Form Events Manager Component -->
        <div id="form-events-manager" class="mt-8"></div>
    </div>
@endsection

