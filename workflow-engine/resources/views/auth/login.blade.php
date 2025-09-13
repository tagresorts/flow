<x-layouts.app>
    <div class="flex items-center justify-center min-h-screen bg-gray-100">
        <div class="w-full max-w-md p-6 bg-white rounded-lg shadow-md">
            <h2 class="text-2xl font-bold text-center text-gray-700">Login</h2>

            <!-- Session Status -->
            @if (session('status'))
                <div class="mb-4 font-medium text-sm text-green-600">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <!-- Email Address -->
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700" for="email">
                        Email
                    </label>
                    <input id="email" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm" type="email" name="email" value="{{ old('email') }}" required autofocus />
                    @error('email')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700" for="password">
                        Password
                    </label>
                    <input id="password" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm"
                                    type="password"
                                    name="password"
                                    required autocomplete="current-password" />
                    @error('password')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Remember Me -->
                <div class="block mt-4">
                    <label for="remember_me" class="inline-flex items-center">
                        <input id="remember_me" type="checkbox" class="text-indigo-600 border-gray-300 rounded shadow-sm" name="remember">
                        <span class="ml-2 text-sm text-gray-600">Remember me</span>
                    </label>
                </div>

                <div class="flex items-center justify-end mt-4">
                    <a class="text-sm text-gray-600 underline hover:text-gray-900" href="#">
                        Forgot your password?
                    </a>

                    <button type="submit" class="inline-flex items-center px-4 py-2 ml-3 text-xs font-semibold tracking-widest text-white uppercase bg-gray-800 border border-transparent rounded-md">
                        Log in
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
