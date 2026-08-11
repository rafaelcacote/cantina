export const phoneDigits = (value) => String(value || '').replace(/\D/g, '').slice(0, 11);

export const formatPhone = (value) => {
    const digits = phoneDigits(value);

    if (!digits.length) {
        return '';
    }

    if (digits.length <= 2) {
        return `(${digits}`;
    }

    if (digits.length <= 6) {
        return `(${digits.slice(0, 2)}) ${digits.slice(2)}`;
    }

    if (digits.length <= 10) {
        return `(${digits.slice(0, 2)}) ${digits.slice(2, 6)}-${digits.slice(6)}`;
    }

    return `(${digits.slice(0, 2)}) ${digits.slice(2, 7)}-${digits.slice(7, 11)}`;
};

export const isValidPhone = (value) => {
    const digits = phoneDigits(value);

    return digits.length === 10 || digits.length === 11;
};
