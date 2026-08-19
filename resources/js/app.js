

import Alpine from 'alpinejs';

window.Alpine = Alpine;

document.addEventListener('alpine:init', () => {
    Alpine.store('cart', {
        count: window.__cartCount ?? 0,
    });
});

window.addToCart = async function (payload, button, url = '/cart/add') {
    if (button) button.disabled = true;

    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '',
            },
            body: JSON.stringify(payload),
        });

        const data = await response.json();

        if (!response.ok) {
            alert(data.message ?? 'Something went wrong adding this to your cart.');
            return false;
        }

        Alpine.store('cart').count = data.count;
        return true;
    } catch (e) {
        alert('Something went wrong adding this to your cart. Please try again.');
        return false;
    } finally {
        if (button) button.disabled = false;
    }
};

Alpine.start();
