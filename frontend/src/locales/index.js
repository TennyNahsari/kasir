import { createI18n } from 'vue-i18n'
import en from './en'
import id from './id'

// Get saved language from localStorage or default to Indonesian
const savedLocale = localStorage.getItem('locale') || 'id'

const i18n = createI18n({
  legacy: false, // Use Composition API mode
  locale: savedLocale, // Set default locale
  fallbackLocale: 'id', // Fallback to Indonesian if translation not found
  messages: {
    en,
    id,
  },
  globalInjection: true, // Inject $t globally
})

export default i18n
