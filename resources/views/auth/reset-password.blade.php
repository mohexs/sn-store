<x-guest-layout>
    <h1 class="mb-3 text-xl font-semibold text-gray-900 dark:text-gray-100">Buat password baru</h1>
    <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">Gunakan password minimal 8 karakter, lalu konfirmasi password baru Anda.</p>
    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password baru')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required minlength="8" autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Konfirmasi password baru')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                                type="password"
                                name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                {{ __('Simpan password baru') }}
            </x-primary-button>
        </div>
    </form>
    <a href="{{ route('password.request') }}" class="mt-4 inline-block text-sm text-gray-600 underline dark:text-gray-400">Link kedaluwarsa? Kirim ulang link reset</a>
</x-guest-layout>
