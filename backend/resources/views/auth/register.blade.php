<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <h2 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-4">🏛️ Informasi Ormawa Baru</h2>

        <!-- Nama Ormawa -->
        <div>
            <x-input-label for="nama_ormawa" :value="__('Nama Ormawa / Organisasi')" />
            <x-text-input id="nama_ormawa" class="block mt-1 w-full" type="text" name="nama_ormawa" :value="old('nama_ormawa')" required autofocus />
            <x-input-error :messages="$errors->get('nama_ormawa')" class="mt-2" />
        </div>

        <!-- Jenis Ormawa -->
        <div class="mt-4">
            <x-input-label for="jenis_ormawa" :value="__('Jenis Organisasi')" />
            <select id="jenis_ormawa" name="jenis_ormawa" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm" required>
                <option value="">-- Pilih Jenis --</option>
                <option value="HMPS">HMPS</option>
                <option value="UKM">UKM</option>
                <option value="BEM">BEM</option>
                <option value="Senat">Senat</option>
                <option value="Lainnya">Lainnya</option>
            </select>
            <x-input-error :messages="$errors->get('jenis_ormawa')" class="mt-2" />
        </div>

        <!-- Subdomain -->
        <div class="mt-4">
            <x-input-label for="subdomain" :value="__('Subdomain Portal (contoh: hmif)')" />
            <x-text-input id="subdomain" class="block mt-1 w-full" type="text" name="subdomain" :value="old('subdomain')" required />
            <x-input-error :messages="$errors->get('subdomain')" class="mt-2" />
        </div>

        <hr class="my-6 border-gray-700" />

        <h2 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-4">👤 Akun Admin Penanggung Jawab</h2>

        <!-- Nama Admin -->
        <div>
            <x-input-label for="admin_name" :value="__('Nama Lengkap Admin')" />
            <x-text-input id="admin_name" class="block mt-1 w-full" type="text" name="admin_name" :value="old('admin_name')" required />
            <x-input-error :messages="$errors->get('admin_name')" class="mt-2" />
        </div>

        <!-- Email Admin -->
        <div class="mt-4">
            <x-input-label for="admin_email" :value="__('Email Admin')" />
            <x-text-input id="admin_email" class="block mt-1 w-full" type="email" name="admin_email" :value="old('admin_email')" required />
            <x-input-error :messages="$errors->get('admin_email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Konfirmasi Password')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-6">
            <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 rounded-md" href="{{ route('login') }}">
                {{ __('Sudah punya akun?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Ajukan Pendaftaran Ormawa') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>