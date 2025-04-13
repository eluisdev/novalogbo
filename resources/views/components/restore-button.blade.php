<form method="POST" action="{{ route($route, $id) }}" id="restore-form-{{ $id }}" class="inline">
    @csrf
    @method('PATCH')
    <button data-id="{{ $id }}" type="button"
            class="restore-btn text-amber-500 hover:text-amber-700 p-1 rounded-full hover:bg-amber-50 transition-colors duration-200"
            title="Restaurar">
        <svg xmlns="http://www.w3.org/2000/svg" 
             class="h-5 w-5" 
             fill="none" 
             viewBox="0 0 24 24" 
             stroke="currentColor"
             stroke-width="1.5">
            <path stroke-linecap="round" 
                  stroke-linejoin="round" 
                  d="M12 3 3 12 12 21 21 12 12 3z" />
            <path stroke-linecap="round" 
                  stroke-linejoin="round" 
                  d="M12 7 7 12 12 17 17 12 12 7z" />
        </svg>
    </button>
</form>