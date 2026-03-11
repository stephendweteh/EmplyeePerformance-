<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">Settings</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('success'))
                <div class="rounded-lg bg-emerald-100 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="rounded-lg bg-rose-100 px-4 py-3 text-sm text-rose-700">{{ $errors->first() }}</div>
            @endif

            <div class="md-card">
                <div class="md-card-body">
                    <h3 class="text-lg font-semibold text-slate-800">SMTP Settings</h3>
                    <p class="text-sm text-slate-500 mt-1">
                        Emails are sent for employee update submissions to employers, and when employers rate and review employee updates.
                    </p>
                    <p class="text-xs text-slate-500 mt-2">
                        Provider tip: use the exact SMTP host, port, encryption mode, username, and password from your email provider (for example, Office 365, Zoho, SendGrid, Mailgun, or your private mail server).
                    </p>

                    <form
                        method="POST"
                        action="{{ route('admin.settings.update') }}"
                        class="mt-5 space-y-4"
                        x-data="{
                            employeeSubject: @js(old('email_alert_employee_update_submitted_subject', $emailAlerts['employee_update_submitted_subject'])),
                            employeeBody: @js(old('email_alert_employee_update_submitted_body', $emailAlerts['employee_update_submitted_body'])),
                            employeeAction: @js(old('email_alert_employee_update_submitted_action', $emailAlerts['employee_update_submitted_action'])),
                            reviewedSubject: @js(old('email_alert_update_reviewed_subject', $emailAlerts['update_reviewed_subject'])),
                            reviewedBody: @js(old('email_alert_update_reviewed_body', $emailAlerts['update_reviewed_body'])),
                            reviewedAction: @js(old('email_alert_update_reviewed_action', $emailAlerts['update_reviewed_action'])),
                            liveSubject: @js(old('email_alert_live_update_subject', $emailAlerts['live_update_subject'])),
                            liveBody: @js(old('email_alert_live_update_body', $emailAlerts['live_update_body'])),
                            liveAction: @js(old('email_alert_live_update_action', $emailAlerts['live_update_action'])),
                            employeeSample: { ':employee_name': 'Alex Morgan', ':date': '2026-03-11', ':team': 'Operations' },
                            reviewedSample: { ':rating': '9', ':status': 'reviewed', ':comment': 'Great progress this week' },
                            liveSample: { ':title': 'Quarterly Launch Reminder' },
                            renderTemplate(template, replacements) {
                                let output = template || '';
                                Object.entries(replacements).forEach(([key, value]) => {
                                    output = output.split(key).join(value);
                                });
                                return output;
                            }
                        }"
                    >
                        @csrf
                        @method('PUT')

                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label for="smtp_host" class="block text-sm text-slate-700">SMTP Host</label>
                                <input id="smtp_host" name="smtp_host" type="text" value="{{ old('smtp_host', $smtp['host']) }}" class="mt-1 w-full rounded-lg border-slate-300" required>
                            </div>
                            <div>
                                <label for="smtp_port" class="block text-sm text-slate-700">SMTP Port</label>
                                <input id="smtp_port" name="smtp_port" type="number" value="{{ old('smtp_port', $smtp['port']) }}" class="mt-1 w-full rounded-lg border-slate-300" required>
                            </div>
                        </div>

                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label for="smtp_encryption" class="block text-sm text-slate-700">Encryption</label>
                                <select id="smtp_encryption" name="smtp_encryption" class="mt-1 w-full rounded-lg border-slate-300">
                                    @php($enc = old('smtp_encryption', $smtp['encryption'] ?: 'none'))
                                    <option value="tls" @selected($enc === 'tls')>TLS</option>
                                    <option value="ssl" @selected($enc === 'ssl')>SSL</option>
                                    <option value="none" @selected($enc === 'none')>None</option>
                                </select>
                            </div>
                            <div>
                                <label for="smtp_username" class="block text-sm text-slate-700">SMTP Username</label>
                                <input id="smtp_username" name="smtp_username" type="text" value="{{ old('smtp_username', $smtp['username']) }}" class="mt-1 w-full rounded-lg border-slate-300">
                            </div>
                        </div>

                        <div>
                            <label for="smtp_password" class="block text-sm text-slate-700">SMTP Password</label>
                            <input id="smtp_password" name="smtp_password" type="password" class="mt-1 w-full rounded-lg border-slate-300" placeholder="Leave blank to keep current password">
                        </div>

                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label for="smtp_from_address" class="block text-sm text-slate-700">From Email Address</label>
                                <input id="smtp_from_address" name="smtp_from_address" type="email" value="{{ old('smtp_from_address', $smtp['from_address']) }}" class="mt-1 w-full rounded-lg border-slate-300" required>
                            </div>
                            <div>
                                <label for="smtp_from_name" class="block text-sm text-slate-700">From Name</label>
                                <input id="smtp_from_name" name="smtp_from_name" type="text" value="{{ old('smtp_from_name', $smtp['from_name'] ?: config('app.name')) }}" class="mt-1 w-full rounded-lg border-slate-300" required>
                            </div>
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="rounded-lg bg-sky-600 text-white px-4 py-2 text-sm hover:bg-sky-700">Save SMTP Settings</button>
                        </div>

                        <hr class="my-6 border-slate-200">

                        <h4 class="text-md font-semibold text-slate-800">Email Alert Content</h4>
                        <p class="text-sm text-slate-500 mt-1">Super admins can customize alert subject, body, and button text. Supported placeholders are shown below each template.</p>

                        <div class="rounded-lg border border-slate-200 p-4 space-y-3">
                            <p class="text-sm font-semibold text-slate-800">Employee Update Submitted Alert</p>
                            <div>
                                <label for="email_alert_employee_update_submitted_subject" class="block text-sm text-slate-700">Subject</label>
                                <input id="email_alert_employee_update_submitted_subject" name="email_alert_employee_update_submitted_subject" x-model="employeeSubject" type="text" value="{{ old('email_alert_employee_update_submitted_subject', $emailAlerts['employee_update_submitted_subject']) }}" class="mt-1 w-full rounded-lg border-slate-300">
                            </div>
                            <div>
                                <label for="email_alert_employee_update_submitted_body" class="block text-sm text-slate-700">Body</label>
                                <textarea id="email_alert_employee_update_submitted_body" name="email_alert_employee_update_submitted_body" x-model="employeeBody" rows="3" class="mt-1 w-full rounded-lg border-slate-300">{{ old('email_alert_employee_update_submitted_body', $emailAlerts['employee_update_submitted_body']) }}</textarea>
                            </div>
                            <div>
                                <label for="email_alert_employee_update_submitted_action" class="block text-sm text-slate-700">Button Label</label>
                                <input id="email_alert_employee_update_submitted_action" name="email_alert_employee_update_submitted_action" x-model="employeeAction" type="text" value="{{ old('email_alert_employee_update_submitted_action', $emailAlerts['employee_update_submitted_action']) }}" class="mt-1 w-full rounded-lg border-slate-300">
                            </div>
                            <p class="text-xs text-slate-500">Placeholders: :employee_name, :date, :team</p>
                            <div class="rounded-lg bg-slate-50 border border-slate-200 p-3 text-xs text-slate-700">
                                <p class="font-semibold">Preview</p>
                                <p class="mt-1"><span class="font-medium">Subject:</span> <span x-text="renderTemplate(employeeSubject, employeeSample)"></span></p>
                                <p class="mt-1"><span class="font-medium">Body:</span> <span x-text="renderTemplate(employeeBody, employeeSample)"></span></p>
                                <p class="mt-1"><span class="font-medium">Button:</span> <span x-text="employeeAction"></span></p>
                            </div>
                        </div>

                        <div class="rounded-lg border border-slate-200 p-4 space-y-3">
                            <p class="text-sm font-semibold text-slate-800">Update Reviewed Alert</p>
                            <div>
                                <label for="email_alert_update_reviewed_subject" class="block text-sm text-slate-700">Subject</label>
                                <input id="email_alert_update_reviewed_subject" name="email_alert_update_reviewed_subject" x-model="reviewedSubject" type="text" value="{{ old('email_alert_update_reviewed_subject', $emailAlerts['update_reviewed_subject']) }}" class="mt-1 w-full rounded-lg border-slate-300">
                            </div>
                            <div>
                                <label for="email_alert_update_reviewed_body" class="block text-sm text-slate-700">Body</label>
                                <textarea id="email_alert_update_reviewed_body" name="email_alert_update_reviewed_body" x-model="reviewedBody" rows="3" class="mt-1 w-full rounded-lg border-slate-300">{{ old('email_alert_update_reviewed_body', $emailAlerts['update_reviewed_body']) }}</textarea>
                            </div>
                            <div>
                                <label for="email_alert_update_reviewed_action" class="block text-sm text-slate-700">Button Label</label>
                                <input id="email_alert_update_reviewed_action" name="email_alert_update_reviewed_action" x-model="reviewedAction" type="text" value="{{ old('email_alert_update_reviewed_action', $emailAlerts['update_reviewed_action']) }}" class="mt-1 w-full rounded-lg border-slate-300">
                            </div>
                            <p class="text-xs text-slate-500">Placeholders: :rating, :status, :comment</p>
                            <div class="rounded-lg bg-slate-50 border border-slate-200 p-3 text-xs text-slate-700">
                                <p class="font-semibold">Preview</p>
                                <p class="mt-1"><span class="font-medium">Subject:</span> <span x-text="renderTemplate(reviewedSubject, reviewedSample)"></span></p>
                                <p class="mt-1"><span class="font-medium">Body:</span> <span x-text="renderTemplate(reviewedBody, reviewedSample)"></span></p>
                                <p class="mt-1"><span class="font-medium">Button:</span> <span x-text="reviewedAction"></span></p>
                            </div>
                        </div>

                        <div class="rounded-lg border border-slate-200 p-4 space-y-3">
                            <p class="text-sm font-semibold text-slate-800">Live Update Published Alert</p>
                            <div>
                                <label for="email_alert_live_update_subject" class="block text-sm text-slate-700">Subject</label>
                                <input id="email_alert_live_update_subject" name="email_alert_live_update_subject" x-model="liveSubject" type="text" value="{{ old('email_alert_live_update_subject', $emailAlerts['live_update_subject']) }}" class="mt-1 w-full rounded-lg border-slate-300">
                            </div>
                            <div>
                                <label for="email_alert_live_update_body" class="block text-sm text-slate-700">Body</label>
                                <textarea id="email_alert_live_update_body" name="email_alert_live_update_body" x-model="liveBody" rows="3" class="mt-1 w-full rounded-lg border-slate-300">{{ old('email_alert_live_update_body', $emailAlerts['live_update_body']) }}</textarea>
                            </div>
                            <div>
                                <label for="email_alert_live_update_action" class="block text-sm text-slate-700">Button Label</label>
                                <input id="email_alert_live_update_action" name="email_alert_live_update_action" x-model="liveAction" type="text" value="{{ old('email_alert_live_update_action', $emailAlerts['live_update_action']) }}" class="mt-1 w-full rounded-lg border-slate-300">
                            </div>
                            <p class="text-xs text-slate-500">Placeholders: :title</p>
                            <div class="rounded-lg bg-slate-50 border border-slate-200 p-3 text-xs text-slate-700">
                                <p class="font-semibold">Preview</p>
                                <p class="mt-1"><span class="font-medium">Subject:</span> <span x-text="renderTemplate(liveSubject, liveSample)"></span></p>
                                <p class="mt-1"><span class="font-medium">Body:</span> <span x-text="renderTemplate(liveBody, liveSample)"></span></p>
                                <p class="mt-1"><span class="font-medium">Button:</span> <span x-text="liveAction"></span></p>
                            </div>
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="rounded-lg bg-slate-900 text-white px-4 py-2 text-sm hover:bg-black">Save Email Alert Content</button>
                        </div>
                    </form>

                    <hr class="my-6 border-slate-200">

                    <h4 class="text-md font-semibold text-slate-800">Send Test Email</h4>
                    <p class="text-sm text-slate-500 mt-1">Use this to confirm your SMTP settings are working.</p>

                    <form method="POST" action="{{ route('admin.settings.test-email') }}" class="mt-4 grid md:grid-cols-[1fr_auto] gap-3 items-end">
                        @csrf
                        <div>
                            <label for="test_email" class="block text-sm text-slate-700">Test Recipient Email</label>
                            <input id="test_email" name="test_email" type="email" value="{{ old('test_email', auth()->user()->email) }}" class="mt-1 w-full rounded-lg border-slate-300" required>
                        </div>
                        <button type="submit" class="rounded-lg bg-indigo-600 text-white px-4 py-2 text-sm hover:bg-indigo-700">Send Test Email</button>
                    </form>

                    <div class="mt-4">
                        <form method="POST" action="{{ route('admin.settings.diagnostics') }}">
                            @csrf
                            <button type="submit" class="rounded-lg bg-slate-700 text-white px-4 py-2 text-sm hover:bg-slate-800">Run SMTP Diagnostics</button>
                        </form>
                    </div>

                    @if (session('smtp_diagnostics'))
                        @php($diag = session('smtp_diagnostics'))
                        <div class="mt-4 rounded-lg border border-slate-200 bg-slate-50 p-4">
                            <p class="text-sm font-semibold text-slate-800">Diagnostics Result</p>
                            <div class="mt-2 grid md:grid-cols-2 gap-2 text-xs text-slate-600">
                                <p><span class="font-medium">Host:</span> {{ $diag['host'] }}</p>
                                <p><span class="font-medium">Port:</span> {{ $diag['port'] }}</p>
                                <p><span class="font-medium">Encryption:</span> {{ $diag['encryption'] }}</p>
                                <p><span class="font-medium">Username:</span> {{ $diag['username'] }}</p>
                                <p><span class="font-medium">Password Set:</span> {{ $diag['password_set'] ? 'Yes' : 'No' }}</p>
                                <p><span class="font-medium">Reachable:</span> {{ $diag['reachable'] ? 'Yes' : 'No' }}</p>
                            </div>
                            @if (!empty($diag['error']))
                                <p class="mt-2 text-xs text-rose-700"><span class="font-medium">Error:</span> {{ $diag['error'] }}</p>
                            @endif
                            @if (!empty($diag['hint']))
                                <p class="mt-2 text-xs text-slate-700"><span class="font-medium">Hint:</span> {{ $diag['hint'] }}</p>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
