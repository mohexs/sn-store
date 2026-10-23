<x-guest-layout>
    <h1 class="mb-3 text-xl font-semibold text-gray-900 dark:text-gray-100">Lupa password</h1>
    <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
        Masukkan email akun admin atau customer Anda. Kami akan mengirimkan link untuk membuat password baru ke email tersebut.
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="email" maxlength="255" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 mt-4">
            <a href="{{ route('login') }}" class="text-sm text-gray-600 underline dark:text-gray-400">Kembali ke login</a>
            <x-primary-button>
                {{ __('Kirim link reset password') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
