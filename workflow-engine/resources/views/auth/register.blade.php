<x-layouts.app>
    <div class="flex items-center justify-center min-h-screen bg-gray-100">
        <div class="w-full max-w-md p-6 bg-white rounded-lg shadow-md">
            <h2 class="text-2xl font-bold text-center text-gray-700">Register</h2>

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <!-- Name -->
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700" for="name">
                        Name
                    </label>
                    <input id="name" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm" type="text" name="name" value="{{ old('name') }}" required autofocus />
                </div>

                <!-- Email Address -->
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700" for="email">
                        Email
                    </label>
                    <input id="email" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm" type="email" name="email" value="{{ old('email') }}" required />
                </div>

                <!-- Password -->
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700" for="password">
                        Password
                    </label>
                    <input id="password" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm"
                                    type="password"
                                    name="password"
                                    required autocomplete="new-password" />
                </div>

                <!-- Confirm Password -->
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700" for="password_confirmation">
                        Confirm Password
                    </label>
                    <input id="password_confirmation" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm"
                                    type="password"
                                    name="password_confirmation" required />
                </div>

                <div class="flex items-center justify-end mt-4">
                    <a class="text-sm text-gray-600 underline hover:text-gray-900" href="{{ route('login') }}">
                        Already registered?
                    </a>

                    <button type="submit" class="inline-flex items-center px-4 py-2 ml-3 text-xs font-semibold tracking-widest text-white uppercase bg-gray-800 border border-transparent rounded-md">
                        Register
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
