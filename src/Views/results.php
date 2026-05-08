<section id="section-items">
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-4xl font-extrabold tracking-tight text-slate-900">Resultados</h1>
        <div class="flex items-center gap-4">
            <form id="filter-form" action="/" method="GET" class="flex items-center gap-4 m-0">
                <label class="flex items-center gap-2 cursor-pointer border border-slate-200 rounded-lg px-3 py-2 bg-white text-sm shadow-sm hover:bg-slate-50 transition-colors">
                    <input type="checkbox" name="favorites_only" value="1" onchange="document.getElementById('filter-form').submit()" class="w-4 h-4 rounded border-slate-300 text-rose-500 focus:ring-rose-500" <?= $favoritesOnly ? 'checked' : '' ?>>
                    <span class="font-medium text-slate-700 select-none">Favoritos</span>
                </label>

                <div class="flex items-center gap-2">
                    <select name="sort" onchange="document.getElementById('filter-form').submit()" class="h-10 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-slate-400">
                        <option value="date_desc" <?= $sort === 'date_desc' ? 'selected' : '' ?>>Más recientes</option>
                        <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Precio: más barato</option>
                        <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Precio: más caro</option>
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <select name="search_id" onchange="document.getElementById('filter-form').submit()" class="h-10 w-[200px] rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-slate-400">
                        <option value="">Todas las búsquedas</option>
                        <?php foreach ($searches as $s): ?>
                            <option value="<?= $s->id ?>" <?= $searchId === $s->id ? 'selected' : '' ?>><?= htmlspecialchars($s->keywords) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
        </div>
    </div>

    <!-- Metric Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-10">
        <div class="bg-white border rounded-xl p-5 shadow-sm">
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">ENCONTRADOS</div>
            <div class="flex items-baseline gap-2">
                <span class="text-3xl font-black text-slate-900"><?= $total ?></span>
                <span class="text-xs font-bold text-emerald-600">+<?= $today ?></span>
            </div>
            <div class="text-[11px] text-slate-500 mt-1">últimas 24 h</div>
        </div>
        <div class="bg-white border rounded-xl p-5 shadow-sm">
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">NUEVOS</div>
            <div class="flex items-baseline gap-2">
                <span class="text-3xl font-black text-slate-900"><?= $new ?></span>
            </div>
            <div class="text-[11px] text-slate-500 mt-1">rastreados ahora mismo</div>
        </div>
        <div class="bg-white border rounded-xl p-5 shadow-sm">
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">PRECIO MEDIO</div>
            <div class="flex items-baseline gap-2">
                <span class="text-3xl font-black text-slate-900"><?= number_format($avgPrice, 0, ',', '.') ?> €</span>
            </div>
            <div class="text-[11px] text-slate-500 mt-1 truncate"><?= $activeSearch ? htmlspecialchars($activeSearch->keywords) : 'Todas las búsquedas' ?></div>
        </div>
        <div class="bg-white border rounded-xl p-5 shadow-sm">
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">COINCIDENCIAS HOY</div>
            <div class="flex items-baseline gap-2">
                <span class="text-3xl font-black text-slate-900"><?= $today ?></span>
            </div>
            <div class="text-[11px] text-slate-500 mt-1">vs. ayer</div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <?php if (empty($items)): ?>
            <div class="col-span-full bg-white rounded-2xl border shadow-sm flex flex-col items-center justify-center py-24 px-4">
                <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center text-slate-400 mb-6 border border-slate-100">
                    <i data-lucide="list-filter" class="w-8 h-8 opacity-60"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-900 mb-2">Sin resultados</h3>
                <p class="text-sm text-slate-500 max-w-sm text-center mb-8 leading-relaxed">WallaBot sigue rastreando esta búsqueda. En cuanto encuentre algo, lo verás aquí.</p>
                <a href="/searches" class="h-10 px-6 rounded-xl border border-slate-200 text-sm font-bold text-slate-700 hover:bg-slate-50 transition-colors flex items-center justify-center shadow-sm">Ir a las búsquedas</a>
            </div>
        <?php else: ?>
            <?php foreach ($items as $item):
                $created = strtotime($item->created_at);
                $diff = time() - $created;
                if ($diff < 60) $timeAgo = 'hace un momento';
                elseif ($diff < 3600) $timeAgo = 'hace ' . floor($diff/60) . ' min';
                elseif ($diff < 86400) $timeAgo = 'hace ' . floor($diff/3600) . ' h';
                else $timeAgo = date('d/m/Y', $created);

                $images = [];
                $highResUrls = [];
                if ($item->images) {
                    $images = json_decode($item->images, true) ?? [];
                    foreach ($images as $img) {
                        if (isset($img['urls']['medium'])) {
                            $highResUrls[] = str_replace('W640', 'W1024', $img['urls']['medium']);
                        }
                    }
                }

                $highResJson = htmlspecialchars(json_encode($highResUrls), ENT_QUOTES, 'UTF-8');

                $searchName = 'Búsqueda';
                foreach ($searches as $s) {
                    if ($s->id === $item->search_id) {
                        $searchName = $s->keywords;
                        break;
                    }
                }
            ?>
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col group hover:shadow-xl hover:border-slate-300 transition-all duration-300 h-full">
                <!-- Media Area -->
                <div class="relative aspect-[4/3] overflow-hidden bg-slate-100" data-carousel>
                    <?php if (!empty($images) && isset($images[0]['urls']['medium'])): ?>
                        <!-- Images Container -->
                        <div class="flex w-full h-full transition-transform duration-500 ease-out" data-carousel-track>
                            <?php foreach ($images as $index => $img): ?>
                                <?php if(isset($img['urls']['medium'])): ?>
                                    <div class="w-full h-full flex-shrink-0 cursor-zoom-in overflow-hidden" onclick="openImageModal(<?= $highResJson ?>, <?= $index ?>)">
                                        <img src="<?= htmlspecialchars($img['urls']['medium']) ?>" alt="" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>

                        <!-- Top Right Counter -->
                        <div class="absolute top-3 right-3 z-10">
                            <span class="bg-black/50 backdrop-blur-md text-white text-[10px] font-bold px-2 py-1 rounded" data-carousel-counter>1/<?= count($images) ?></span>
                        </div>

                        <!-- Navigation Arrows (only if > 1 image) -->
                        <?php if (count($images) > 1): ?>
                            <button type="button" class="absolute left-2 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-white/90 shadow-sm flex items-center justify-center text-slate-900 opacity-0 group-hover:opacity-100 transition-opacity z-10" data-carousel-prev>
                                <i data-lucide="chevron-left" class="w-4 h-4"></i>
                            </button>
                            <button type="button" class="absolute right-2 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-white/90 shadow-sm flex items-center justify-center text-slate-900 opacity-0 group-hover:opacity-100 transition-opacity z-10" data-carousel-next>
                                <i data-lucide="chevron-right" class="w-4 h-4"></i>
                            </button>

                            <!-- Bottom Dots -->
                            <div class="absolute bottom-3 left-1/2 -translate-x-1/2 flex gap-1 z-10" data-carousel-dots>
                                <?php foreach ($images as $index => $img): ?>
                                    <?php if(isset($img['urls']['medium'])): ?>
                                        <div class="w-1.5 h-1.5 rounded-full <?= $index === 0 ? 'bg-white' : 'bg-white/50' ?> transition-colors" data-index="<?= $index ?>"></div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                    <?php else: ?>
                        <div class="w-full h-full flex items-center justify-center text-slate-300">
                            <i data-lucide="image" class="w-12 h-12 opacity-20"></i>
                        </div>
                    <?php endif; ?>

                    <!-- Overlay Badges -->
                    <div class="absolute top-3 left-3 flex flex-col gap-2 z-10">
                        <?php if ($diff < 7200): ?>
                            <span class="bg-black text-white text-[9px] font-black tracking-widest px-2 py-1 rounded flex items-center gap-1 w-fit">
                                <div class="w-1 h-1 rounded-full bg-emerald-400 animate-pulse"></div> NUEVO
                            </span>
                        <?php endif; ?>

                        <div class="flex gap-2">
                            <form action="/items/favorite" method="POST" class="m-0 p-0">
                                <input type="hidden" name="id" value="<?= $item->id ?>">
                                <button type="submit" class="w-8 h-8 rounded-full bg-white/90 shadow-sm flex items-center justify-center text-rose-500 hover:scale-110 transition-transform" title="<?= $item->is_favorite ? 'Quitar de favoritos' : 'Añadir a favoritos' ?>">
                                    <i data-lucide="heart" class="w-4 h-4 <?= $item->is_favorite ? 'fill-current' : '' ?>"></i>
                                </button>
                            </form>

                            <form action="/items/hide" method="POST" class="m-0 p-0" onsubmit="return confirm('¿Ocultar este resultado permanentemente?');">
                                <input type="hidden" name="id" value="<?= $item->id ?>">
                                <button type="submit" class="w-8 h-8 rounded-full bg-white/90 shadow-sm flex items-center justify-center text-slate-400 hover:text-rose-600 hover:scale-110 transition-transform" title="Ocultar resultado">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </div>
                    </div>

                    <?php if ($item->is_shippable): ?>
                    <div class="absolute bottom-3 right-3 z-10">
                        <span class="bg-white shadow-sm text-slate-900 text-[10px] font-bold px-2 py-1 rounded">Envío</span>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Info Area -->
                <a href="https://es.wallapop.com/item/<?= htmlspecialchars($item->url) ?>" rel="nofollow noopener noreferrer" target="_blank" class="p-5 flex-1 flex flex-col">
                    <div class="flex justify-between items-baseline mb-2">
                        <span class="text-2xl font-black text-slate-900"><?= number_format($item->price, 0, ',', '.') ?> €</span>
                        <span class="text-[11px] font-bold text-slate-400 uppercase"><?= $timeAgo ?></span>
                    </div>

                    <h3 class="text-sm font-bold text-slate-700 leading-snug line-clamp-2 mb-3"><?= htmlspecialchars($item->title) ?></h3>

                    <div class="mt-auto">
                        <?php if ($item->location_city): ?>
                        <p class="text-[11px] font-medium text-slate-400 truncate mb-4"><?= htmlspecialchars($item->location_city) ?><?= $item->category_id === 100 ? ' · Coches' : ($item->category_id === 200 ? ' · Inmobiliaria' : '') ?></p>
                        <?php endif; ?>

                        <?php if ($item->type_attributes): ?>
                            <?php $attrs = json_decode($item->type_attributes, true); ?>
                            <?php if (is_array($attrs) && !empty($attrs)): ?>
                                <div class="flex flex-wrap gap-1 mb-4">
                                    <?php if (isset($attrs['year'])): ?><span class="bg-slate-100 text-slate-500 text-[10px] font-bold px-1.5 py-0.5 rounded"><?= htmlspecialchars((string)$attrs['year']) ?></span><?php endif; ?>
                                    <?php if (isset($attrs['km'])): ?><span class="bg-slate-100 text-slate-500 text-[10px] font-bold px-1.5 py-0.5 rounded"><?= number_format($attrs['km'], 0, ',', '.') ?> km</span><?php endif; ?>
                                    <?php if (isset($attrs['engine'])): ?><span class="bg-slate-100 text-slate-500 text-[10px] font-bold px-1.5 py-0.5 rounded"><?= htmlspecialchars((string)$attrs['engine']) ?></span><?php endif; ?>
                                    <?php if (isset($attrs['horsepower'])): ?><span class="bg-slate-100 text-slate-500 text-[10px] font-bold px-1.5 py-0.5 rounded"><?= htmlspecialchars((string)$attrs['horsepower']) ?> cv</span><?php endif; ?>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php if ($item->notes): ?>
                        <div class="bg-amber-50 border border-amber-100 rounded-xl p-3 mb-4">                            <div class="flex items-start gap-2">
                                <i data-lucide="bell-ring" class="w-3.5 h-3.5 text-amber-500 shrink-0 mt-0.5"></i>
                                <span class="text-[11px] font-bold text-amber-700 italic"><?= htmlspecialchars($item->notes) ?></span>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                            <span class="bg-slate-100 text-slate-500 text-[10px] font-bold px-2 py-1 rounded"><?= htmlspecialchars($searchName) ?></span>
                            <span class="text-xs font-black text-slate-900 hover:translate-x-1 transition-transform flex items-center gap-1">
                                Ver En Wallapop <i data-lucide="arrow-right" class="w-3 h-3"></i>
                            </span>
                        </div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>
