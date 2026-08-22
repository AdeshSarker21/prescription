<x-guest-layout>
    <div class="text-center">
        <div class="mx-auto w-16 h-16 bg-amber-100 rounded-full flex items-center justify-center mb-4">
            <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>

        <h2 class="text-2xl font-bold text-gray-900 mb-2">Registration Submitted!</h2>
        <p class="text-gray-600 mb-6">
            Your account is pending admin approval. You will be able to log in once an admin reviews and approves your registration.
        </p>

        <div class="bg-gray-50 rounded-lg p-4 mb-6 text-left text-sm text-gray-600">
            <p class="font-medium text-gray-900 mb-1">What happens next?</p>
            <ul class="space-y-1.5">
                <li class="flex items-start gap-2">
                    <svg class="w-4 h-4 text-gray-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    <span>An admin will review your registration</span>
                </li>
                <li class="flex items-start gap-2">
                    <svg class="w-4 h-4 text-gray-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    <span>You'll receive an email once approved</span>
                </li>
                <li class="flex items-start gap-2">
                    <svg class="w-4 h-4 text-gray-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    <span>Then you can log in with your credentials</span>
                </li>
            </ul>
        </div>

        <a href="{{ route('login') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
            Back to Login
        </a>
    </div>
</x-guest-layout>
