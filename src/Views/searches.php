<section id="section-searches">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900">Búsquedas</h1>
        <button onclick="openSearchModal()" class="inline-flex items-center justify-center rounded-lg bg-black px-5 py-2.5 text-sm font-bold text-white shadow-lg hover:bg-slate-800 transition-all w-full sm:w-auto">
            + Nueva búsqueda
        </button>
    </div>

    <?php if (empty($searches)): ?>
        <div class="bg-white rounded-2xl border shadow-sm flex flex-col items-center justify-center py-24 px-4 mt-8">
            <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center text-slate-400 mb-6 border border-slate-100">
                <i data-lucide="search" class="w-8 h-8 opacity-60"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-900 mb-2 text-center">Aún no hay búsquedas</h3>
            <p class="text-sm text-slate-500 max-w-sm text-center mb-8 leading-relaxed">Crea tu primera búsqueda para que WallaBot rastree Wallapop por ti y te avise cuando aparezca algo nuevo.</p>
            <button onclick="openSearchModal()" class="h-10 px-6 rounded-xl bg-black text-white text-sm font-bold hover:bg-slate-800 transition-colors flex items-center justify-center shadow-lg w-full sm:w-auto">+ Crear búsqueda</button>
        </div>
    <?php else: ?>
    <div class="bg-white rounded-xl border shadow-sm overflow-x-auto custom-scrollbar">
        <table class="w-full text-sm min-w-[700px]">
            <thead>
                <tr class="border-b bg-slate-50/50">
                    <th class="h-12 px-6 text-left align-middle font-bold text-slate-400 uppercase text-[10px] tracking-widest">Búsqueda</th>
                    <th class="h-12 px-6 text-left align-middle font-bold text-slate-400 uppercase text-[10px] tracking-widest">Filtros</th>
                    <th class="h-12 px-6 text-center align-middle font-bold text-slate-400 uppercase text-[10px] tracking-widest">Resultados</th>
                    <th class="h-12 px-6 text-left align-middle font-bold text-slate-400 uppercase text-[10px] tracking-widest">Última Ejecución</th>
                    <th class="h-12 px-6 text-right align-middle font-bold text-slate-400 uppercase text-[10px] tracking-widest">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php foreach ($searches as $s): ?>
                <tr class="hover:bg-slate-50 transition-all group">
                    <td class="px-6 py-6">
                        <div class="flex items-center gap-4">
                            <form action="/searches/save" method="POST" class="inline m-0 p-0">
                                <input type="hidden" name="id" value="<?= $s->id ?>">
                                <input type="hidden" name="keywords" value="<?= htmlspecialchars($s->keywords) ?>">
                                <input type="hidden" name="price_min" value="<?= $s->price_min ?>">
                                <input type="hidden" name="price_max" value="<?= $s->price_max ?>">
                                <input type="hidden" name="category_ids" value="<?= htmlspecialchars($s->category_ids ?? '') ?>">
                                <input type="hidden" name="distance" value="<?= $s->distance ?>">
                                <input type="hidden" name="latitude" value="<?= $s->latitude ?>">
                                <input type="hidden" name="longitude" value="<?= $s->longitude ?>">
                                <?php if ($s->is_shippable): ?>
                                <input type="hidden" name="is_shippable" value="1">
                                <?php endif; ?>
                                <input type="hidden" name="active" value="<?= $s->active ? '0' : '1' ?>">
                                <label class="switch cursor-pointer">
                                    <input type="checkbox" onchange="this.form.submit()" <?= $s->active ? 'checked' : '' ?>>
                                    <span class="slider"></span>
                                </label>
                            </form>
                            <div>
                                <a href="/?search_id=<?= $s->id ?>" class="text-base font-bold text-slate-900 hover:underline"><?= htmlspecialchars($s->keywords) ?></a>
                                <div class="text-[11px] text-slate-400 font-medium mt-0.5">ID #<?= $s->id ?></div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-6">
                        <div class="flex flex-wrap gap-2">
                            <?php if ($s->price_min || $s->price_max): ?>
                                <span class="bg-slate-100 text-slate-600 px-3 py-1 rounded-full text-[11px] font-bold"><?= $s->price_min ?? '-' ?> – <?= $s->price_max ?? '-' ?> €</span>
                            <?php endif; ?>
                            <?php if ($s->is_shippable): ?>
                                <span class="bg-slate-100 text-slate-600 px-3 py-1 rounded-full text-[11px] font-bold">Solo envío</span>
                            <?php endif; ?>
                            <?php if ($s->distance): ?>
                                <span class="bg-slate-100 text-slate-600 px-3 py-1 rounded-full text-[11px] font-bold"><?= htmlspecialchars((string)$s->distance) ?> km</span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td class="px-6 py-6 text-center">
                        <div class="flex flex-col items-center gap-1">
                            <span class="text-lg font-black text-slate-900"><?= $counts[$s->id] ?? 0 ?></span>
                        </div>
                    </td>
                    <td class="px-6 py-6">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full <?= $s->active ? 'bg-emerald-500' : 'bg-slate-300' ?>"></div>
                            <span class="text-sm font-medium text-slate-600"><?= $s->active ? 'Activa' : 'Pausada' ?></span>
                        </div>
                    </td>
                    <td class="px-6 py-6 text-right">
                        <button onclick="editSearch(<?= $s->id ?>)" class="text-sm font-bold text-slate-400 hover:text-black transition-colors">Editar</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</section>
