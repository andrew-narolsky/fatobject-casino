document.addEventListener('click', e => {
    const btn = e.target.closest('.copy-clipboard');
    if (!btn) return;
    e.preventDefault();

    const code = btn.closest('.promo-block')?.querySelector('.item-value')?.textContent;
    if (!code) return;

    (navigator.clipboard?.writeText
            ? navigator.clipboard.writeText(code)
            : new Promise(res => {
                const t = document.createElement('textarea');
                t.value = code;
                document.body.appendChild(t);
                t.select();
                document.execCommand('copy');
                document.body.removeChild(t);
                res();
            })
    ).then(() => btn.classList.add('copied'))
        .catch(err => console.error('Clipboard error:', err));
});

function toggleExpandable(btn) {
    const itemSelector = btn.dataset.toggleItem;
    const wrapperSelector = btn.dataset.toggleWrapper;
    const rootSelector = btn.dataset.toggleRoot;

    let wrapper;

    if (itemSelector) {
        const item = btn.closest(itemSelector);
        if (!item) return;
        wrapper = item.querySelector(wrapperSelector);
    } else if (rootSelector) {
        const root = btn.closest(rootSelector);
        if (!root) return;
        wrapper = root.querySelector(wrapperSelector);
    }

    if (!wrapper) return;

    const isZeroClose = btn.dataset.toggleCloseHeight === '0';
    const isOpen = wrapper.classList.contains('show');

    if (isOpen) {
        // close
        wrapper.style.maxHeight = wrapper.scrollHeight + 'px';
        requestAnimationFrame(() => {
            wrapper.style.maxHeight = isZeroClose
                ? '0'
                : wrapper.dataset.closedHeight + 'px';
        });

        wrapper.classList.remove('show');
        btn.classList.remove('active');
        btn.textContent = 'Show more';
    } else {
        // open
        if (!isZeroClose && !wrapper.dataset.closedHeight) {
            wrapper.dataset.closedHeight = wrapper.offsetHeight;
        }

        wrapper.classList.add('show');
        wrapper.style.maxHeight = wrapper.scrollHeight + 'px';

        btn.classList.add('active');
        btn.textContent = 'Show less';
    }
}

document.addEventListener('click', function (e) {
    const btn = e.target.closest('.show-more[data-toggle-wrapper]');
    if (!btn) return;

    toggleExpandable(btn);
});

document.addEventListener('click', function (e) {
    const btn = e.target.closest('.show-more');
    if (!btn) return;

    // підтримка різних контейнерів
    const wrapper = btn.closest(
        '.foc-casino__best-slots, .foc-casino__best-brands'
    );
    if (!wrapper) return;

    // визначаємо список усередині wrapper
    const list = wrapper.querySelector(
        '.best-slots-wrapper, .best-brands-wrapper'
    );
    if (!list) return;

    const page     = parseInt(wrapper.dataset.page, 10) + 1;
    const perPage  = parseInt(wrapper.dataset.perPage, 10);
    const postType = wrapper.dataset.postType;

    const ids      = wrapper.dataset.ids || '';
    const orderby  = wrapper.dataset.orderby || '';
    const order    = wrapper.dataset.order || '';
    const metaKey  = wrapper.dataset.metaKey || '';

    btn.disabled = true;

    fetch(FOC_FRONTEND.ajax_url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams({
            action: 'foc_load_more',
            nonce: FOC_FRONTEND.nonce,
            page,
            per_page: perPage,
            post_type: postType,
            ids,
            orderby,
            order,
            meta_key: metaKey
        })
    })
        .then(r => r.json())
        .then(response => {
            if (!response.success) {
                btn.disabled = false;
                return;
            }

            list.insertAdjacentHTML('beforeend', response.data.html);
            wrapper.dataset.page = page;

            if (!response.data.has_more) {
                btn.closest('.show-more-block')?.remove();
            } else {
                btn.disabled = false;
            }
        })
        .catch(() => {
            btn.disabled = false;
        });
});
