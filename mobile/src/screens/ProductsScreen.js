import React, { useState, useEffect } from 'react';
import {
  StyleSheet,
  View,
  Text,
  TextInput,
  TouchableOpacity,
  FlatList,
  ScrollView,
  ActivityIndicator,
  RefreshControl,
} from 'react-native';
import { useTranslation } from 'react-i18next';
import api from '../services/api';
import { Search, Package, Tag, ShieldCheck, AlertTriangle, X } from 'lucide-react-native';

export default function ProductsScreen() {
  const { t } = useTranslation();

  const [products, setProducts] = useState([]);
  const [categories, setCategories] = useState([]);
  const [selectedCategory, setSelectedCategory] = useState(null);
  const [searchQuery, setSearchQuery] = useState('');
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  // Pagination State
  const [currentPage, setCurrentPage] = useState(1);
  const perPage = 8;

  useEffect(() => {
    fetchData();
  }, []);

  const fetchData = async () => {
    try {
      setLoading(true);
      const [prodRes, catRes] = await Promise.all([
        api.get('/products?per_page=100'),
        api.get('/categories'),
      ]);

      setProducts(prodRes.data.data || prodRes.data || []);
      setCategories(catRes.data.data || catRes.data || []);
    } catch (error) {
      console.error('Error loading products:', error);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  const onRefresh = () => {
    setRefreshing(true);
    fetchData();
  };

  const filteredProducts = products.filter((p) => {
    const matchSearch =
      p.name?.toLowerCase().includes(searchQuery.toLowerCase()) ||
      p.sku?.toLowerCase().includes(searchQuery.toLowerCase());
    const matchCat = selectedCategory ? p.category_id === selectedCategory : true;
    return matchSearch && matchCat;
  });

  const totalPages = Math.ceil(filteredProducts.length / perPage) || 1;
  const paginatedProducts = filteredProducts.slice(
    (currentPage - 1) * perPage,
    currentPage * perPage
  );

  useEffect(() => {
    setCurrentPage(1);
  }, [searchQuery, selectedCategory]);

  const getVisiblePages = (total, current) => {
    if (total <= 5) {
      const pages = [];
      for (let i = 1; i <= total; i++) pages.push(i);
      return pages;
    }

    const pages = [];
    const firstPages = [1, 2];
    const lastPages = [total - 1, total];

    firstPages.forEach((p) => pages.push(p));

    const middlePages = [];
    for (let i = current - 1; i <= current + 1; i++) {
      if (i > 2 && i < total - 1) {
        middlePages.push(i);
      }
    }

    if (middlePages.length > 0 && middlePages[0] > 3) {
      pages.push('...');
    } else if (middlePages.length === 0 && current > 2 && current < total - 1) {
      pages.push('...');
    }

    middlePages.forEach((p) => {
      if (!pages.includes(p)) pages.push(p);
    });

    if (middlePages.length > 0 && middlePages[middlePages.length - 1] < total - 2) {
      pages.push('...');
    } else if (middlePages.length === 0 && (current <= 2 || current >= total - 1) && total > 4) {
      if (pages[pages.length - 1] !== '...') {
        pages.push('...');
      }
    }

    lastPages.forEach((p) => {
      if (!pages.includes(p)) pages.push(p);
    });

    return pages;
  };

  const formatCurrency = (val) => {
    return `Rp ${(val || 0).toLocaleString('id-ID')}`;
  };

  const renderProductRow = ({ item }) => (
    <View style={styles.card}>
      <View style={styles.cardHeader}>
        <View style={styles.iconBox}>
          <Package size={22} color="#2563EB" />
        </View>
        <View style={{ flex: 1, marginLeft: 10 }}>
          <Text style={styles.productName}>{item.name}</Text>
          <Text style={styles.skuText}>SKU: {item.sku || '-'}</Text>
        </View>
        <View style={styles.categoryBadge}>
          <Tag size={12} color="#64748B" style={{ marginRight: 4 }} />
          <Text style={styles.categoryText}>
            {item.category?.name || t('common.all')}
          </Text>
        </View>
      </View>

      <View style={styles.divider} />

      <View style={styles.cardFooter}>
        <View>
          <Text style={styles.priceLabel}>{t('products.price')}</Text>
          <Text style={styles.priceVal}>{formatCurrency(item.selling_price)}</Text>
        </View>

        <View style={{ alignItems: 'flex-end' }}>
          <Text style={styles.priceLabel}>{t('products.stockLevel')}</Text>
          <View style={styles.stockBox}>
            {item.stock <= (item.min_stock || 5) ? (
              <AlertTriangle size={14} color="#DC2626" style={{ marginRight: 4 }} />
            ) : (
              <ShieldCheck size={14} color="#16A34A" style={{ marginRight: 4 }} />
            )}
            <Text
              style={[
                styles.stockText,
                item.stock <= (item.min_stock || 5) && styles.lowStockText,
              ]}
            >
              {item.stock} {item.unit || 'pcs'}
            </Text>
          </View>
        </View>
      </View>
    </View>
  );

  return (
    <View style={styles.container}>
      {/* Header Search */}
      <View style={styles.header}>
        <View style={styles.searchBar}>
          <Search size={18} color="#64748B" style={{ marginRight: 8 }} />
          <TextInput
            style={styles.searchInput}
            placeholder={t('products.searchPlaceholder')}
            placeholderTextColor="#94A3B8"
            value={searchQuery}
            onChangeText={setSearchQuery}
          />
          {searchQuery ? (
            <TouchableOpacity onPress={() => setSearchQuery('')}>
              <X size={18} color="#94A3B8" />
            </TouchableOpacity>
          ) : null}
        </View>

        {/* Category Pills */}
        <ScrollView
          horizontal
          showsHorizontalScrollIndicator={false}
          contentContainerStyle={styles.catScroll}
        >
          <TouchableOpacity
            style={[styles.catChip, selectedCategory === null && styles.catChipActive]}
            onPress={() => setSelectedCategory(null)}
          >
            <Text style={[styles.catText, selectedCategory === null && styles.catTextActive]}>
              {t('pos.allCategories')}
            </Text>
          </TouchableOpacity>

          {categories.map((c) => (
            <TouchableOpacity
              key={c.id}
              style={[styles.catChip, selectedCategory === c.id && styles.catChipActive]}
              onPress={() => setSelectedCategory(c.id)}
            >
              <Text style={[styles.catText, selectedCategory === c.id && styles.catTextActive]}>
                {c.name}
              </Text>
            </TouchableOpacity>
          ))}
        </ScrollView>
      </View>

      {/* Products List */}
      {loading ? (
        <View style={styles.centerContainer}>
          <ActivityIndicator size="large" color="#2563EB" />
        </View>
      ) : filteredProducts.length === 0 ? (
        <View style={styles.centerContainer}>
          <Package size={48} color="#CBD5E1" />
          <Text style={styles.noDataText}>{t('pos.noProducts')}</Text>
        </View>
      ) : (
        <FlatList
          data={filteredProducts}
          keyExtractor={(item) => item.id.toString()}
          renderItem={renderProductRow}
          contentContainerStyle={styles.listContainer}
          refreshControl={
            <RefreshControl refreshing={refreshing} onRefresh={onRefresh} colors={['#2563EB']} />
          }
        />
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#F9F6F0',
  },
  header: {
    backgroundColor: '#FFFFFF',
    padding: 12,
    borderBottomWidth: 1,
    borderBottomColor: '#E5D9C5',
  },
  searchBar: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#F9F6F0',
    borderWidth: 1,
    borderColor: '#E5D9C5',
    borderRadius: 12,
    paddingHorizontal: 12,
    height: 44,
    marginBottom: 10,
  },
  searchInput: {
    flex: 1,
    fontSize: 14,
    color: '#2C2C2C',
  },
  catScroll: {
    gap: 8,
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
  listContainer: {
    padding: 12,
    gap: 10,
  },
  card: {
    backgroundColor: '#FFFFFF',
    borderRadius: 16,
    padding: 14,
    borderWidth: 1,
    borderColor: '#E5D9C5',
  },
  cardHeader: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  iconBox: {
    width: 40,
    height: 40,
    borderRadius: 12,
    backgroundColor: '#1E1E1E',
    alignItems: 'center',
    justifyContent: 'center',
  },
  productName: {
    fontSize: 14,
    fontWeight: '700',
    color: '#2C2C2C',
  },
  skuText: {
    fontSize: 12,
    color: '#64748B',
    marginTop: 2,
  },
  categoryBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#F9F6F0',
    borderWidth: 1,
    borderColor: '#E5D9C5',
    paddingVertical: 4,
    paddingHorizontal: 8,
    borderRadius: 10,
  },
  categoryText: {
    fontSize: 12,
    color: '#6B2E3E',
    fontWeight: '600',
  },
  divider: {
    height: 1,
    backgroundColor: '#F9F6F0',
    marginVertical: 10,
  },
  cardFooter: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
  },
  priceLabel: {
    fontSize: 11,
    color: '#64748B',
  },
  priceVal: {
    fontSize: 15,
    fontWeight: '700',
    color: '#C9A96E',
  },
  stockBox: {
    flexDirection: 'row',
    alignItems: 'center',
    marginTop: 2,
  },
  stockText: {
    fontSize: 13,
    fontWeight: '700',
    color: '#16A34A',
  },
  lowStockText: {
    color: '#DC2626',
  },
  centerContainer: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
  },
  noDataText: {
    marginTop: 10,
    color: '#94A3B8',
    fontSize: 15,
  },
});
