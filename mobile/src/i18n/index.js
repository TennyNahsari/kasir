import i18n from 'i18next';
import { initReactI18next } from 'react-i18next';
import AsyncStorage from '@react-native-async-storage/async-storage';

import en from './en.json';
import id from './id.json';

const LANGUAGE_KEY = '@app_language';

const resources = {
  en: { translation: en },
  id: { translation: id },
};

i18n
  .use(initReactI18next)
  .init({
    resources,
    lng: 'en', // Default language
    fallbackLng: 'en',
    interpolation: {
      escapeValue: false,
    },
    compatibilityJSON: 'v3',
  });

// Load saved language asynchronously
AsyncStorage.getItem(LANGUAGE_KEY).then((savedLang) => {
  if (savedLang && (savedLang === 'en' || savedLang === 'id')) {
    i18n.changeLanguage(savedLang);
  }
}).catch((err) => console.log('Error loading saved language', err));

export const changeAppLanguage = async (lang) => {
  try {
    await AsyncStorage.setItem(LANGUAGE_KEY, lang);
    await i18n.changeLanguage(lang);
  } catch (error) {
    console.error('Error changing language:', error);
  }
};

export default i18n;
