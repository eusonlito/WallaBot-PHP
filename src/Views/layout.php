<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WallaBot</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        border: "hsl(var(--border))",
                        input: "hsl(var(--input))",
                        ring: "hsl(var(--ring))",
                        background: "hsl(var(--background))",
                        foreground: "hsl(var(--foreground))",
                        primary: { DEFAULT: "hsl(var(--primary))", foreground: "hsl(var(--primary-foreground))" },
                        secondary: { DEFAULT: "hsl(var(--secondary))", foreground: "hsl(var(--secondary-foreground))" },
                        destructive: { DEFAULT: "hsl(var(--destructive))", foreground: "hsl(var(--destructive-foreground))" },
                        muted: { DEFAULT: "hsl(var(--muted))", foreground: "hsl(var(--muted-foreground))" },
                        accent: { DEFAULT: "hsl(var(--accent))", foreground: "hsl(var(--accent-foreground))" },
                        popover: { DEFAULT: "hsl(var(--popover))", foreground: "hsl(var(--popover-foreground))" },
                        card: { DEFAULT: "hsl(var(--card))", foreground: "hsl(var(--card-foreground))" },
                    },
                    borderRadius: {
                        lg: "var(--radius)",
                        md: "calc(var(--radius) - 2px)",
                        sm: "calc(var(--radius) - 4px)",
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="/css/style.css?v=<?= helper()->publicVersion('/css/style.css') ?>">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-background text-foreground min-h-screen font-sans antialiased">
    <div class="relative flex min-h-screen flex-col">
        <!-- Header -->
        <?php if ($page !== 'logout'): ?>
        <header class="sticky top-0 z-40 w-full border-b bg-background/95 backdrop-blur">
            <div class="container flex h-16 items-center justify-between mx-auto px-4 overflow-x-auto custom-scrollbar">
                <a href="/" class="flex items-center gap-2 sm:gap-3 font-bold text-lg tracking-tight shrink-0 mr-4">
                    <div class="bg-black text-white w-8 h-8 rounded flex items-center justify-center text-sm font-black">W</div>
                    <span class="hidden sm:inline">WallaBot</span>
                </a>
                <div class="flex items-center gap-4 sm:gap-8 h-full shrink-0">
                    <nav class="flex h-full items-center gap-4 sm:gap-8 text-sm font-medium">
                        <a href="/" class="flex h-full items-center px-1 border-b-2 <?= $page === 'results' ? 'border-black text-foreground font-bold' : 'border-transparent text-muted-foreground hover:text-foreground transition-all' ?>">Resultados</a>
                        <a href="/searches" class="flex h-full items-center px-1 border-b-2 <?= $page === 'searches' ? 'border-black text-foreground font-bold' : 'border-transparent text-muted-foreground hover:text-foreground transition-all' ?>">Búsquedas</a>
                    </nav>
                    <div class="h-6 w-px bg-slate-200"></div>
                    <a href="/logout" class="text-slate-400 hover:text-rose-600 transition-colors flex items-center justify-center w-8 h-8 rounded-full hover:bg-rose-50" title="Cerrar sesión">
                        <i data-lucide="log-out" class="w-4 h-4"></i>
                    </a>
                </div>
            </div>
        </header>
        <?php endif; ?>

        <main class="flex-1 container mx-auto px-4 py-6 sm:py-10 relative">
            <?php require __DIR__ . "/{$page}.php"; ?>
        </main>
    </div>

    <!-- Modal Form -->
    <?php $isSavedSearchForm = $page === 'searches'; ?>
    <?php if ($isSavedSearchForm || $page === 'general-search' || $page === 'results'): ?>
    <div id="modal-container" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 backdrop-blur-sm invisible opacity-0 transition-all duration-200 overflow-y-auto py-4">
        <div id="modal-content" class="relative w-full max-w-xl bg-white shadow-2xl rounded-2xl overflow-hidden transform opacity-0 scale-95 transition-all duration-200 my-auto">
            <div class="px-6 py-4 border-b flex items-center justify-between bg-white">
                <div>
                    <h2 id="modal-title" class="text-lg font-bold text-slate-900"><?= $isSavedSearchForm ? 'Nueva búsqueda' : 'Buscar en Wallapop' ?></h2>
                </div>
                <button type="button" onclick="closeSearchModal()" class="text-slate-400 hover:text-slate-600 transition-colors text-2xl leading-none">&times;</button>
            </div>

            <form id="search-form" method="<?= $isSavedSearchForm ? 'POST' : 'GET' ?>" action="<?= $isSavedSearchForm ? '/searches/save' : '/explore' ?>" class="m-0 flex flex-col overflow-hidden max-h-[calc(100vh-8rem)]">
                <input type="hidden" name="id" id="field-id">

                <div class="p-8 overflow-y-auto custom-scrollbar flex-1 space-y-2">
                    <!-- QUÉ BUSCAR -->
                    <div class="space-y-2">
                        <div class="space-y-1.5">
                            <label class="text-sm font-semibold text-slate-700">Palabras clave <span class="text-rose-500">*</span></label>
                            <input type="text" name="keywords" id="field-keywords" required class="w-full h-11 px-4 rounded-lg border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400 transition-shadow" placeholder="ej. Audi A5">
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-sm font-semibold text-slate-700">Palabras excluidas</label>
                            <input type="text" name="exclude_keywords" id="field-exclude_keywords" class="w-full h-11 px-4 rounded-lg border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400 transition-shadow" placeholder="ej. roto, averiado, piezas, despiece (separadas por comas)">
                        </div>
                    </div>

                    <!-- FILTROS -->
                    <div class="space-y-2">
                        <div class="grid grid-cols-2 gap-3">
                            <div class="space-y-1.5">
                                <label class="text-sm font-semibold text-slate-700">Precio mínimo</label>
                                <div class="relative">
                                    <input type="number" step="0.01" name="price_min" id="field-price_min" class="w-full h-11 pl-4 pr-8 rounded-lg border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400 transition-shadow" placeholder="0">
                                    <span class="absolute right-4 top-3 text-slate-400 text-sm font-medium">€</span>
                                </div>
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-sm font-semibold text-slate-700">Precio máximo</label>
                                <div class="relative">
                                    <input type="number" step="0.01" name="price_max" id="field-price_max" class="w-full h-11 pl-4 pr-8 rounded-lg border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400 transition-shadow" placeholder="∞">
                                    <span class="absolute right-4 top-3 text-slate-400 text-sm font-medium">€</span>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-sm font-semibold text-slate-700">Categoría</label>
                            <select name="category_ids" id="field-category_ids" onchange="toggleExtraFilters()" class="w-full h-11 px-4 rounded-lg border border-slate-200 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-slate-400 transition-shadow">
                                <option value="">Todas las categorías</option>
                                <option value="100">Coches (100)</option>
                                <option value="14000">Motos (14000)</option>
                                <option value="12800">Motor y accesorios (12800)</option>
                                <option value="12465">Moda y accesorios (12465)</option>
                                <option value="200">Inmobiliaria (200)</option>
                                <option value="12545">Tecnología y electrónica (12545)</option>
                                <option value="16000">Móviles y Telefonía (16000)</option>
                                <option value="15000">Informática (15000)</option>
                                <option value="12579">Deporte y ocio (12579)</option>
                                <option value="17000">Bicicletas (17000)</option>
                                <option value="12900">Consolas y Videojuegos (12900)</option>
                                <option value="12467">Hogar y jardín (12467)</option>
                                <option value="13100">Electrodomésticos (13100)</option>
                                <option value="12463">Cine, libros y música (12463)</option>
                                <option value="12461">Niños y bebés (12461)</option>
                                <option value="18000">Coleccionismo (18000)</option>
                                <option value="19000">Construcción y reformas (19000)</option>
                                <option value="20000">Industria y agricultura (20000)</option>
                                <option value="21000">Empleo (21000)</option>
                                <option value="13200">Servicios (13200)</option>
                                <option value="12485">Otros (12485)</option>
                            </select>
                        </div>

                        <!-- Extra Filters: Cars (100) -->
                        <div id="extra-filters-100" class="hidden space-y-2 pt-4 mt-2 border-t border-slate-100">
                            <div class="grid grid-cols-2 gap-3">
                                <div class="space-y-1.5">
                                    <label class="text-xs font-semibold text-slate-700">Marca</label>
                                    <input type="text" name="extra_filters[brand]" id="ef-brand" class="w-full h-10 px-3 rounded-lg border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400" placeholder="Audi">
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-xs font-semibold text-slate-700">Modelo</label>
                                    <input type="text" name="extra_filters[model]" id="ef-model" class="w-full h-10 px-3 rounded-lg border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400" placeholder="A5">
                                </div>
                            </div>

                            <div class="grid grid-cols-3 gap-3">
                                <div class="space-y-1.5">
                                    <label class="text-xs font-semibold text-slate-700">Año mínimo</label>
                                    <input type="number" name="extra_filters[min_year]" id="ef-min_year" class="w-full h-10 px-3 rounded-lg border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400" placeholder="2021">
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-xs font-semibold text-slate-700">Año máximo</label>
                                    <input type="number" name="extra_filters[max_year]" id="ef-max_year" class="w-full h-10 px-3 rounded-lg border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400" placeholder="2025">
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-xs font-semibold text-slate-700">Km máximo</label>
                                    <div class="relative">
                                        <input type="number" name="extra_filters[max_km]" id="ef-max_km" class="w-full h-10 pl-3 pr-8 rounded-lg border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400" placeholder="150000">
                                        <span class="absolute right-3 top-2.5 text-slate-400 text-xs font-medium">km</span>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-2 pt-2">
                                <label class="text-xs font-semibold text-slate-700">Etiqueta DGT</label>
                                <div class="flex flex-wrap gap-2">
                                    <label class="cursor-pointer">
                                        <input type="checkbox" name="extra_filters[ecolabel][]" value="0" class="ef-check peer hidden">
                                        <div class="px-4 py-1.5 rounded-lg border border-slate-200 text-sm font-bold text-slate-600 bg-white peer-checked:bg-blue-500 peer-checked:text-white peer-checked:border-blue-500 transition-colors">0</div>
                                    </label>
                                    <label class="cursor-pointer">
                                        <input type="checkbox" name="extra_filters[ecolabel][]" value="eco" class="ef-check peer hidden">
                                        <div class="px-4 py-1.5 rounded-lg border border-slate-200 text-sm font-bold text-slate-600 bg-white peer-checked:bg-emerald-600 peer-checked:text-white peer-checked:border-emerald-600 transition-colors">ECO</div>
                                    </label>
                                    <label class="cursor-pointer">
                                        <input type="checkbox" name="extra_filters[ecolabel][]" value="c" class="ef-check peer hidden">
                                        <div class="px-4 py-1.5 rounded-lg border border-slate-200 text-sm font-bold text-slate-600 bg-white peer-checked:bg-amber-500 peer-checked:text-white peer-checked:border-amber-500 transition-colors">C</div>
                                    </label>
                                    <label class="cursor-pointer">
                                        <input type="checkbox" name="extra_filters[ecolabel][]" value="b" class="ef-check peer hidden">
                                        <div class="px-4 py-1.5 rounded-lg border border-slate-200 text-sm font-bold text-slate-600 bg-white peer-checked:bg-amber-400 peer-checked:text-white peer-checked:border-amber-400 transition-colors">B</div>
                                    </label>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3 pt-2">
                                <div class="space-y-2">
                                    <label class="text-xs font-semibold text-slate-700">Motor</label>
                                    <div class="flex flex-wrap gap-2">
                                        <label class="cursor-pointer">
                                            <input type="checkbox" name="extra_filters[engine][]" value="gasoline" class="ef-check peer hidden">
                                            <div class="px-3 py-1.5 rounded-lg border border-slate-200 text-sm font-medium text-slate-600 bg-white peer-checked:bg-black peer-checked:text-white peer-checked:border-black transition-colors">Gasolina</div>
                                        </label>
                                        <label class="cursor-pointer">
                                            <input type="checkbox" name="extra_filters[engine][]" value="gasoil" class="ef-check peer hidden">
                                            <div class="px-3 py-1.5 rounded-lg border border-slate-200 text-sm font-medium text-slate-600 bg-white peer-checked:bg-black peer-checked:text-white peer-checked:border-black transition-colors">Diésel</div>
                                        </label>
                                        <label class="cursor-pointer">
                                            <input type="checkbox" name="extra_filters[engine][]" value="hybride" class="ef-check peer hidden">
                                            <div class="px-3 py-1.5 rounded-lg border border-slate-200 text-sm font-medium text-slate-600 bg-white peer-checked:bg-black peer-checked:text-white peer-checked:border-black transition-colors">Híbrido</div>
                                        </label>
                                        <label class="cursor-pointer">
                                            <input type="checkbox" name="extra_filters[engine][]" value="electric" class="ef-check peer hidden">
                                            <div class="px-3 py-1.5 rounded-lg border border-slate-200 text-sm font-medium text-slate-600 bg-white peer-checked:bg-black peer-checked:text-white peer-checked:border-black transition-colors">Eléctrico</div>
                                        </label>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-xs font-semibold text-slate-700">Caja de cambios</label>
                                    <div class="flex flex-wrap gap-2">
                                        <label class="cursor-pointer">
                                            <input type="checkbox" name="extra_filters[gearbox][]" value="manual" class="ef-check peer hidden">
                                            <div class="px-3 py-1.5 rounded-lg border border-slate-200 text-sm font-medium text-slate-600 bg-white peer-checked:bg-black peer-checked:text-white peer-checked:border-black transition-colors">Manual</div>
                                        </label>
                                        <label class="cursor-pointer">
                                            <input type="checkbox" name="extra_filters[gearbox][]" value="automatic" class="ef-check peer hidden">
                                            <div class="px-3 py-1.5 rounded-lg border border-slate-200 text-sm font-medium text-slate-600 bg-white peer-checked:bg-black peer-checked:text-white peer-checked:border-black transition-colors">Automático</div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Extra Filters: Real Estate (200) -->
                        <div id="extra-filters-200" class="hidden space-y-2 pt-4 mt-2 border-t border-slate-100">
                            <div class="grid grid-cols-2 gap-3">
                                <div class="space-y-1.5">
                                    <label class="text-xs font-semibold text-slate-700">Operación</label>
                                    <select name="extra_filters[operation]" id="ef-operation" class="w-full h-10 px-3 rounded-lg border border-slate-200 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-slate-400">
                                        <option value="">Cualquiera</option>
                                        <option value="buy">Comprar</option>
                                        <option value="rent">Alquilar</option>
                                    </select>
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-xs font-semibold text-slate-700">Tipo</label>
                                    <select name="extra_filters[type]" id="ef-type" class="w-full h-10 px-3 rounded-lg border border-slate-200 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-slate-400">
                                        <option value="">Cualquiera</option>
                                        <option value="house">Casa/Chalet</option>
                                        <option value="apartment">Piso</option>
                                        <option value="room">Habitación</option>
                                        <option value="garage">Garaje</option>
                                    </select>
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-xs font-semibold text-slate-700">Habitaciones (mín)</label>
                                    <input type="number" name="extra_filters[rooms]" id="ef-rooms" class="w-full h-10 px-3 rounded-lg border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400" placeholder="3">
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-xs font-semibold text-slate-700">Baños (mín)</label>
                                    <input type="number" name="extra_filters[bathrooms]" id="ef-bathrooms" class="w-full h-10 px-3 rounded-lg border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400" placeholder="2">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- UBICACIÓN -->
                    <div class="space-y-2">
                        <div class="grid grid-cols-2 gap-3 items-end">
                            <div class="space-y-1.5">
                                <label class="text-sm font-semibold text-slate-700">Distancia máxima</label>
                                <div class="relative">
                                    <input type="text" name="distance" id="field-distance" value="400" class="w-full h-11 pl-4 pr-10 rounded-lg border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400 transition-shadow" placeholder="400">
                                    <span class="absolute right-4 top-3 text-slate-400 text-xs font-medium">km</span>
                                </div>
                            </div>
                            <div id="container-shippable" class="pb-3">
                                <label class="flex items-center gap-3 cursor-pointer group w-fit">
                                    <div class="relative flex items-center">
                                        <input type="checkbox" name="is_shippable" value="1" id="field-is_shippable" class="peer sr-only">
                                        <div class="w-5 h-5 rounded border border-slate-300 bg-white peer-checked:bg-black peer-checked:border-black transition-colors flex items-center justify-center">
                                            <i data-lucide="check" class="w-3.5 h-3.5 text-white opacity-0 peer-checked:opacity-100 transition-opacity"></i>
                                        </div>
                                    </div>
                                    <span class="text-sm font-semibold text-slate-700 group-hover:text-slate-900 transition-colors select-none">Solo con envío</span>
                                </label>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3 pt-2">
                            <div class="space-y-1.5">
                                <label class="text-sm font-semibold text-slate-700">Latitud</label>
                                <input type="number" step="any" name="latitude" id="field-latitude" class="w-full h-11 px-4 rounded-lg border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400 transition-shadow" placeholder="ej. 40.4168">
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-sm font-semibold text-slate-700">Longitud</label>
                                <input type="number" step="any" name="longitude" id="field-longitude" class="w-full h-11 px-4 rounded-lg border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400 transition-shadow" placeholder="ej. -3.7038">
                            </div>
                        </div>
                    </div>

                    <!-- ESTADO -->
                    <?php if ($isSavedSearchForm): ?><div class="space-y-4 pt-6 border-t border-slate-100">
                        <h3 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">ESTADO</h3>
                        <div class="flex flex-col gap-4 sm:flex-row sm:gap-8">
                            <label class="flex items-center gap-3 cursor-pointer group w-fit">
                                <div class="relative flex items-center">
                                    <input type="checkbox" name="active" value="1" id="field-active" class="peer sr-only" checked>
                                    <div class="w-10 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                                </div>
                                <span class="text-sm font-bold text-slate-900 select-none">Búsqueda activa</span>
                            </label>

                            <label class="flex items-center gap-3 cursor-pointer group w-fit">
                                <div class="relative flex items-center">
                                    <input type="checkbox" name="title_only" value="1" id="field-title_only" class="peer sr-only" checked>
                                    <div class="w-10 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                                </div>
                                <span class="text-sm font-bold text-slate-900 select-none">Solo en título</span>
                            </label>
                        </div>
                    </div><?php else: ?>
                    <div class="pt-6 border-t border-slate-100">
                        <label class="flex items-center gap-3 cursor-pointer group w-fit">
                            <div class="relative flex items-center">
                                <input type="checkbox" name="title_only" value="1" id="field-title_only" class="peer sr-only" checked>
                                <div class="w-10 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                            </div>
                            <span class="text-sm font-bold text-slate-900 select-none">Solo en título</span>
                        </label>
                    </div><?php endif; ?>
                </div>

                <!-- Footer -->
                <div class="px-6 py-4 sm:px-8 sm:py-5 border-t border-slate-100 bg-slate-50 flex items-center justify-between shrink-0 sm:rounded-b-2xl">
                    <button type="button" id="btn-delete-search" onclick="deleteCurrentSearch()" class="hidden items-center justify-center w-10 h-10 sm:w-11 sm:h-11 rounded-xl text-rose-500 hover:bg-rose-100 hover:text-rose-700 transition-colors" title="Borrar búsqueda">
                        <i data-lucide="trash-2" class="w-5 h-5"></i>
                    </button>
                    <div class="flex gap-2 sm:gap-3 ml-auto">
                        <button type="button" onclick="closeSearchModal()" class="h-10 sm:h-11 px-4 sm:px-6 rounded-xl border border-slate-200 text-sm font-bold text-slate-600 bg-white hover:bg-slate-50 hover:text-slate-900 transition-colors shadow-sm">Cancelar</button>
                        <button type="submit" id="btn-submit-search" class="h-10 sm:h-11 px-4 sm:px-6 rounded-xl bg-black text-white text-sm font-bold hover:bg-slate-800 transition-colors shadow-md"><?= $isSavedSearchForm ? 'Guardar' : 'Buscar' ?></button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <script>
        <?php if ($isSavedSearchForm): ?>const searchesData = <?= json_encode($searches) ?>;<?php endif; ?>
        <?php if ($page === 'general-search' && $search !== null): ?>const generalSearchData = <?= json_encode($search) ?>;<?php endif; ?>
    </script>
    <?php endif; ?>

    <!-- Image Zoom Modal -->
    <div id="image-modal" class="fixed inset-0 z-[60] flex items-center justify-center bg-black/90 backdrop-blur-md invisible opacity-0 transition-all duration-200">
        <!-- Top Controls -->
        <div class="absolute top-0 left-0 right-0 p-6 flex justify-between items-start z-20 pointer-events-none">
            <div id="image-modal-counter" class="text-white/70 font-bold text-sm bg-black/50 px-3 py-1.5 rounded-lg backdrop-blur-md">1/1</div>
            <button type="button" id="image-modal-close" class="text-white/50 hover:text-white transition-colors pointer-events-auto">
                <i data-lucide="x" class="w-10 h-10"></i>
            </button>
        </div>

        <!-- Navigation Arrows -->
        <button type="button" id="image-modal-prev" class="absolute left-6 top-1/2 -translate-y-1/2 w-12 h-12 rounded-full bg-black/50 text-white flex items-center justify-center hover:bg-black/80 transition-colors z-20 hidden">
            <i data-lucide="chevron-left" class="w-8 h-8"></i>
        </button>
        <button type="button" id="image-modal-next" class="absolute right-6 top-1/2 -translate-y-1/2 w-12 h-12 rounded-full bg-black/50 text-white flex items-center justify-center hover:bg-black/80 transition-colors z-20 hidden">
            <i data-lucide="chevron-right" class="w-8 h-8"></i>
        </button>

        <!-- Image -->
        <img id="image-modal-img" src="" class="max-w-full max-h-full object-contain p-4 scale-95 transition-transform duration-300 ease-out z-10" alt="Full Image">
    </div>

    <script src="/js/app.js?v=<?= helper()->publicVersion('/js/app.js') ?>"></script>
    <script>
        if (window.lucide) window.lucide.createIcons();
    </script>
</body>
</html>
