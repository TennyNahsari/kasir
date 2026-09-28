import React, { useState, useContext } from 'react';
import {
  StyleSheet,
  View,
  Text,
  TouchableOpacity,
  ScrollView,
  TextInput,
  Alert,
} from 'react-native';
import { useTranslation } from 'react-i18next';
import { AuthContext } from '../context/AuthContext';
import { changeAppLanguage } from '../i18n';
import {
  User,
  Store,
  Globe,
  Server,
  LogOut,
  Info,
  Check,
  ChevronRight,
  Shield,
  TrendingUp,
  Package,
} from 'lucide-react-native';

export default function SettingsScreen({ navigation }) {
  const { t, i18n } = useTranslation();
  const { user, logout, serverUrl, updateServerUrl } = useContext(AuthContext);

  const [customUrl, setCustomUrl] = useState(serverUrl);
  const [editingUrl, setEditingUrl] = useState(false);

  const currentLang = i18n.language || 'en';

  const handleLanguageChange = (lang) => {
    changeAppLanguage(lang);
  };

  const handleSaveServerUrl = async () => {
    const res = await updateServerUrl(customUrl);
    if (res.success) {
      Alert.alert(t('common.success'), t('settings.urlSaved'));
      setEditingUrl(false);
    }
  };

  const handleLogout = () => {
    Alert.alert(
      t('auth.logout'),
      t('settings.logoutConfirm'),
      [
        { text: t('common.cancel'), style: 'cancel' },
        {
          text: t('auth.logout'),
          style: 'destructive',
          onPress: () => logout(),
        },
      ]
    );
  };

  return (
    <ScrollView style={styles.container} contentContainerStyle={styles.scrollContent}>
      {/* Profile Card */}
      <View style={styles.card}>
        <View style={styles.profileRow}>
          <View style={styles.avatarBox}>
            <User size={32} color="#FFFFFF" />
          </View>
          <View style={{ flex: 1, marginLeft: 12 }}>
            <Text style={styles.userName}>{user?.name || 'Kasir Retail'}</Text>
            <Text style={styles.userEmail}>{user?.email || 'kasir@kasir.app'}</Text>
            <View style={styles.roleBadge}>
              <Shield size={12} color="#2563EB" style={{ marginRight: 4 }} />
              <Text style={styles.roleText}>{user?.role?.toUpperCase() || 'KASIR'}</Text>
            </View>
          </View>
        </View>
      </View>

      {/* Quick Access Menu Cards */}
      <Text style={styles.sectionTitle}>Manajemen POS</Text>
      <View style={styles.card}>
        <TouchableOpacity
          style={styles.menuLinkRow}
          onPress={() => navigation.navigate('Dashboard')}
        >
          <TrendingUp size={20} color="#2563EB" style={styles.infoIcon} />
          <View style={{ flex: 1 }}>
            <Text style={styles.infoValue}>Dashboard Penjualan</Text>
            <Text style={styles.infoLabel}>Lihat total grafik & produk terlaris</Text>
          </View>
          <ChevronRight size={18} color="#94A3B8" />
        </TouchableOpacity>

        <View style={styles.divider} />

        <TouchableOpacity
          style={styles.menuLinkRow}
          onPress={() => navigation.navigate('Products')}
        >
          <Package size={20} color="#2563EB" style={styles.infoIcon} />
          <View style={{ flex: 1 }}>
            <Text style={styles.infoValue}>Katalog & Stok Produk</Text>
            <Text style={styles.infoLabel}>Cek sisa stok produk & harga</Text>
          </View>
          <ChevronRight size={18} color="#94A3B8" />
        </TouchableOpacity>
      </View>

      {/* Outlet & Location Info Card */}
      <Text style={styles.sectionTitle}>{t('settings.outlet')}</Text>
      <View style={styles.card}>
        <View style={styles.infoRow}>
          <Store size={20} color="#2563EB" style={styles.infoIcon} />
          <View style={{ flex: 1 }}>
            <Text style={styles.infoLabel}>{t('settings.outlet')}</Text>
            <Text style={styles.infoValue}>
              {user?.outlet?.name || user?.location?.name || 'Main Retail Outlet'}
            </Text>
          </View>
        </View>
      </View>

      {/* Language Switcher Section */}
      <Text style={styles.sectionTitle}>{t('settings.language')}</Text>
      <View style={styles.card}>
        <View style={styles.langRow}>
          <Globe size={20} color="#2563EB" style={styles.infoIcon} />
          <Text style={[styles.infoLabel, { flex: 1 }]}>
            {t('settings.language')}
          </Text>
        </View>

        <View style={styles.langToggleContainer}>
          <TouchableOpacity
            style={[
              styles.langOption,
              currentLang === 'en' && styles.langOptionActive,
            ]}
            onPress={() => handleLanguageChange('en')}
          >
            <Text style={styles.langFlag}>🇬🇧</Text>
            <Text
              style={[
                styles.langOptionText,
                currentLang === 'en' && styles.langOptionTextActive,
              ]}
            >
              English
            </Text>
            {currentLang === 'en' && <Check size={18} color="#2563EB" />}
          </TouchableOpacity>

          <TouchableOpacity
            style={[
              styles.langOption,
              currentLang === 'id' && styles.langOptionActive,
            ]}
            onPress={() => handleLanguageChange('id')}
          >
            <Text style={styles.langFlag}>🇮🇩</Text>
            <Text
              style={[
                styles.langOptionText,
                currentLang === 'id' && styles.langOptionTextActive,
              ]}
            >
              Bahasa Indonesia
            </Text>
            {currentLang === 'id' && <Check size={18} color="#2563EB" />}
          </TouchableOpacity>
        </View>
      </View>

      {/* Server API Config */}
      <Text style={styles.sectionTitle}>{t('settings.serverConfig')}</Text>
      <View style={styles.card}>
        <View style={styles.infoRow}>
          <Server size={20} color="#2563EB" style={styles.infoIcon} />
          <View style={{ flex: 1 }}>
            <Text style={styles.infoLabel}>{t('settings.serverUrl')}</Text>
            {editingUrl ? (
              <View style={{ marginTop: 6 }}>
                <TextInput
                  style={styles.urlInput}
                  value={customUrl}
                  onChangeText={setCustomUrl}
                  autoCapitalize="none"
                />
                <TouchableOpacity
                  style={styles.saveUrlBtn}
                  onPress={handleSaveServerUrl}
                >
                  <Check size={16} color="#FFFFFF" />
                  <Text style={styles.saveUrlText}>{t('common.save')}</Text>
                </TouchableOpacity>
              </View>
            ) : (
              <Text style={styles.infoValue}>{serverUrl}</Text>
            )}
          </View>
          {!editingUrl && (
            <TouchableOpacity onPress={() => setEditingUrl(true)}>
              <Text style={styles.editBtnText}>{t('common.edit')}</Text>
            </TouchableOpacity>
          )}
        </View>
      </View>

      {/* About App */}
      <Text style={styles.sectionTitle}>{t('settings.about')}</Text>
      <View style={styles.card}>
        <View style={styles.infoRow}>
          <Info size={20} color="#64748B" style={styles.infoIcon} />
          <View style={{ flex: 1 }}>
            <Text style={styles.infoLabel}>Kasir POS Mobile</Text>
            <Text style={styles.infoValue}>{t('settings.version')}</Text>
          </View>
        </View>
      </View>

      {/* Logout Button */}
      <TouchableOpacity style={styles.logoutBtn} onPress={handleLogout}>
        <LogOut size={20} color="#EF4444" style={{ marginRight: 8 }} />
        <Text style={styles.logoutText}>{t('auth.logout')}</Text>
      </TouchableOpacity>
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#F9F6F0',
  },
  scrollContent: {
    padding: 16,
  },
  card: {
    backgroundColor: '#FFFFFF',
    borderRadius: 16,
    padding: 16,
    borderWidth: 1,
    borderColor: '#E5D9C5',
    marginBottom: 16,
  },
  profileRow: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  avatarBox: {
    width: 56,
    height: 56,
    borderRadius: 28,
    backgroundColor: '#1E1E1E',
    borderWidth: 1,
    borderColor: '#C9A96E',
    alignItems: 'center',
    justifyContent: 'center',
  },
  userName: {
    fontSize: 18,
    fontWeight: '700',
    color: '#2C2C2C',
    fontFamily: 'serif',
  },
  userEmail: {
    fontSize: 13,
    color: '#5A5A5A',
    marginTop: 2,
  },
  roleBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#F9F6F0',
    borderWidth: 1,
    borderColor: '#E5D9C5',
    paddingVertical: 3,
    paddingHorizontal: 8,
    borderRadius: 10,
    alignSelf: 'flex-start',
    marginTop: 6,
  },
  roleText: {
    fontSize: 11,
    fontWeight: '700',
    color: '#6B2E3E',
  },
  sectionTitle: {
    fontSize: 14,
    fontWeight: '700',
    color: '#6B2E3E',
    marginBottom: 8,
    marginLeft: 4,
    textTransform: 'uppercase',
    letterSpacing: 0.5,
  },
  infoRow: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  menuLinkRow: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 4,
  },
  divider: {
    height: 1,
    backgroundColor: '#F9F6F0',
    marginVertical: 10,
  },
  infoIcon: {
    marginRight: 12,
  },
  infoLabel: {
    fontSize: 13,
    color: '#64748B',
  },
  infoValue: {
    fontSize: 15,
    fontWeight: '600',
    color: '#2C2C2C',
    marginTop: 2,
  },
  editBtnText: {
    fontSize: 13,
    fontWeight: '600',
    color: '#6B2E3E',
  },
  urlInput: {
    backgroundColor: '#F9F6F0',
    borderWidth: 1,
    borderColor: '#E5D9C5',
    borderRadius: 8,
    paddingHorizontal: 10,
    height: 38,
    fontSize: 13,
    color: '#2C2C2C',
  },
  saveUrlBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#6B2E3E',
    paddingVertical: 8,
    borderRadius: 8,
    marginTop: 8,
    gap: 6,
  },
  saveUrlText: {
    color: '#F9F6F0',
    fontSize: 13,
    fontWeight: '600',
  },
  langRow: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 12,
  },
  langToggleContainer: {
    gap: 8,
  },
  langOption: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#F9F6F0',
    borderRadius: 12,
    padding: 12,
    borderWidth: 1,
    borderColor: '#E5D9C5',
  },
  langOptionActive: {
    backgroundColor: '#F9F6F0',
    borderColor: '#6B2E3E',
  },
  langFlag: {
    fontSize: 20,
    marginRight: 10,
  },
  langOptionText: {
    flex: 1,
    fontSize: 14,
    fontWeight: '600',
    color: '#2C2C2C',
  },
  langOptionTextActive: {
    color: '#6B2E3E',
  },
  logoutBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#FEF2F2',
    borderWidth: 1,
    borderColor: '#FECACA',
    borderRadius: 16,
    height: 48,
    marginTop: 8,
    marginBottom: 32,
  },
  logoutText: {
    color: '#DC2626',
    fontSize: 15,
    fontWeight: '700',
  },
});
