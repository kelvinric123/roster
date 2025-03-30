<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>
        
        @if($user->role)
        <div>
            <x-input-label for="role" :value="__('Role')" />
            <div class="mt-1 p-2 bg-gray-50 border border-gray-200 rounded-md text-gray-700">
                @php
                    $roleLabels = [
                        'admin' => 'Administrator',
                        'specialist_doctor_leader' => 'Specialist Doctor Leader',
                        'medical_officer_leader' => 'Medical Officer Leader',
                        'houseman_officer_leader' => 'Houseman Officer Leader',
                        'nurse_leader' => 'Nurse Leader',
                        'specialist_doctor' => 'Specialist Doctor',
                        'medical_officer' => 'Medical Officer', 
                        'houseman_officer' => 'Houseman Officer',
                        'nurse' => 'Nurse'
                    ];
                    $roleLabel = $roleLabels[$user->role] ?? ucfirst(str_replace('_', ' ', $user->role));
                @endphp
                {{ $roleLabel }}
            </div>
            <p class="mt-1 text-xs text-gray-500">This is your assigned role in the system based on your staff type.</p>
        </div>
        @endif

        @if($user->staff && $user->role !== 'admin')
        <div>
            <x-input-label for="department" :value="__('Department')" />
            <div class="mt-1 p-2 bg-gray-50 border border-gray-200 rounded-md text-gray-700">
                @php
                    $departmentName = 'Not Assigned';
                    $staff = $user->staff;
                    
                    // For department leaders, get department from TeamLeader
                    if (str_contains($user->role ?? '', '_leader')) {
                        $teamLeader = App\Models\TeamLeader::where('staff_id', $staff->id)
                            ->where(function($query) {
                                $query->whereNull('end_date')
                                      ->orWhere('end_date', '>=', now());
                            })->first();
                        
                        if ($teamLeader && $teamLeader->department) {
                            $departmentName = $teamLeader->department->name;
                        } elseif ($teamLeader && $teamLeader->department_id) {
                            $department = App\Models\Department::find($teamLeader->department_id);
                            $departmentName = $department ? $department->name : 'Not Assigned';
                        }
                    } 
                    // For regular staff, get department from staff record
                    else {
                        if (is_object($staff->department)) {
                            $departmentName = $staff->department->name;
                        } elseif ($staff->department_id) {
                            $department = App\Models\Department::find($staff->department_id);
                            $departmentName = $department ? $department->name : 'Not Assigned';
                        }
                    }
                @endphp
                {{ $departmentName }}
            </div>
            <p class="mt-1 text-xs text-gray-500">This is your assigned department in the hospital.</p>
        </div>

        <div>
            <x-input-label for="staff_type" :value="__('Staff Type')" />
            <div class="mt-1 p-2 bg-gray-50 border border-gray-200 rounded-md text-gray-700">
                @php
                    $staffTypeLabels = [
                        'specialist_doctor' => 'Specialist Doctor',
                        'medical_officer' => 'Medical Officer',
                        'houseman_officer' => 'Houseman Officer',
                        'nurse' => 'Nurse'
                    ];
                    $staffTypeLabel = $staffTypeLabels[$user->staff->type] ?? ucfirst(str_replace('_', ' ', $user->staff->type));
                @endphp
                {{ $staffTypeLabel }}
            </div>
            <p class="mt-1 text-xs text-gray-500">Your staff type determines which rosters you can view and manage.</p>
        </div>
        @endif

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
