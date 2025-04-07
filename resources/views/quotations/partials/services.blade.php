<div class="p-6 border-b-2 border-blue-600 bg-white shadow-sm">
    <div class="flex flex-col sm:flex-row sm:items-center gap-3 mb-6">
        <div class="flex items-center">
            <span class="inline-flex items-center justify-center p-3 rounded-full bg-blue-50 text-blue-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </span>
            <h3 class="ml-3 text-lg font-semibold text-gray-800">Servicios Adicionales</h3>
        </div>
        <p class="text-sm text-gray-500 sm:ml-4">Seleccione los servicios requeridos para su cotización</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
        @foreach ($services as $service)
        <div class="relative bg-white rounded-xl border border-gray-200 hover:border-blue-300 transition-all duration-200 shadow-sm hover:shadow-md overflow-hidden">
            <div class="p-4">
                <h4 class="text-base font-medium text-gray-800 mb-3 flex items-center">
                    <span class="flex-shrink-0 mr-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd" />
                        </svg>
                    </span>
                    {{ $service->name }}
                </h4>
                
                <div class="flex items-center space-x-4 justify-center">
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="radio" name="services[{{ $service->id }}]" value="include" 
                               class="h-4 w-4 text-green-500 focus:ring-green-400 border-gray-300"
                               @checked(old("services.{$service->id}") === 'include')>
                        <span class="ml-2 text-sm font-medium text-gray-700">Incluir</span>
                    </label>
                    
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="radio" name="services[{{ $service->id }}]" value="exclude"
                               class="h-4 w-4 text-red-500 focus:ring-red-400 border-gray-300"
                               @checked(old("services.{$service->id}") === 'exclude')>
                        <span class="ml-2 text-sm font-medium text-gray-700">Excluir</span>
                    </label>
                </div>
            </div>
            
            @if($service->description)
            <div class="px-4 pb-3">
                <p class="text-xs text-gray-500">{{ $service->description }}</p>
            </div>
            @endif
        </div>
        @endforeach
    </div>
</div>