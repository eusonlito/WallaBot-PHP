document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('modal-container')?.addEventListener('click', (e) => {
        if (e.target.id === 'modal-container') closeSearchModal();
    });

    document.getElementById('image-modal')?.addEventListener('click', (e) => {
        // Only close if clicking the backdrop or the explicit close button
        if (e.target.id === 'image-modal' || e.target.closest('#image-modal-close')) {
            closeImageModal();
        }
    });

    document.getElementById('image-modal-next')?.addEventListener('click', modalNextImage);
    document.getElementById('image-modal-prev')?.addEventListener('click', modalPrevImage);

    document.getElementById('search-form')?.addEventListener('submit', function() {
        const btn = document.getElementById('btn-submit-search');
        if (btn) {
            btn.disabled = true;
            btn.innerText = 'Guardando...';
            btn.classList.add('opacity-50', 'cursor-not-allowed');
        }
    });

    initCarousels();
});

let modalImages = [];
let modalCurrentIndex = 0;

function openImageModal(images, startIndex = 0) {
    modalImages = Array.isArray(images) ? images : [images];
    modalCurrentIndex = startIndex;

    const modal = document.getElementById('image-modal');
    if (!modal) return;

    updateImageModalContent();

    modal.classList.remove('invisible', 'opacity-0');
    modal.classList.add('opacity-100');
    
    setTimeout(() => {
        const img = document.getElementById('image-modal-img');
        if(img) img.classList.replace('scale-95', 'scale-100');
    }, 10);
    
    document.body.style.overflow = 'hidden';
    document.addEventListener('keydown', handleModalKeydown);
}

function updateImageModalContent() {
    const img = document.getElementById('image-modal-img');
    const counter = document.getElementById('image-modal-counter');
    const prevBtn = document.getElementById('image-modal-prev');
    const nextBtn = document.getElementById('image-modal-next');

    if (img) img.src = modalImages[modalCurrentIndex];
    if (counter) counter.textContent = `${modalCurrentIndex + 1}/${modalImages.length}`;

    if (modalImages.length > 1) {
        prevBtn?.classList.remove('hidden');
        nextBtn?.classList.remove('hidden');
    } else {
        prevBtn?.classList.add('hidden');
        nextBtn?.classList.add('hidden');
    }
}

function closeImageModal() {
    const modal = document.getElementById('image-modal');
    const img = document.getElementById('image-modal-img');
    if (!modal || !img) return;

    modal.classList.replace('opacity-100', 'opacity-0');
    img.classList.replace('scale-100', 'scale-95');
    setTimeout(() => {
        modal.classList.add('invisible');
        img.src = '';
        document.body.style.overflow = '';
    }, 200);

    document.removeEventListener('keydown', handleModalKeydown);
}

function modalNextImage(e) {
    if(e) e.stopPropagation();
    if (modalImages.length <= 1) return;
    modalCurrentIndex = (modalCurrentIndex === modalImages.length - 1) ? 0 : modalCurrentIndex + 1;
    updateImageModalContent();
}

function modalPrevImage(e) {
    if(e) e.stopPropagation();
    if (modalImages.length <= 1) return;
    modalCurrentIndex = (modalCurrentIndex === 0) ? modalImages.length - 1 : modalCurrentIndex - 1;
    updateImageModalContent();
}

function handleModalKeydown(e) {
    if (e.key === 'Escape') closeImageModal();
    if (e.key === 'ArrowRight') modalNextImage();
    if (e.key === 'ArrowLeft') modalPrevImage();
}

function initCarousels() {
    const carousels = document.querySelectorAll('[data-carousel]');
    
    carousels.forEach(carousel => {
        const track = carousel.querySelector('[data-carousel-track]');
        const prevBtn = carousel.querySelector('[data-carousel-prev]');
        const nextBtn = carousel.querySelector('[data-carousel-next]');
        const counter = carousel.querySelector('[data-carousel-counter]');
        const dots = carousel.querySelectorAll('[data-carousel-dots] div');
        
        if (!track || !prevBtn || !nextBtn) return;

        let currentIndex = 0;
        const totalImages = track.children.length;

        const updateCarousel = () => {
            track.style.transform = `translateX(-${currentIndex * 100}%)`;
            if (counter) {
                counter.textContent = `${currentIndex + 1}/${totalImages}`;
            }
            dots.forEach((dot, idx) => {
                if (idx === currentIndex) {
                    dot.classList.replace('bg-white/50', 'bg-white');
                } else {
                    dot.classList.replace('bg-white', 'bg-white/50');
                }
            });
        };

        prevBtn.addEventListener('click', (e) => {
            e.preventDefault(); // Prevent accidental navigation if wrapped in <a>
            currentIndex = (currentIndex === 0) ? totalImages - 1 : currentIndex - 1;
            updateCarousel();
        });

        nextBtn.addEventListener('click', (e) => {
            e.preventDefault();
            currentIndex = (currentIndex === totalImages - 1) ? 0 : currentIndex + 1;
            updateCarousel();
        });
    });
}

