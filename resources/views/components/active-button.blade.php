<form method="POST" action="{{ route($route, $id) }}" id="active-form-{{ $id }}" class="flex">
    @csrf
    @method('PATCH')
    <button data-id="{{ $id }}" type="button"
        class="active-btn text-green-600 hover:text-green-900 p-1 rounded-full hover:bg-green-50 transition-colors duration-200"
        title="Estado activar o desactivar">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 13l4 4L19 7" />
        </svg>
    </button>
</form>