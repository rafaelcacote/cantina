export const moneyToCents = (value) => {
    const digits = String(value || '').replace(/\D/g, '');

    return parseInt(digits || '0', 10);
};

export const centsToNumber = (cents) => (cents / 100).toFixed(2);

export const formatMoneyInput = (cents) => {
    const number = (cents / 100).toFixed(2);
    const [intPart, decPart] = number.split('.');
    const intFormatted = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

    return `${intFormatted},${decPart}`;
};

export const initMoneyInputs = (root = document) => {
    root.querySelectorAll('[data-money-input]').forEach((display) => {
        const hiddenName = display.dataset.moneyInput;
        const form = display.closest('form');
        const hidden = form?.querySelector(`input[type="hidden"][name="${hiddenName}"]`);

        if (!hidden || display.dataset.moneyReady === 'true') {
            return;
        }

        display.dataset.moneyReady = 'true';

        let cents = moneyToCents(display.value);

        if (!display.value && hidden.value !== '') {
            cents = Math.round(parseFloat(hidden.value) * 100) || 0;
        }

        const sync = () => {
            display.value = cents ? formatMoneyInput(cents) : '';
            hidden.value = cents ? centsToNumber(cents) : '';
        };

        sync();

        display.addEventListener('input', () => {
            cents = moneyToCents(display.value);
            sync();
        });

        display.addEventListener('blur', () => {
            if (!cents) {
                display.value = '';
                hidden.value = '';
            }
        });

        form?.addEventListener('submit', () => {
            sync();
        });
    });
};
