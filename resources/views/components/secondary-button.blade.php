<button {{ $attributes->merge(['type' => 'button', 'class' => 'px-6 py-2 font-medium tracking-wide text-black capitalize transition-colors duration-300 transform bg-gray-200 rounded-lg hover:bg-gray-100 focus:outline-none focus:ring focus:ring-gray-300 focus:ring-opacity-80']) }}>
    {{ $slot }}
</button>
