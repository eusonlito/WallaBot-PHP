<section>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900">Resultados de búsqueda</h1>
        <button type="button" onclick="openGeneralSearchModal(true)" class="text-sm font-bold text-slate-600 hover:text-black">Modificar filtros</button>
    </div>

        <p class="mb-6 text-sm text-slate-500"><strong class="text-slate-900"><?= count($items) ?></strong> resultados para “<?= htmlspecialchars($search->keywords) ?>”</p>
        <?php if ($searchError): ?><div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700"><?= htmlspecialchars($searchError) ?></div><?php endif; ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php foreach ($items as $item): ?>
                <?php $images = $item['images'] ?? []; $image = $images[0]['urls']['medium'] ?? null; $slug = $item['web_slug'] ?? ''; ?>
                <a href="https://es.wallapop.com/item/<?= htmlspecialchars($slug) ?>" target="_blank" rel="noopener" class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col hover:shadow-xl hover:border-slate-300 transition-all duration-300">
                    <div class="aspect-[4/3] bg-slate-100"><?php if ($image): ?><img src="<?= htmlspecialchars($image) ?>" alt="" class="w-full h-full object-cover"><?php endif; ?></div>
                    <div class="p-4 flex flex-col flex-1"><div class="flex justify-between gap-3 mb-3"><span class="text-xl font-black text-slate-900"><?= number_format((float)($item['price']['amount'] ?? 0), 0, ',', '.') ?> €</span><span class="text-[11px] text-slate-400"><?= htmlspecialchars((string)($item['location']['city'] ?? '')) ?></span></div><h2 class="text-sm font-bold text-slate-700 leading-snug line-clamp-2"><?= htmlspecialchars((string)($item['title'] ?? '')) ?></h2></div>
                </a>
            <?php endforeach; ?>
        </div>
        <?php if (empty($items)): ?><div class="bg-white rounded-2xl border shadow-sm text-center py-20"><h2 class="text-xl font-bold text-slate-900">Sin resultados</h2><p class="mt-2 text-sm text-slate-500">Prueba a ajustar los filtros.</p></div><?php endif; ?>
</section>
