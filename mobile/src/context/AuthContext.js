import React, { createContext, useState, useEffect } from 'react';
import AsyncStorage from '@react-native-async-storage/async-storage';
import api, { STORAGE_KEYS, DEFAULT_BASE_URL } from '../services/api';

export const AuthContext = createContext();

export const AuthProvider = ({ children }) => {
  const [user, setUser] = useState(null);
  const [token, setToken] = useState(null);
  const [isLoading, setIsLoading] = useState(true);
  const [serverUrl, setServerUrlState] = useState(DEFAULT_BASE_URL);

  useEffect(() => {
    loadStoredSession();
  }, []);

  const loadStoredSession = async () => {
    try {
      setIsLoading(true);
      const savedUrl = await AsyncStorage.getItem(STORAGE_KEYS.SERVER_URL);
      if (savedUrl) {
        setServerUrlState(savedUrl);
      }

      const savedToken = await AsyncStorage.getItem(STORAGE_KEYS.TOKEN);
      const savedUserStr = await AsyncStorage.getItem(STORAGE_KEYS.USER);

      if (savedToken && savedUserStr) {
        setToken(savedToken);
        setUser(JSON.parse(savedUserStr));
        
        // Optionally fetch fresh user info
        try {
          const res = await api.get('/me');
          if (res.data) {
            setUser(res.data);
            await AsyncStorage.setItem(STORAGE_KEYS.USER, JSON.stringify(res.data));
          }
        } catch (e) {
          console.log('Session refresh info:', e.message);
        }
      }
    } catch (error) {
      console.error('Failed to load session:', error);
    } finally {
      setIsLoading(false);
    }
  };

  const login = async (email, password) => {
    try {
      const response = await api.post('/login', { email, password });
      const { user: userData, token: userToken } = response.data;

      if (!userData) {
        throw new Error('Invalid server response');
      }

      const activeToken = userToken || 'bearer-dummy-token';

      setUser(userData);
      setToken(activeToken);

      await AsyncStorage.setItem(STORAGE_KEYS.TOKEN, activeToken);
      await AsyncStorage.setItem(STORAGE_KEYS.USER, JSON.stringify(userData));

      return { success: true };
    } catch (error) {
      const msg = error.response?.data?.message || error.message || 'Login failed';
      return { success: false, error: msg };
    }
  };

  const logout = async () => {
    try {
      if (token) {
        await api.post('/logout').catch(() => {});
      }
    } catch (e) {
      console.log('Logout error:', e);
    } finally {
      setUser(null);
      setToken(null);
      await AsyncStorage.removeItem(STORAGE_KEYS.TOKEN);
      await AsyncStorage.removeItem(STORAGE_KEYS.USER);
    }
  };

  const updateServerUrl = async (newUrl) => {
    try {
      let formattedUrl = newUrl.trim();
      if (!formattedUrl.startsWith('http://') && !formattedUrl.startsWith('https://')) {
        formattedUrl = `http://${formattedUrl}`;
      }
      if (!formattedUrl.endsWith('/api')) {
        formattedUrl = `${formattedUrl.replace(/\/$/, '')}/api`;
      }
      setServerUrlState(formattedUrl);
      await AsyncStorage.setItem(STORAGE_KEYS.SERVER_URL, formattedUrl);
      return { success: true, url: formattedUrl };
    } catch (err) {
      return { success: false, error: err.message };
    }
  };

  return (
    <AuthContext.Provider
      value={{
        user,
        token,
        isLoading,
        serverUrl,
        login,
        logout,
        updateServerUrl,
      }}
    >
      {children}
    </AuthContext.Provider>
  );
};
