import React, { useState, useContext } from 'react';
import {
  StyleSheet,
  View,
  Text,
  TextInput,
  TouchableOpacity,
  ActivityIndicator,
  KeyboardAvoidingView,
  Platform,
  ScrollView,
  Alert,
} from 'react-native';
import { useTranslation } from 'react-i18next';
import { AuthContext } from '../context/AuthContext';
import { changeAppLanguage } from '../i18n';
import { Store, Mail, Lock, Server, Globe, Check } from 'lucide-react-native';

export default function LoginScreen() {
  const { t, i18n } = useTranslation();
  const { login, serverUrl, updateServerUrl } = useContext(AuthContext);

  const [email, setEmail] = useState('kasir@kasir.app');
  const [password, setPassword] = useState('password');
  const [loading, setLoading] = useState(false);
  const [showServerConfig, setShowServerConfig] = useState(false);
  const [customUrl, setCustomUrl] = useState(serverUrl);
  const [errorMsg, setErrorMsg] = useState('');

  const currentLang = i18n.language || 'en';

  const handleLogin = async () => {
    if (!email || !password) {
      setErrorMsg(t('auth.loginFailed'));
      return;
    }

    setErrorMsg('');
    setLoading(true);

    const res = await login(email, password);
    setLoading(false);

    if (!res.success) {
      setErrorMsg(res.error || t('auth.loginFailed'));
    }
  };

  const handleSaveServerUrl = async () => {
    const res = await updateServerUrl(customUrl);
    if (res.success) {
      Alert.alert(t('common.success'), t('settings.urlSaved'));
      setShowServerConfig(false);
    }
  };

  const toggleLanguage = (lang) => {
    changeAppLanguage(lang);
  };

  return (
    <KeyboardAvoidingView
      behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
      style={styles.container}
    >
      <ScrollView contentContainerStyle={styles.scrollContent}>
        {/* Language Switcher Header */}
        <View style={styles.langHeader}>
          <Globe size={18} color="#64748B" style={styles.langIcon} />
          <TouchableOpacity
            style={[styles.langBtn, currentLang === 'en' && styles.langBtnActive]}
            onPress={() => toggleLanguage('en')}
          >
            <Text style={[styles.langText, currentLang === 'en' && styles.langTextActive]}>
              🇬🇧 EN
            </Text>
          </TouchableOpacity>
          <TouchableOpacity
            style={[styles.langBtn, currentLang === 'id' && styles.langBtnActive]}
            onPress={() => toggleLanguage('id')}
          >
            <Text style={[styles.langText, currentLang === 'id' && styles.langTextActive]}>
              🇮🇩 ID
            </Text>
          </TouchableOpacity>
        </View>

        {/* Hero Branding */}
        <View style={styles.heroSection}>
          <View style={styles.logoBadge}>
            <Store size={40} color="#FFFFFF" />
          </View>
          <Text style={styles.appTitle}>{t('auth.title')}</Text>
          <Text style={styles.appSubtitle}>{t('auth.subtitle')}</Text>
        </View>

        {/* Login Card */}
        <View style={styles.card}>
          {errorMsg ? (
            <View style={styles.errorBox}>
              <Text style={styles.errorText}>{errorMsg}</Text>
            </View>
          ) : null}

          {/* Email Input */}
          <Text style={styles.label}>{t('auth.email')}</Text>
          <View style={styles.inputWrapper}>
            <Mail size={20} color="#94A3B8" style={styles.inputIcon} />
            <TextInput
              style={styles.input}
              placeholder={t('auth.emailPlaceholder')}
              placeholderTextColor="#94A3B8"
              value={email}
              onChangeText={setEmail}
              keyboardType="email-address"
              autoCapitalize="none"
            />
          </View>

          {/* Password Input */}
          <Text style={styles.label}>{t('auth.password')}</Text>
          <View style={styles.inputWrapper}>
            <Lock size={20} color="#94A3B8" style={styles.inputIcon} />
            <TextInput
              style={styles.input}
              placeholder={t('auth.passwordPlaceholder')}
              placeholderTextColor="#94A3B8"
              value={password}
              onChangeText={setPassword}
              secureTextEntry
            />
          </View>

          {/* Login Button */}
          <TouchableOpacity
            style={styles.submitBtn}
            onPress={handleLogin}
            disabled={loading}
          >
            {loading ? (
              <ActivityIndicator color="#FFFFFF" />
            ) : (
              <Text style={styles.submitBtnText}>{t('auth.login')}</Text>
            )}
          </TouchableOpacity>

          {/* Preset Helper Links */}
          <View style={styles.presetSection}>
            <Text style={styles.presetTitle}>Quick Demo Logins:</Text>
            <View style={styles.presetRow}>
              <TouchableOpacity
                style={styles.presetChip}
                onPress={() => {
                  setEmail('kasir@kasir.app');
                  setPassword('password');
                }}
              >
                <Text style={styles.presetText}>Kasir Retail</Text>
              </TouchableOpacity>

              <TouchableOpacity
                style={styles.presetChip}
                onPress={() => {
                  setEmail('owner@kasir.app');
                  setPassword('password');
                }}
              >
                <Text style={styles.presetText}>Owner</Text>
              </TouchableOpacity>
            </View>
          </View>

          {/* Server Config Toggle */}
          <TouchableOpacity
            style={styles.serverToggle}
            onPress={() => setShowServerConfig(!showServerConfig)}
          >
            <Server size={16} color="#64748B" />
            <Text style={styles.serverToggleText}>{t('auth.serverUrl')}</Text>
          </TouchableOpacity>

          {showServerConfig && (
            <View style={styles.serverBox}>
              <TextInput
                style={styles.serverInput}
                value={customUrl}
                onChangeText={setCustomUrl}
                placeholder={t('auth.serverUrlPlaceholder')}
                placeholderTextColor="#94A3B8"
                autoCapitalize="none"
              />
              <TouchableOpacity style={styles.serverSaveBtn} onPress={handleSaveServerUrl}>
                <Check size={16} color="#FFFFFF" />
                <Text style={styles.serverSaveText}>{t('common.save')}</Text>
              </TouchableOpacity>
            </View>
          )}
        </View>

        <Text style={styles.footerText}>Kasir POS Mobile System v1.0</Text>
      </ScrollView>
    </KeyboardAvoidingView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#F9F6F0',
  },
  scrollContent: {
    padding: 20,
    alignItems: 'center',
    justifyContent: 'center',
    minHeight: '100%',
  },
  langHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    alignSelf: 'flex-end',
    marginBottom: 20,
  },
  langIcon: {
    marginRight: 6,
  },
  langBtn: {
    paddingVertical: 6,
    paddingHorizontal: 12,
    borderRadius: 20,
    backgroundColor: '#E5D9C5',
    marginLeft: 6,
  },
  langBtnActive: {
    backgroundColor: '#6B2E3E',
  },
  langText: {
    fontSize: 13,
    fontWeight: '600',
    color: '#2C2C2C',
  },
  langTextActive: {
    color: '#F9F6F0',
  },
  heroSection: {
    alignItems: 'center',
    marginBottom: 24,
  },
  logoBadge: {
    width: 72,
    height: 72,
    borderRadius: 24,
    backgroundColor: '#1E1E1E',
    borderWidth: 1,
    borderColor: '#C9A96E',
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 14,
    shadowColor: '#1E1E1E',
    shadowOffset: { width: 0, height: 6 },
    shadowOpacity: 0.35,
    shadowRadius: 10,
    elevation: 8,
  },
  appTitle: {
    fontSize: 26,
    fontWeight: '700',
    fontFamily: 'serif',
    color: '#2C2C2C',
    marginBottom: 4,
  },
  appSubtitle: {
    fontSize: 14,
    color: '#5A5A5A',
    textAlign: 'center',
  },
  card: {
    width: '100%',
    maxWidth: 420,
    backgroundColor: '#FFFFFF',
    borderRadius: 20,
    padding: 24,
    borderWidth: 1,
    borderColor: '#E5D9C5',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.08,
    shadowRadius: 12,
    elevation: 4,
  },
  errorBox: {
    backgroundColor: '#FEF2F2',
    borderWidth: 1,
    borderColor: '#FECACA',
    borderRadius: 10,
    padding: 12,
    marginBottom: 16,
  },
  errorText: {
    color: '#DC2626',
    fontSize: 13,
    textAlign: 'center',
  },
  label: {
    fontSize: 13,
    fontWeight: '600',
    color: '#2C2C2C',
    marginBottom: 6,
  },
  inputWrapper: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#F9F6F0',
    borderWidth: 1,
    borderColor: '#E5D9C5',
    borderRadius: 12,
    paddingHorizontal: 14,
    height: 48,
    marginBottom: 16,
  },
  inputIcon: {
    marginRight: 10,
  },
  input: {
    flex: 1,
    fontSize: 15,
    color: '#2C2C2C',
  },
  submitBtn: {
    height: 50,
    backgroundColor: '#6B2E3E',
    borderRadius: 12,
    alignItems: 'center',
    justifyContent: 'center',
    marginTop: 8,
    shadowColor: '#6B2E3E',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.25,
    shadowRadius: 6,
    elevation: 4,
  },
  submitBtnText: {
    color: '#F9F6F0',
    fontSize: 16,
    fontWeight: '700',
    letterSpacing: 0.5,
  },
  presetSection: {
    marginTop: 20,
    paddingTop: 16,
    borderTopWidth: 1,
    borderTopColor: '#E5D9C5',
  },
  presetTitle: {
    fontSize: 12,
    color: '#64748B',
    marginBottom: 8,
  },
  presetRow: {
    flexDirection: 'row',
    gap: 8,
  },
  presetChip: {
    backgroundColor: '#F9F6F0',
    paddingVertical: 6,
    paddingHorizontal: 12,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#E5D9C5',
  },
  presetText: {
    fontSize: 12,
    fontWeight: '600',
    color: '#6B2E3E',
  },
  serverToggle: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    marginTop: 18,
    gap: 6,
  },
  serverToggleText: {
    fontSize: 13,
    color: '#64748B',
    fontWeight: '500',
  },
  serverBox: {
    marginTop: 12,
    padding: 12,
    backgroundColor: '#F8FAFC',
    borderRadius: 10,
    borderWidth: 1,
    borderColor: '#E2E8F0',
  },
  serverInput: {
    backgroundColor: '#FFFFFF',
    borderWidth: 1,
    borderColor: '#CBD5E1',
    borderRadius: 8,
    paddingHorizontal: 10,
    height: 38,
    fontSize: 13,
    color: '#0F172A',
    marginBottom: 8,
  },
  serverSaveBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#10B981',
    paddingVertical: 8,
    borderRadius: 8,
    gap: 6,
  },
  serverSaveText: {
    color: '#FFFFFF',
    fontSize: 13,
    fontWeight: '600',
  },
  footerText: {
    marginTop: 28,
    fontSize: 12,
    color: '#94A3B8',
  },
});
