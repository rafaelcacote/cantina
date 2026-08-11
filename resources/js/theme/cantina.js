import { definePreset, palette } from '@primeuix/themes';
import Aura from '@primeuix/themes/aura';

const leaf = palette('#3d7a5f');

export const cantinaPreset = definePreset(Aura, {
    primitive: {
        borderRadius: {
            none: '0',
            xs: '8px',
            sm: '12px',
            md: '16px',
            lg: '20px',
            xl: '24px',
        },
    },
    semantic: {
        primary: leaf,
        colorScheme: {
            light: {
                primary: {
                    color: '{primary.600}',
                    inverseColor: '#ffffff',
                    hoverColor: '{primary.700}',
                    activeColor: '{primary.800}',
                },
                highlight: {
                    background: '#d4e86a',
                    focusBackground: '#c8dc5c',
                    color: '#14241f',
                    focusColor: '#14241f',
                },
                surface: {
                    0: '#ffffff',
                    50: '#f7faf8',
                    100: '#eef3f0',
                    200: '#dce6e0',
                    300: '#c4d2cb',
                    400: '#8aa197',
                    500: '#5d7369',
                    600: '#3d5249',
                    700: '#2a3f38',
                    800: '#1b2c27',
                    900: '#14241f',
                    950: '#0c1613',
                },
            },
        },
    },
    components: {
        button: {
            root: {
                borderRadius: '1rem',
                paddingX: '1.15rem',
                paddingY: '0.7rem',
                label: {
                    fontWeight: '600',
                },
            },
        },
        dialog: {
            root: {
                borderRadius: '1.6rem',
                borderColor: 'rgba(20, 36, 31, 0.08)',
            },
            header: {
                padding: '1.25rem 1.25rem 0.5rem',
            },
            content: {
                padding: '0.75rem 1.25rem 0.5rem',
            },
            footer: {
                padding: '0.75rem 1.25rem 1.25rem',
            },
        },
        toast: {
            root: {
                borderRadius: '1.15rem',
            },
        },
        tag: {
            root: {
                borderRadius: '999px',
                fontSize: '0.7rem',
                fontWeight: '700',
                padding: '0.25rem 0.65rem',
            },
        },
        inputtext: {
            root: {
                borderRadius: '1rem',
                paddingX: '0.95rem',
                paddingY: '0.75rem',
            },
        },
        textarea: {
            root: {
                borderRadius: '1.15rem',
                paddingX: '0.95rem',
                paddingY: '0.8rem',
            },
        },
        message: {
            root: {
                borderRadius: '1.15rem',
            },
        },
        avatar: {
            root: {
                borderRadius: '1rem',
            },
        },
    },
});
