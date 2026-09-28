import React, { useState, useEffect, useContext } from 'react';
import {
  StyleSheet,
  View,
  Text,
  ScrollView,
  TouchableOpacity,
  TextInput,
  Image,
  FlatList,
  ActivityIndicator,
  Modal,
  Alert,
} from 'react-native';
import { useTranslation } from 'react-i18next';
import { AuthContext } from '../context/AuthContext';
import { changeAppLanguage } from '../i18n';
import api from '../services/api';
import {
  Coffee,
  Globe,
  LogIn,
  Search,
  Store,
  Sparkles,
  MapPin,
  CheckCircle,
  Clock,
  X,
  ChevronRight,
  ShoppingBag,
  Award,
} from 'lucide-react-native';

export default function HomeScreen({ navigation }) {
  const { t, i18n } = useTranslation();
  const { user } = useContext(AuthContext);

  const [products, setProducts] = useState([]);
  const [categories, setCategories] = useState([]);
  const [locations, setLocations] = useState([]);
  const [selectedCat, setSelectedCat] = useState(null);
  const [selectedLocId, setSelectedLocId] = useState(null);
  const [loading, setLoading] = useState(true);

  // ── Pagination States ──
  const [currentMenuPage, setCurrentMenuPage] = useState(1);
  const menuPerPage = 4;

  const [currentOutletPage, setCurrentOutletPage] = useState(1);
  const outletsPerPage = 3;

  const [currentOutletTabPage, setCurrentOutletTabPage] = useState(1);
  const outletTabPerPage = 3;

  // Customer Order Search
  const [searchTxNo, setSearchTxNo] = useState('');
  const [searchingTx, setSearchingTx] = useState(false);
  const [foundTx, setFoundTx] = useState(null);
  const [showOrderModal, setShowOrderModal] = useState(false);

  const currentLang = i18n.language || 'en';

  useEffect(() => {
    fetchPublicData();
  }, []);

  const fetchPublicData = async () => {
    try {
      setLoading(true);
      const [prodRes, catRes, locRes] = await Promise.all([
        api.get('/public/products').catch(async () => {
          try {
            return await api.get('/products?per_page=20');
          } catch (e) {
            return { data: [] };
          }
        }),
        api.get('/public/categories').catch(async () => {
          try {
            return await api.get('/categories');
          } catch (e) {
            return { data: [] };
          }
        }),
        api.get('/public/locations').catch(async () => {
          try {
            return await api.get('/locations');
          } catch (e) {
            return { data: [] };
          }
        }),
      ]);

      const prodData = prodRes.data?.data || prodRes.data || [];
      const catData = catRes.data?.data || catRes.data || [];
      const locData = locRes.data?.data || locRes.data || [];

      const rawCat = Array.isArray(catData) ? catData : [];
      // Filter categories strictly for FNB categories (Makanan-FNB, Minuman-FNB, Snack-FNB)
      const fnbCatList = rawCat.filter((cat) => {
        const name = (cat.name || '').toLowerCase();
        return name.includes('fnb');
      });

      setProducts(Array.isArray(prodData) ? prodData : []);
      setCategories(fnbCatList);
      setLocations(Array.isArray(locData) ? locData : []);
    } catch (err) {
      console.log('Error fetching public landing data:', err);
    } finally {
      setLoading(false);
    }
  };

  const handleSearchOrder = async () => {
    if (!searchTxNo.trim()) return;
    try {
      setSearchingTx(true);
      const res = await api.get(`/public/orders/search?transaction_no=${searchTxNo.trim()}`);
      const tx = res.data?.data || res.data;
      if (tx) {
        setFoundTx(tx);
        setShowOrderModal(true);
      } else {
        Alert.alert(t('common.warning'), t('landing.orderNotFound'));
      }
    } catch (e) {
      Alert.alert(t('common.warning'), t('landing.orderNotFound'));
    } finally {
      setSearchingTx(false);
    }
  };

  const toggleLanguage = () => {
    const nextLang = currentLang === 'en' ? 'id' : 'en';
    changeAppLanguage(nextLang);
  };

  // Filter locations for FNB & Outlet
  const fnbLocations = locations.filter((loc) => {
    const locType = (loc.type || '').toUpperCase();
    return locType === 'FNB' || locType === 'OUTLET' || locType === 'F&B';
  });
  const displayLocations = fnbLocations.length > 0 ? fnbLocations : locations;

  // Outlet Tab Selector Pagination
  const outletTabPageCount = Math.ceil(displayLocations.length / outletTabPerPage) || 1;
  const paginatedOutletTabs = displayLocations.slice(
    (currentOutletTabPage - 1) * outletTabPerPage,
    currentOutletTabPage * outletTabPerPage
  );

  // Outlets Showcase Section Pagination
  const outletPageCount = Math.ceil(displayLocations.length / outletsPerPage) || 1;
  const paginatedOutlets = displayLocations.slice(
    (currentOutletPage - 1) * outletsPerPage,
    currentOutletPage * outletsPerPage
  );

  // Only show products belonging to FNB categories
  const allowedCatIds = categories.map((c) => c.id);
  const fnbProducts = products.filter(
    (p) => allowedCatIds.length === 0 || allowedCatIds.includes(p.category_id)
  );

  // Filter products by category AND selected location
  const filteredProducts = fnbProducts.filter((p) => {
    const matchCat = selectedCat ? p.category_id === selectedCat : true;
    const matchLoc = selectedLocId ? (p.location_id === selectedLocId || p.outlet_id === selectedLocId) : true;
    return matchCat && matchLoc;
  });

  // Menu List Pagination
  const menuPageCount = Math.ceil(filteredProducts.length / menuPerPage) || 1;
  const paginatedProducts = filteredProducts.slice(
    (currentMenuPage - 1) * menuPerPage,
    currentMenuPage * menuPerPage
  );

  useEffect(() => {
    setCurrentMenuPage(1);
  }, [selectedCat, selectedLocId]);

  useEffect(() => {
    setCurrentOutletTabPage(1);
    setCurrentOutletPage(1);
  }, [locations]);

  const formatCurrency = (val) => {
    return `Rp ${(val || 0).toLocaleString('id-ID')}`;
  };

  return (
    <View style={styles.container}>
      {/* Top Header */}
      <View style={styles.header}>
        <View style={styles.brandContainer}>
          <View style={styles.logoBadge}>
            <Text style={styles.logoLetter}>L</Text>
          </View>
          <View>
            <Text style={styles.brandName}>{t('landing.brandName')}</Text>
            <Text style={styles.brandSubtitle}>{t('landing.brandSubtitle')}</Text>
          </View>
        </View>

        <View style={styles.headerActions}>
          {/* Language Switcher */}
          <TouchableOpacity style={styles.langBtn} onPress={toggleLanguage}>
            <Globe size={14} color="#C9A96E" />
            <Text style={styles.langBtnText}>{currentLang.toUpperCase()}</Text>
          </TouchableOpacity>

          {/* POS Login / Dashboard Button */}
          <TouchableOpacity
            style={styles.posNavBtn}
            onPress={() => {
              if (user) {
                navigation.navigate('PosTab');
              } else {
                navigation.navigate('Login');
              }
            }}
          >
            <LogIn size={14} color="#FFFFFF" style={{ marginRight: 4 }} />
            <Text style={styles.posNavText}>
              {user ? t('nav.pos') : t('nav.login')}
            </Text>
          </TouchableOpacity>
        </View>
      </View>

      <ScrollView style={styles.scrollBody} contentContainerStyle={{ paddingBottom: 40 }}>
        {/* HERO SECTION */}
        <View style={styles.heroSection}>
          <View style={styles.badgeChip}>
            <Sparkles size={12} color="#C9A96E" style={{ marginRight: 6 }} />
            <Text style={styles.badgeText}>{t('landing.badge')}</Text>
          </View>

          <Text style={styles.heroTitle1}>
            {t('landing.heroTitle1')}{'\n'}
            <Text style={styles.heroTitle2}>{t('landing.heroTitle2')}</Text>
          </Text>

          <Text style={styles.heroTagline}>{t('landing.heroTagline')}</Text>

          {/* Hero Visual Card */}
          <View style={styles.heroImageCard}>
            <Image
              source={{ uri: 'https://images.unsplash.com/photo-1554118811-1e0d58224f24?auto=format&fit=crop&w=800&q=80' }}
              style={styles.heroImage}
              resizeMode="cover"
            />
            <View style={styles.heroImageOverlay}>
              <Text style={styles.heroImageTitle}>L'ÉTOILE Signature Experience</Text>
              <Text style={styles.heroImageSubtitle}>White Marble • Dark Wood • Fine Coffee</Text>
            </View>
          </View>

          {/* Order Search Box */}
          <View style={styles.searchOrderCard}>
            <Search size={18} color="#C9A96E" style={{ marginRight: 8 }} />
            <TextInput
              style={styles.searchOrderInput}
              placeholder={t('landing.searchOrderPlaceholder')}
              placeholderTextColor="#94A3B8"
              value={searchTxNo}
              onChangeText={setSearchTxNo}
              autoCapitalize="characters"
            />
            <TouchableOpacity
              style={styles.searchOrderBtn}
              onPress={handleSearchOrder}
              disabled={searchingTx}
            >
              {searchingTx ? (
                <ActivityIndicator size="small" color="#F9F6F0" />
              ) : (
                <Text style={styles.searchOrderBtnText}>{t('landing.checkOrderStatus')}</Text>
              )}
            </TouchableOpacity>
          </View>
        </View>

        {/* STAFF & POS PORTAL CARD */}
        <View style={styles.portalCard}>
          <View style={{ flex: 1 }}>
            <View style={styles.portalBadge}>
              <Store size={14} color="#2563EB" style={{ marginRight: 4 }} />
              <Text style={styles.portalBadgeText}>POS System</Text>
            </View>
            <Text style={styles.portalTitle}>{t('landing.posPortalTitle')}</Text>
            <Text style={styles.portalDesc}>{t('landing.posPortalDesc')}</Text>
          </View>

          <TouchableOpacity
            style={styles.portalActionBtn}
            onPress={() => {
              if (user) {
                navigation.navigate('PosTab');
              } else {
                navigation.navigate('Login');
              }
            }}
          >
            <Text style={styles.portalActionText}>{t('landing.enterPos')}</Text>
            <ChevronRight size={16} color="#FFFFFF" />
          </TouchableOpacity>
        </View>

        {/* OUTLETS & LOCATIONS SHOWCASE (MOVED ABOVE MENU) */}
        <View style={styles.sectionHeader}>
          <Text style={styles.sectionTitle}>{t('landing.outletsTitle')}</Text>
        </View>

        <View style={styles.outletsList}>
          {paginatedOutlets.map((loc) => (
            <View key={loc.id} style={styles.outletCard}>
              <MapPin size={22} color="#6B2E3E" style={{ marginRight: 12 }} />
              <View style={{ flex: 1 }}>
                <Text style={styles.outletName}>{loc.name}</Text>
                <Text style={styles.outletType}>
                  {t('landing.locationType')}: {loc.type || 'OUTLET'}
                </Text>
              </View>
              <View style={styles.openBadge}>
                <Text style={styles.openText}>● {t('landing.openNow')}</Text>
              </View>
            </View>
          ))}
        </View>

        {/* Outlets Section Pagination */}
        {outletPageCount > 1 && (
          <View style={styles.paginationContainer}>
            <TouchableOpacity
              style={[styles.pageBtn, currentOutletPage === 1 && styles.pageBtnDisabled]}
              disabled={currentOutletPage === 1}
              onPress={() => setCurrentOutletPage((prev) => Math.max(1, prev - 1))}
            >
              <Text style={[styles.pageBtnText, currentOutletPage === 1 && styles.pageBtnTextDisabled]}>‹</Text>
            </TouchableOpacity>
            <Text style={styles.pageIndicator}>
              {currentOutletPage} / {outletPageCount}
            </Text>
            <TouchableOpacity
              style={[styles.pageBtn, currentOutletPage === outletPageCount && styles.pageBtnDisabled]}
              disabled={currentOutletPage === outletPageCount}
              onPress={() => setCurrentOutletPage((prev) => Math.min(outletPageCount, prev + 1))}
            >
              <Text style={[styles.pageBtnText, currentOutletPage === outletPageCount && styles.pageBtnTextDisabled]}>›</Text>
            </TouchableOpacity>
          </View>
        )}

        {/* MENU SHOWCASE SECTION */}
        <View style={styles.sectionHeader}>
          <Text style={styles.sectionTitle}>{t('landing.menuTitle')}</Text>
        </View>

        {/* 📍 Select F&B Outlet Selector Tabs (with pagination) */}
        {displayLocations.length > 0 && (
          <View style={styles.outletSelectorBox}>
            <View style={styles.outletTabHeader}>
              <Text style={styles.outletTabTitle}>📍 Select F&B Outlet:</Text>
              {outletTabPageCount > 1 && (
                <View style={styles.miniPagination}>
                  <TouchableOpacity
                    style={[styles.miniPageBtn, currentOutletTabPage === 1 && styles.miniPageBtnDisabled]}
                    disabled={currentOutletTabPage === 1}
                    onPress={() => setCurrentOutletTabPage((prev) => Math.max(1, prev - 1))}
                  >
                    <Text style={[styles.miniPageBtnText, currentOutletTabPage === 1 && styles.miniPageBtnTextDisabled]}>‹</Text>
                  </TouchableOpacity>
                  <Text style={styles.miniPageIndicator}>
                    {currentOutletTabPage} / {outletTabPageCount}
                  </Text>
                  <TouchableOpacity
                    style={[styles.miniPageBtn, currentOutletTabPage === outletTabPageCount && styles.miniPageBtnDisabled]}
                    disabled={currentOutletTabPage === outletTabPageCount}
                    onPress={() => setCurrentOutletTabPage((prev) => Math.min(outletTabPageCount, prev + 1))}
                  >
                    <Text style={[styles.miniPageBtnText, currentOutletTabPage === outletTabPageCount && styles.miniPageBtnTextDisabled]}>›</Text>
                  </TouchableOpacity>
                </View>
              )}
            </View>

            <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.outletTabScroll}>
              <TouchableOpacity
                style={[styles.outletChip, selectedLocId === null && styles.outletChipActive]}
                onPress={() => setSelectedLocId(null)}
              >
                <Text style={[styles.outletChipText, selectedLocId === null && styles.outletChipTextActive]}>
                  Semua Outlet
                </Text>
              </TouchableOpacity>
              {paginatedOutletTabs.map((loc) => (
                <TouchableOpacity
                  key={loc.id}
                  style={[styles.outletChip, selectedLocId === loc.id && styles.outletChipActive]}
                  onPress={() => setSelectedLocId(loc.id)}
                >
                  <Text style={[styles.outletChipText, selectedLocId === loc.id && styles.outletChipTextActive]}>
                    {loc.name}
                  </Text>
                </TouchableOpacity>
              ))}
            </ScrollView>
          </View>
        )}

        {/* Categories Bar */}
        <ScrollView
          horizontal
          showsHorizontalScrollIndicator={false}
          contentContainerStyle={styles.catScroll}
        >
          <TouchableOpacity
            style={[styles.catChip, selectedCat === null && styles.catChipActive]}
            onPress={() => setSelectedCat(null)}
          >
            <Text style={[styles.catText, selectedCat === null && styles.catTextActive]}>
              {t('pos.allCategories')}
            </Text>
          </TouchableOpacity>

          {categories.map((c) => (
            <TouchableOpacity
              key={c.id}
              style={[styles.catChip, selectedCat === c.id && styles.catChipActive]}
              onPress={() => setSelectedCat(c.id)}
            >
              <Text style={[styles.catText, selectedCat === c.id && styles.catTextActive]}>
                {c.name}
              </Text>
            </TouchableOpacity>
          ))}
        </ScrollView>

        {/* Products Grid */}
        {loading ? (
          <ActivityIndicator color="#C9A96E" style={{ marginVertical: 20 }} />
        ) : (
          <View>
            <View style={styles.menuGrid}>
              {paginatedProducts.map((p) => (
                <View key={p.id} style={styles.menuCard}>
                  <View style={styles.menuIconBox}>
                    <Coffee size={24} color="#C9A96E" />
                  </View>
                  <View style={{ flex: 1, marginLeft: 12 }}>
                    <Text style={styles.menuName}>{p.name}</Text>
                    <Text style={styles.menuCategory}>
                      {p.category?.name || 'Culinary'}
                    </Text>
                    <Text style={styles.menuPrice}>
                      {formatCurrency(parseFloat(p.selling_price))}
                    </Text>
                  </View>
                </View>
              ))}
            </View>

            {/* Menu List Pagination */}
            {menuPageCount > 1 && (
              <View style={styles.paginationContainer}>
                <TouchableOpacity
                  style={[styles.pageBtn, currentMenuPage === 1 && styles.pageBtnDisabled]}
                  disabled={currentMenuPage === 1}
                  onPress={() => setCurrentMenuPage((prev) => Math.max(1, prev - 1))}
                >
                  <Text style={[styles.pageBtnText, currentMenuPage === 1 && styles.pageBtnTextDisabled]}>‹</Text>
                </TouchableOpacity>
                <Text style={styles.pageIndicator}>
                  {currentMenuPage} / {menuPageCount}
                </Text>
                <TouchableOpacity
                  style={[styles.pageBtn, currentMenuPage === menuPageCount && styles.pageBtnDisabled]}
                  disabled={currentMenuPage === menuPageCount}
                  onPress={() => setCurrentMenuPage((prev) => Math.min(menuPageCount, prev + 1))}
                >
                  <Text style={[styles.pageBtnText, currentMenuPage === menuPageCount && styles.pageBtnTextDisabled]}>›</Text>
                </TouchableOpacity>
              </View>
            )}
          </View>
        )}
      </ScrollView>

      {/* ORDER SEARCH DETAIL MODAL */}
      <Modal
        visible={showOrderModal}
        animationType="fade"
        transparent={true}
        onRequestClose={() => setShowOrderModal(false)}
      >
        <View style={styles.modalOverlay}>
          <View style={styles.modalCard}>
            <View style={styles.modalHeader}>
              <CheckCircle size={36} color="#10B981" />
              <Text style={styles.modalOrderTitle}>{t('landing.orderFound')}</Text>
              <Text style={styles.modalTxNo}>{foundTx?.transaction_no}</Text>
              <TouchableOpacity
                onPress={() => setShowOrderModal(false)}
                style={styles.modalCloseBtn}
              >
                <X size={20} color="#94A3B8" />
              </TouchableOpacity>
            </View>

            <View style={styles.modalDivider} />

            <ScrollView style={{ maxHeight: 200 }}>
              {foundTx?.items?.map((it, idx) => (
                <View key={idx} style={styles.orderItemRow}>
                  <Text style={styles.orderItemName}>{it.product?.name || it.name}</Text>
                  <Text style={styles.orderItemQty}>x{it.quantity}</Text>
                  <Text style={styles.orderItemPrice}>
                    {formatCurrency((it.price || 0) * (it.quantity || 1))}
                  </Text>
                </View>
              ))}
            </ScrollView>

            <View style={styles.modalDivider} />

            <View style={styles.summaryRow}>
              <Text style={styles.summaryLabel}>{t('common.status')}:</Text>
              <Text style={styles.statusCompletedText}>
                {foundTx?.status?.toUpperCase() || 'COMPLETED'}
              </Text>
            </View>
            <View style={styles.summaryRow}>
              <Text style={styles.summaryLabel}>{t('pos.total')}:</Text>
              <Text style={styles.summaryValText}>{formatCurrency(foundTx?.total)}</Text>
            </View>
          </View>
        </View>
      </Modal>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#F9F6F0',
  },
  header: {
    backgroundColor: '#1E1E1E',
    paddingHorizontal: 16,
    paddingVertical: 12,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    borderBottomWidth: 1,
    borderBottomColor: '#C9A96E',
  },
  brandContainer: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  logoBadge: {
    width: 36,
    height: 36,
    borderRadius: 18,
    borderWidth: 1,
    borderColor: '#C9A96E',
    backgroundColor: '#2C2C2C',
    alignItems: 'center',
    justifyContent: 'center',
    marginRight: 10,
  },
  logoLetter: {
    fontFamily: 'serif',
    fontSize: 20,
    fontWeight: '700',
    color: '#C9A96E',
  },
  brandName: {
    fontSize: 16,
    fontWeight: '700',
    color: '#F9F6F0',
    letterSpacing: 1,
  },
  brandSubtitle: {
    fontSize: 10,
    color: '#C9A96E',
    letterSpacing: 0.5,
  },
  headerActions: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
  },
  langBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 4,
    paddingHorizontal: 8,
    borderRadius: 12,
    borderWidth: 1,
    borderColor: '#C9A96E',
    gap: 4,
  },
  langBtnText: {
    fontSize: 11,
    fontWeight: '700',
    color: '#C9A96E',
  },
  posNavBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#6B2E3E',
    paddingVertical: 6,
    paddingHorizontal: 12,
    borderRadius: 12,
  },
  posNavText: {
    fontSize: 12,
    fontWeight: '700',
    color: '#FFFFFF',
  },
  scrollBody: {
    flex: 1,
  },
  heroSection: {
    backgroundColor: '#1E1E1E',
    padding: 24,
    alignItems: 'center',
    borderBottomLeftRadius: 24,
    borderBottomRightRadius: 24,
  },
  badgeChip: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: 'rgba(201, 169, 110, 0.15)',
    borderWidth: 1,
    borderColor: '#C9A96E',
    paddingVertical: 4,
    paddingHorizontal: 12,
    borderRadius: 20,
    marginBottom: 14,
  },
  badgeText: {
    color: '#C9A96E',
    fontSize: 12,
    fontWeight: '600',
    letterSpacing: 0.5,
  },
  heroTitle1: {
    fontSize: 26,
    fontWeight: '700',
    fontFamily: 'serif',
    color: '#F9F6F0',
    textAlign: 'center',
    marginBottom: 10,
    lineHeight: 34,
  },
  heroTitle2: {
    color: '#C9A96E',
  },
  heroTagline: {
    fontSize: 13,
    color: '#E5D9C5',
    textAlign: 'center',
    lineHeight: 20,
    marginBottom: 16,
    maxWidth: 320,
  },
  heroImageCard: {
    width: '100%',
    maxWidth: 360,
    height: 180,
    borderRadius: 16,
    overflow: 'hidden',
    borderWidth: 1,
    borderColor: 'rgba(201, 169, 110, 0.4)',
    marginBottom: 20,
    position: 'relative',
    backgroundColor: '#2C2C2C',
  },
  heroImage: {
    width: '100%',
    height: '100%',
    opacity: 0.85,
  },
  heroImageOverlay: {
    position: 'absolute',
    bottom: 0,
    left: 0,
    right: 0,
    backgroundColor: 'rgba(30, 30, 30, 0.75)',
    paddingVertical: 8,
    paddingHorizontal: 12,
    alignItems: 'center',
  },
  heroImageTitle: {
    fontFamily: 'serif',
    fontSize: 14,
    fontWeight: '700',
    color: '#C9A96E',
    fontStyle: 'italic',
  },
  heroImageSubtitle: {
    fontSize: 10,
    color: '#E5D9C5',
    letterSpacing: 0.5,
    marginTop: 2,
  },
  searchOrderCard: {
    width: '100%',
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#2C2C2C',
    borderRadius: 14,
    paddingHorizontal: 12,
    height: 48,
    borderWidth: 1,
    borderColor: '#C9A96E',
  },
  searchOrderInput: {
    flex: 1,
    color: '#F9F6F0',
    fontSize: 13,
  },
  searchOrderBtn: {
    backgroundColor: '#C9A96E',
    paddingVertical: 8,
    paddingHorizontal: 12,
    borderRadius: 10,
  },
  searchOrderBtnText: {
    color: '#1E1E1E',
    fontSize: 12,
    fontWeight: '700',
  },
  portalCard: {
    margin: 16,
    backgroundColor: '#FFFFFF',
    borderRadius: 18,
    padding: 16,
    borderWidth: 1,
    borderColor: '#E2E8F0',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05,
    shadowRadius: 6,
    elevation: 2,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
  },
  portalBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#EFF6FF',
    paddingVertical: 2,
    paddingHorizontal: 8,
    borderRadius: 8,
    alignSelf: 'flex-start',
    marginBottom: 6,
  },
  portalBadgeText: {
    fontSize: 11,
    fontWeight: '700',
    color: '#2563EB',
  },
  portalTitle: {
    fontSize: 15,
    fontWeight: '700',
    color: '#0F172A',
  },
  portalDesc: {
    fontSize: 12,
    color: '#64748B',
    marginTop: 2,
    maxWidth: 200,
  },
  portalActionBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#2563EB',
    paddingVertical: 10,
    paddingHorizontal: 14,
    borderRadius: 12,
    gap: 4,
  },
  portalActionText: {
    color: '#FFFFFF',
    fontSize: 13,
    fontWeight: '700',
  },
  sectionHeader: {
    paddingHorizontal: 16,
    marginTop: 16,
    marginBottom: 8,
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: '#2C2C2C',
    fontFamily: 'serif',
  },
  catScroll: {
    paddingHorizontal: 16,
    gap: 8,
    marginBottom: 12,
  },
  catChip: {
    paddingVertical: 6,
    paddingHorizontal: 14,
    borderRadius: 20,
    backgroundColor: '#E5D9C5',
  },
  catChipActive: {
    backgroundColor: '#6B2E3E',
  },
  catText: {
    fontSize: 13,
    fontWeight: '600',
    color: '#2C2C2C',
  },
  catTextActive: {
    color: '#F9F6F0',
  },
  menuGrid: {
    paddingHorizontal: 16,
    gap: 10,
  },
  menuCard: {
    backgroundColor: '#FFFFFF',
    borderRadius: 14,
    padding: 12,
    flexDirection: 'row',
    alignItems: 'center',
    borderWidth: 1,
    borderColor: '#E5D9C5',
  },
  menuIconBox: {
    width: 48,
    height: 48,
    borderRadius: 12,
    backgroundColor: '#1E1E1E',
    alignItems: 'center',
    justifyContent: 'center',
  },
  menuName: {
    fontSize: 14,
    fontWeight: '700',
    color: '#2C2C2C',
  },
  menuCategory: {
    fontSize: 11,
    color: '#64748B',
    marginTop: 2,
  },
  menuPrice: {
    fontSize: 14,
    fontWeight: '700',
    color: '#C9A96E',
    marginTop: 4,
  },
  outletsList: {
    paddingHorizontal: 16,
    gap: 10,
  },
  outletCard: {
    backgroundColor: '#FFFFFF',
    borderRadius: 14,
    padding: 14,
    flexDirection: 'row',
    alignItems: 'center',
    borderWidth: 1,
    borderColor: '#E5D9C5',
  },
  outletName: {
    fontSize: 14,
    fontWeight: '700',
    color: '#2C2C2C',
  },
  outletType: {
    fontSize: 12,
    color: '#64748B',
    marginTop: 2,
  },
  openBadge: {
    backgroundColor: '#DCFCE7',
    paddingVertical: 4,
    paddingHorizontal: 8,
    borderRadius: 10,
  },
  openText: {
    fontSize: 11,
    fontWeight: '700',
    color: '#16A34A',
  },

  /* Order Modal */
  modalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.6)',
    alignItems: 'center',
    justifyContent: 'center',
    padding: 20,
  },
  modalCard: {
    width: '100%',
    maxWidth: 380,
    backgroundColor: '#FFFFFF',
    borderRadius: 20,
    padding: 20,
  },
  modalHeader: {
    alignItems: 'center',
  },
  modalOrderTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: '#0F172A',
    marginTop: 8,
  },
  modalTxNo: {
    fontSize: 13,
    color: '#64748B',
    marginTop: 2,
  },
  modalCloseBtn: {
    position: 'absolute',
    top: 0,
    right: 0,
    padding: 4,
  },
  modalDivider: {
    height: 1,
    backgroundColor: '#E2E8F0',
    marginVertical: 12,
  },
  orderItemRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    paddingVertical: 4,
  },
  orderItemName: {
    flex: 2,
    fontSize: 13,
    color: '#334155',
  },
  orderItemQty: {
    flex: 1,
    fontSize: 13,
    color: '#64748B',
    textAlign: 'center',
  },
  orderItemPrice: {
    flex: 1,
    fontSize: 13,
    fontWeight: '600',
    color: '#0F172A',
    textAlign: 'right',
  },
  summaryRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginTop: 4,
  },
  summaryLabel: {
    fontSize: 13,
    color: '#64748B',
  },
  statusCompletedText: {
    fontSize: 13,
    fontWeight: '700',
    color: '#16A34A',
  },
  summaryValText: {
    fontSize: 15,
    fontWeight: '800',
    color: '#2563EB',
  },

  /* Pagination Styles */
  paginationContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    marginVertical: 16,
    gap: 16,
  },
  pageBtn: {
    width: 36,
    height: 36,
    borderRadius: 18,
    borderWidth: 1,
    borderColor: '#C9A96E',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#F9F6F0',
  },
  pageBtnDisabled: {
    borderColor: '#CBD5E1',
    opacity: 0.4,
  },
  pageBtnText: {
    fontSize: 18,
    fontWeight: '600',
    color: '#C9A96E',
    lineHeight: 20,
  },
  pageBtnTextDisabled: {
    color: '#94A3B8',
  },
  pageIndicator: {
    fontSize: 13,
    fontWeight: '600',
    color: '#475569',
    minWidth: 50,
    textAlign: 'center',
  },

  /* Outlet Selector Box */
  outletSelectorBox: {
    paddingHorizontal: 16,
    marginBottom: 12,
  },
  outletTabHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    marginBottom: 8,
  },
  outletTabTitle: {
    fontSize: 12,
    fontWeight: '700',
    color: '#334155',
    textTransform: 'uppercase',
    letterSpacing: 0.5,
  },
  miniPagination: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
  },
  miniPageBtn: {
    width: 26,
    height: 26,
    borderRadius: 13,
    borderWidth: 1,
    borderColor: '#C9A96E',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#F9F6F0',
  },
  miniPageBtnDisabled: {
    borderColor: '#CBD5E1',
    opacity: 0.3,
  },
  miniPageBtnText: {
    fontSize: 14,
    fontWeight: '600',
    color: '#C9A96E',
    lineHeight: 16,
  },
  miniPageBtnTextDisabled: {
    color: '#94A3B8',
  },
  miniPageIndicator: {
    fontSize: 11,
    fontWeight: '600',
    color: '#64748B',
  },
  outletTabScroll: {
    flexDirection: 'row',
    gap: 8,
    paddingVertical: 4,
  },
  outletChip: {
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 12,
    borderWidth: 1,
    borderColor: '#E2E8F0',
    backgroundColor: '#FFFFFF',
  },
  outletChipActive: {
    backgroundColor: '#1E1E1E',
    borderColor: '#1E1E1E',
  },
  outletChipText: {
    fontSize: 12,
    fontWeight: '600',
    color: '#475569',
  },
  outletChipTextActive: {
    color: '#F9F6F0',
  },
});
