<button {{ $attributes->merge(['type' => 'submit', 'class' => 'px-6 py-2 font-medium tracking-wide text-white capitalize transition-colors duration-300 transform bg-indigo-600 rounded-lg hover:bg-indigo-500 focus:outline-none focus:ring focus:ring-indigo-300 focus:ring-opacity-80']) }}>
    {{ $slot }}
</button>
