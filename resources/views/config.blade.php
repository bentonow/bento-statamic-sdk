@extends('statamic::layout')

@section('title', 'Bento Configuration')

@section('content')
    <div class="max-w-lg mt-2 mx-auto">
        <div class="card p-4 mb-4">
            <h2 class="font-bold text-lg mb-2">Important Information</h2>

            <div class="flex flex-row gap-6">
                <!-- Left Column - Text Content -->
                <div class="prose">
                    <p class="mb-4">
                        You can find your API keys at <a href="https://app.bentonow.com/account/teams" target="_blank" class="text-blue-600 hover:text-blue-800">app.bentonow.com/account/teams</a>. If you have trouble finding them, please contact our support.
                    </p>
                    <p class="mb-2">
                        Bento Transactional Email API is designed to send <strong>low volume emails from your Statamic site (such as password resets, form notifications, etc)</strong>, it is not designed for high volume/frequent sending (such as newsletter plugins).
                    </p>
                    <p>
                        Please use Bento's main application for that activity to avoid the aggressive rate limits that we've put in place to stop abuse.
                    </p>

                    <div class="bg-blue-50 border-l border-blue-400 mb-2">
                        <p class="ml-3">
                            Please be aware that Bento does not support email attachments of any kind at this time. Emails with attachments will continue to use the email provider configured in your mail configuration.
                        </p>
                    </div>

                    <p class="mb-4">
                        You can read the quick setup guide at <a href="https://docs.bentonow.com" target="_blank" class="text-blue-600 hover:text-blue-800">docs.bentonow.com</a>
                    </p>
                </div>

                <!-- Right Column - Image -->
                <div class="flex items-center justify-center">
                    <img src="{{ asset('vendor/bento-statamic/images/bento-statamic.webp') }}"
                         alt="Bento for Statamic"
                         class="max-w-full rounded-lg shadow-lg"
                         style="max-height: 200px; object-fit: contain;">
                </div>
            </div>
        </div>

        <!-- Forms Section -->
        <div class="grid grid-cols-1 max-w-lg mx-auto">
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

            <form method="POST" action="{{ cp_route('bento.update') }}">
                @csrf

                <div class="card p-4 mb-4">
                    <div class="mb-4">
                        <div class="flex items-center justify-between">
                            <label class="font-bold ">Enable Bento</label>
                            <div>
                                <input type="hidden" name="enabled" value="0">
                                <input type="checkbox"
                                       name="enabled"
                                       value="1"
                                       class="checkbox w-6 h-6"
                                    {{ config('bento.enabled') ? 'checked' : '' }}>
                            </div>
                        </div>
                        @error('enabled')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="font-bold mb-1 block">Site UUID</label>
                        <input type="text"
                               name="site_uuid"
                               id="site_uuid"
                               value="{{ old('site_uuid', config('bento.site_uuid')) }}"
                               class="input-text @error('site_uuid') border-red-500 @enderror">
                        @error('site_uuid')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="font-bold mb-1 block">Publishable Key</label>
                        <input type="text"
                               name="publishable_key"
                               id="publishable_key"
                               value="{{ old('publishable_key', config('bento.publishable_key')) }}"
                               class="input-text @error('publishable_key') border-red-500 @enderror">
                        @error('publishable_key')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="font-bold mb-1 block">Secret Key</label>
                        <input type="password"
                               name="secret_key"
                               id="secret_key"
                               value="{{ old('secret_key', config('bento.secret_key')) }}"
                               class="input-text @error('secret_key') border-red-500 @enderror">
                        @error('secret_key')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end mt-6">
                        <button type="submit" class="btn-primary">Save Configuration</button>
                    </div>
                </div>
            </form>

            <form method="POST" action="{{ cp_route('bento.update-email') }}" id="emailConfigForm" class="card p-4 mt-4">
                @csrf
                <h3 class="font-bold mb-4">Email Configuration</h3>

                <div class="mb-4">
                    <label class="font-bold mb-1 block">From Author</label>
                    <select id="from_email" name="from_email" class="input-text">
                        <option value="">Loading authors...</option>
                    </select>
                    <p id="author_fetch_error" class="text-red-500 text-sm mt-1 hidden"></p>
                </div>

                <div class="flex items-center justify-between">
                    <p class="text-gray-600 text-sm">
                        Test email will be sent to: {{ $admin_email }}
                    </p>
                    <div class="space-x-2">
                        <button type="submit" class="btn-primary">Save Author</button>
                        <button type="button"
                                id="sendTestEmail"
                                class="btn-primary"
                            {{ !config('bento.enabled') || !config('bento.site_uuid') || !config('bento.publishable_key') || !config('bento.secret_key') ? 'disabled' : '' }}>
                            Send Test Email
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    @parent
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Debug: Initializing Bento configuration...');

            const sendTestButton = document.getElementById('sendTestEmail');
            const fromEmailSelect = document.getElementById('from_email');
            const authorFetchError = document.getElementById('author_fetch_error');

            // Function to fetch authors
            async function fetchAuthors() {
                console.log('Debug: Attempting to fetch authors...');

                const siteUuid = document.getElementById('site_uuid').value;
                const publishableKey = document.getElementById('publishable_key').value;
                const secretKey = document.getElementById('secret_key').value;

                if (!siteUuid || !publishableKey || !secretKey) {
                    console.log('Debug: Missing required credentials');
                    fromEmailSelect.innerHTML = '<option value="">Please configure Bento credentials first</option>';
                    return;
                }

                try {
                    const credentials = btoa(`${publishableKey}:${secretKey}`);
                    const response = await fetch(`https://app.bentonow.com/api/v1/fetch/authors?site_uuid=${siteUuid}`, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'Authorization': `Basic ${credentials}`
                        }
                    });

                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }

                    const result = await response.json();
                    console.log('Debug: Authors API response:', result);

                    // Clear existing options
                    fromEmailSelect.innerHTML = '';

                    // Add default option
                    fromEmailSelect.appendChild(new Option('Select an author...', ''));

                    // Add author options
                    if (result.data && Array.isArray(result.data)) {
                        result.data.forEach(author => {
                            const authorName = author.attributes.name;
                            const authorEmail = author.attributes.email;
                            const option = new Option(`${authorName} <${authorEmail}>`, authorEmail);
                            if (authorEmail === '{{ env('MAIL_FROM_ADDRESS') }}') {
                                option.selected = true;
                            }
                            fromEmailSelect.appendChild(option);
                        });
                        console.log(`Debug: Added ${result.data.length} authors to select`);
                    } else {
                        console.log('Debug: No authors found in response');
                        fromEmailSelect.innerHTML = '<option value="">No authors available</option>';
                    }

                    authorFetchError.classList.add('hidden');
                } catch (error) {
                    console.error('Debug: Error fetching authors:', error);
                    authorFetchError.textContent = `Failed to fetch authors: ${error.message}`;
                    authorFetchError.classList.remove('hidden');
                    fromEmailSelect.innerHTML = '<option value="">Error loading authors</option>';
                }
            }

            // Fetch authors on page load
            fetchAuthors();

            // Add event listeners for credential changes
            ['site_uuid', 'publishable_key', 'secret_key'].forEach(id => {
                document.getElementById(id).addEventListener('change', fetchAuthors);
            });

            // Test email sending functionality
            if (sendTestButton && fromEmailSelect) {
                sendTestButton.addEventListener('click', async function(e) {
                    console.log('Debug: Send test email clicked');
                    e.preventDefault();

                    if (!fromEmailSelect.value) {
                        alert('Please select an author');
                        return;
                    }

                    try {
                        sendTestButton.disabled = true;
                        sendTestButton.innerHTML = 'Sending...';

                        const response = await fetch('{{ cp_route('bento.test-email') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                from_email: fromEmailSelect.value
                            })
                        });

                        const data = await response.json();
                        console.log('Debug: Test email response:', data);

                        if (data.success) {
                            alert('Test email sent successfully!');
                        } else {
                            alert('Failed to send test email: ' + data.message);
                        }
                    } catch (error) {
                        console.error('Debug: Error sending test email:', error);
                        alert('Failed to send test email: ' + error.message);
                    } finally {
                        sendTestButton.disabled = false;
                        sendTestButton.innerHTML = 'Send Test Email';
                    }
                });
            }
        });
    </script>
@endsection