function toggleExtraFilters() {
    const val = document.getElementById('field-category_ids').value;
    document.getElementById('extra-filters-100')?.classList.add('hidden');
    document.getElementById('extra-filters-200')?.classList.add('hidden');
    
    const shippableContainer = document.getElementById('container-shippable');
    const shippableCheckbox = document.getElementById('field-is_shippable');

    // Clear the values when hiding, so they aren't submitted
    const clearInputs = (containerId) => {
        const container = document.getElementById(containerId);
        if (!container) return;
        const inputs = container.querySelectorAll('input, select');
        inputs.forEach(input => input.value = '');
        const checks = container.querySelectorAll('.ef-check');
        checks.forEach(c => c.checked = false);
    };

    if (val === '100') {
        document.getElementById('extra-filters-100')?.classList.remove('hidden');
        clearInputs('extra-filters-200');
        if (shippableContainer) shippableContainer.classList.add('hidden');
        if (shippableCheckbox) shippableCheckbox.checked = false;
    } else if (val === '200') {
        document.getElementById('extra-filters-200')?.classList.remove('hidden');
        clearInputs('extra-filters-100');
        if (shippableContainer) shippableContainer.classList.add('hidden');
        if (shippableCheckbox) shippableCheckbox.checked = false;
    } else {
        clearInputs('extra-filters-100');
        clearInputs('extra-filters-200');
        if (shippableContainer) shippableContainer.classList.remove('hidden');
    }
}

function openSearchModal() {
    document.getElementById('modal-title').innerText = 'Nueva búsqueda';
    document.getElementById('btn-submit-search').innerText = 'Crear búsqueda';
    document.getElementById('search-form').reset();
    document.getElementById('field-id').value = '';
    document.getElementById('field-active').checked = true;
    
    // Manual reset of checkboxes as .reset() might not catch all
    document.querySelectorAll('.ef-check').forEach(c => c.checked = false);
    
    toggleExtraFilters();
    
    const container = document.getElementById('modal-container');
    const content = document.getElementById('modal-content');
    container.classList.remove('invisible', 'opacity-0');
    container.classList.add('opacity-100');
    content.classList.remove('opacity-0', 'scale-95');
    content.classList.add('opacity-100', 'scale-100');
}

function closeSearchModal() {
    const container = document.getElementById('modal-container');
    const content = document.getElementById('modal-content');
    container.classList.replace('opacity-100', 'opacity-0');
    content.classList.replace('opacity-100', 'opacity-0');
    content.classList.add('scale-95');
    setTimeout(() => container.classList.add('invisible'), 200);
}

function editSearch(id) {
    if (typeof searchesData === 'undefined') return;
    const s = searchesData.find(x => x.id == id);
    if (!s) return;
    
    document.getElementById('modal-title').innerText = 'Editar búsqueda';
    document.getElementById('btn-submit-search').innerText = 'Guardar';
    document.getElementById('field-id').value = s.id;
    document.getElementById('field-active').checked = !!s.active;
    document.getElementById('field-keywords').value = s.keywords;
    document.getElementById('field-price_min').value = s.price_min || '';
    document.getElementById('field-price_max').value = s.price_max || '';
    document.getElementById('field-category_ids').value = s.category_ids || '';
    document.getElementById('field-distance').value = s.distance || '';
    document.getElementById('field-latitude').value = s.latitude || '';
    document.getElementById('field-longitude').value = s.longitude || '';
    document.getElementById('field-is_shippable').checked = !!s.is_shippable;

    // Reset extra filters first
    toggleExtraFilters();
    
    // Reset all checkboxes before populating
    document.querySelectorAll('.ef-check').forEach(c => c.checked = false);

    // Populate extra filters
    if (s.extra_filters) {
        try {
            const extra = JSON.parse(s.extra_filters);
            for (const key in extra) {
                const value = extra[key];
                const el = document.getElementById('ef-' + key);
                if (el) {
                    el.value = value;
                } else {
                    // Check if it's a checkbox group (multi-select)
                    const values = value.split(',');
                    const checks = document.querySelectorAll(`[name="extra_filters[${key}][]"]`);
                    checks.forEach(c => {
                        if (values.includes(c.value)) c.checked = true;
                    });
                }
            }
        } catch(e) {}
    }
    
    const container = document.getElementById('modal-container');
    const content = document.getElementById('modal-content');
    container.classList.remove('invisible', 'opacity-0');
    container.classList.add('opacity-100');
    content.classList.remove('opacity-0', 'scale-95');
    content.classList.add('opacity-100', 'scale-100');
}
