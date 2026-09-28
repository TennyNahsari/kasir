import axios from 'axios';
import AsyncStorage from '@react-native-async-storage/async-storage';

export const DEFAULT_BASE_URL = 'http://localhost:8000/api';
const STORAGE_KEYS = {
  TOKEN: '@auth_token',
  USER: '@auth_user',
  SERVER_URL: '@server_url',
};

const api = axios.create({
  baseURL: DEFAULT_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  timeout: 15000,
});

// Request interceptor to inject dynamic Base URL and Auth Token
api.interceptors.request.use(
  async (config) => {
    try {
      const customUrl = await AsyncStorage.getItem(STORAGE_KEYS.SERVER_URL);
      if (customUrl) {
        config.baseURL = customUrl;
      }
      
      const token = await AsyncStorage.getItem(STORAGE_KEYS.TOKEN);
      if (token) {
        config.headers.Authorization = `Bearer ${token}`;
      }
    } catch (error) {
      console.error('API Interceptor error:', error);
    }
    return config;
  },
  (error) => Promise.reject(error)
);

export default api;
export { STORAGE_KEYS };
