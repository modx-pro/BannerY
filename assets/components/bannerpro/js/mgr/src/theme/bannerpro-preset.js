import { definePreset } from '@primeuix/themes'
import Aura from '@primeuix/themes/aura'

/** Emerald/teal accent — mSearch-style premium manager theme */
export const BannerProPreset = definePreset(Aura, {
  semantic: {
    primary: {
      50: '{emerald.50}',
      100: '{emerald.100}',
      200: '{emerald.200}',
      300: '{emerald.300}',
      400: '{emerald.400}',
      500: '{emerald.500}',
      600: '{emerald.600}',
      700: '{emerald.700}',
      800: '{emerald.800}',
      900: '{emerald.900}',
      950: '{emerald.950}',
    },
    colorScheme: {
      light: {
        surface: {
          0: '#ffffff',
          50: '#f8faf9',
          100: '#f1f5f3',
          200: '#e8eeeb',
        },
      },
      dark: {
        surface: {
          0: '{zinc.950}',
          50: '{zinc.900}',
          100: '{zinc.800}',
          200: '{zinc.700}',
        },
      },
    },
  },
  borderRadius: {
    none: '0',
    xs: '2px',
    sm: '4px',
    md: '6px',
    lg: '8px',
    xl: '12px',
  },
})
