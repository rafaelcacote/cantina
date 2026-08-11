export const cpfDigits = (value) => String(value || '').replace(/\D/g, '').slice(0, 11);

export const formatCpf = (value) => {
    const digits = cpfDigits(value);

    if (digits.length <= 3) {
        return digits;
    }

    if (digits.length <= 6) {
        return `${digits.slice(0, 3)}.${digits.slice(3)}`;
    }

    if (digits.length <= 9) {
        return `${digits.slice(0, 3)}.${digits.slice(3, 6)}.${digits.slice(6)}`;
    }

    return `${digits.slice(0, 3)}.${digits.slice(3, 6)}.${digits.slice(6, 9)}-${digits.slice(9)}`;
};

const checkDigit = (digits, length) => {
    let sum = 0;

    for (let index = 0; index < length; index += 1) {
        sum += Number(digits[index]) * ((length + 1) - index);
    }

    const remainder = (sum * 10) % 11;

    return remainder === 10 ? 0 : remainder;
};

export const isValidCpf = (value) => {
    const digits = cpfDigits(value);

    if (digits.length !== 11) {
        return false;
    }

    if (/^(\d)\1{10}$/.test(digits)) {
        return false;
    }

    return checkDigit(digits, 9) === Number(digits[9])
        && checkDigit(digits, 10) === Number(digits[10]);
};
