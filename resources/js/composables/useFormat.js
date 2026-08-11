export const formatMoney = (value) =>
    Number(value || 0).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });

export const timeGreeting = () => {
    const hour = new Date().getHours();

    if (hour < 12) {
        return 'Bom dia';
    }

    if (hour < 18) {
        return 'Boa tarde';
    }

    return 'Boa noite';
};

export const orderStatusMeta = (status) => {
    const map = {
        pending: {
            label: 'Pendente',
            hint: 'Aguardando a cantina',
            severity: 'warn',
            icon: 'pi pi-clock',
            tone: 'bloom',
            active: true,
        },
        confirmed: {
            label: 'Confirmado',
            hint: 'A cantina aceitou',
            severity: 'info',
            icon: 'pi pi-check',
            tone: 'leaf',
            active: true,
        },
        preparing: {
            label: 'Preparando',
            hint: 'Preparando',
            severity: 'info',
            icon: 'pi pi-cog',
            tone: 'leaf',
            active: true,
        },
        ready: {
            label: 'Pronto',
            hint: 'Pode retirar',
            severity: 'success',
            icon: 'pi pi-bell',
            tone: 'zest',
            active: true,
        },
        delivered: {
            label: 'Entregue',
            hint: 'Pedido entregue',
            severity: 'success',
            icon: 'pi pi-check-circle',
            tone: 'mist',
            active: false,
        },
        completed: {
            label: 'Concluído',
            hint: 'Tudo certo',
            severity: 'success',
            icon: 'pi pi-check-circle',
            tone: 'mist',
            active: false,
        },
        cancelled: {
            label: 'Cancelado',
            hint: 'Pedido cancelado',
            severity: 'danger',
            icon: 'pi pi-ban',
            tone: 'muted',
            active: false,
        },
    };

    return map[status] || {
        label: status || 'Pedido',
        hint: '',
        severity: 'secondary',
        icon: 'pi pi-circle',
        tone: 'mist',
        active: false,
    };
};

export const studentStatusMeta = (status) => {
    const map = {
        pending: { label: 'Aguardando cantina', tone: 'bloom' },
        active: { label: 'Ativo', tone: 'leaf' },
        inactive: { label: 'Inativo', tone: 'mist' },
        blocked: { label: 'Bloqueado', tone: 'muted' },
    };

    return map[status] || { label: status || 'Aluno', tone: 'mist' };
};

export const topupStatusMeta = (status) => {
    const map = {
        awaiting_payment: { label: 'Pague o Pix', hint: 'Copie a chave e pague', tone: 'bloom' },
        pending_review: { label: 'Na cantina', hint: 'Aguardando conferência', tone: 'leaf' },
        approved: { label: 'Creditado', hint: 'Saldo liberado', tone: 'zest' },
        rejected: { label: 'Recusado', hint: 'A cantina recusou', tone: 'muted' },
    };

    return map[status] || { label: status || 'Recarga', hint: '', tone: 'mist' };
};

export const paymentLabel = (mode) =>
    ({
        wallet: 'Carteira',
        tab: 'Fiado',
        cash: 'Pagar na cantina',
        pix: 'Pix',
        card: 'Cartão',
    })[mode] || mode;

export const tabStatusMeta = (status) => {
    const map = {
        open: { label: 'Em aberto', tone: 'bloom' },
        paid: { label: 'Pago', tone: 'leaf' },
        cancelled: { label: 'Cancelado', tone: 'muted' },
    };

    return map[status] || { label: status || 'Fiado', tone: 'mist' };
};
