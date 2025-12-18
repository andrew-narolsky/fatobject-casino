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
