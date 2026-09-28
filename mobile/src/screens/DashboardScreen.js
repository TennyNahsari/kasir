import React, { useState, useEffect, useContext } from 'react';
import {
  StyleSheet,
  View,
  Text,
  ScrollView,
  RefreshControl,
  ActivityIndicator,
} from 'react-native';
import { useTranslation } from 'react-i18next';
import { AuthContext } from '../context/AuthContext';
import api from '../services/api';
import {
  TrendingUp,
  ShoppingBag,
  DollarSign,
  Award,
  Calendar,
} from 'lucide-react-native';

export default function DashboardScreen() {
  const { t } = useTranslation();
  const { user } = useContext(AuthContext);

  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [stats, setStats] = useState({
    total_revenue: 0,
    total_transactions: 0,
    average_transaction: 0,
    top_products: [],
  });

  const fetchDashboardData = async () => {
    try {
      const res = await api.get('/dashboard');
      const data = res.data;

      const todayStats = data.today || {};
      setStats({
        total_revenue: todayStats.total_revenue || 0,
        total_transactions: todayStats.total_transactions || 0,
        average_transaction: todayStats.average_transaction || 0,
        top_products: data.top_products || [],
      });
    } catch (error) {
      console.error('Error fetching dashboard stats:', error);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  useEffect(() => {
    fetchDashboardData();
  }, []);

  const onRefresh = () => {
    setRefreshing(true);
    fetchDashboardData();
  };

  const formatCurrency = (val) => {
    return `Rp ${(val || 0).toLocaleString('id-ID')}`;
  };

  if (loading) {
    return (
      <View style={styles.centerContainer}>
        <ActivityIndicator size="large" color="#2563EB" />
        <Text style={styles.loadingText}>{t('common.loading')}</Text>
      </View>
    );
  }

  return (
    <ScrollView
      style={styles.container}
      contentContainerStyle={styles.scrollContent}
      refreshControl={
        <RefreshControl refreshing={refreshing} onRefresh={onRefresh} colors={['#2563EB']} />
      }
    >
      {/* Welcome Banner */}
      <View style={styles.banner}>
        <View style={{ flex: 1 }}>
          <Text style={styles.welcomeText}>
            Hi, {user?.name || 'Kasir'} 👋
          </Text>
          <Text style={styles.outletBadge}>
            📍 {user?.outlet?.name || user?.location?.name || 'Main Outlet'}
          </Text>
        </View>
        <View style={styles.dateChip}>
          <Calendar size={14} color="#64748B" style={{ marginRight: 4 }} />
          <Text style={styles.dateText}>
            {new Date().toLocaleDateString('id-ID', {
              day: 'numeric',
              month: 'short',
            })}
          </Text>
        </View>
      </View>

      {/* KPI Cards Grid */}
      <Text style={styles.sectionTitle}>{t('dashboard.title')}</Text>
      <View style={styles.kpiGrid}>
        {/* Revenue Card */}
        <View style={[styles.kpiCard, styles.kpiCardBlue]}>
          <View style={styles.kpiHeader}>
            <Text style={styles.kpiLabel}>{t('dashboard.todayRevenue')}</Text>
            <View style={[styles.iconCircle, { backgroundColor: '#DBEAFE' }]}>
              <TrendingUp size={20} color="#2563EB" />
            </View>
          </View>
          <Text style={styles.kpiValue}>{formatCurrency(stats.total_revenue)}</Text>
        </View>

        {/* Transactions Count */}
        <View style={[styles.kpiCard, styles.kpiCardGreen]}>
          <View style={styles.kpiHeader}>
            <Text style={styles.kpiLabel}>{t('dashboard.totalTransactions')}</Text>
            <View style={[styles.iconCircle, { backgroundColor: '#D1FAE5' }]}>
              <ShoppingBag size={20} color="#10B981" />
            </View>
          </View>
          <Text style={styles.kpiValue}>{stats.total_transactions}</Text>
        </View>

        {/* Average Order Card */}
        <View style={[styles.kpiCard, styles.kpiCardOrange]}>
          <View style={styles.kpiHeader}>
            <Text style={styles.kpiLabel}>{t('dashboard.averageTransaction')}</Text>
            <View style={[styles.iconCircle, { backgroundColor: '#FFEDD5' }]}>
              <DollarSign size={20} color="#F97316" />
            </View>
          </View>
          <Text style={styles.kpiValue}>
            {formatCurrency(stats.average_transaction)}
          </Text>
        </View>
      </View>

      {/* Top Products Section */}
      <Text style={[styles.sectionTitle, { marginTop: 24 }]}>
        {t('dashboard.topProducts')}
      </Text>
      {stats.top_products.length === 0 ? (
        <View style={styles.emptyBox}>
          <Text style={styles.emptyText}>{t('dashboard.noDataYet')}</Text>
        </View>
      ) : (
        <View style={styles.topProductsList}>
          {stats.top_products.map((item, index) => (
            <View key={item.id || index} style={styles.topProductRow}>
              <View style={styles.rankBadge}>
                <Text style={styles.rankText}>#{index + 1}</Text>
              </View>
              <View style={{ flex: 1, marginLeft: 12 }}>
                <Text style={styles.topProductName}>{item.name}</Text>
                <Text style={styles.topProductQty}>
                  {item.total_quantity || 0} {t('dashboard.sold')}
                </Text>
              </View>
              <Text style={styles.topProductRevenue}>
                {formatCurrency(item.total_revenue || 0)}
              </Text>
            </View>
          ))}
        </View>
      )}
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
  centerContainer: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
  },
  loadingText: {
    marginTop: 10,
    color: '#64748B',
  },
  banner: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#FFFFFF',
    padding: 16,
    borderRadius: 16,
    marginBottom: 20,
    borderWidth: 1,
    borderColor: '#E5D9C5',
  },
  welcomeText: {
    fontSize: 18,
    fontWeight: '700',
    color: '#2C2C2C',
    fontFamily: 'serif',
  },
  outletBadge: {
    fontSize: 13,
    color: '#5A5A5A',
    marginTop: 2,
  },
  dateChip: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#F9F6F0',
    borderWidth: 1,
    borderColor: '#E5D9C5',
    paddingVertical: 6,
    paddingHorizontal: 10,
    borderRadius: 20,
  },
  dateText: {
    fontSize: 12,
    fontWeight: '600',
    color: '#6B2E3E',
  },
  sectionTitle: {
    fontSize: 16,
    fontWeight: '700',
    color: '#2C2C2C',
    marginBottom: 12,
    fontFamily: 'serif',
  },
  kpiGrid: {
    gap: 12,
  },
  kpiCard: {
    backgroundColor: '#FFFFFF',
    borderRadius: 16,
    padding: 16,
    borderWidth: 1,
    borderColor: '#E5D9C5',
  },
  kpiHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    marginBottom: 8,
  },
  kpiLabel: {
    fontSize: 13,
    fontWeight: '600',
    color: '#5A5A5A',
  },
  iconCircle: {
    width: 36,
    height: 36,
    borderRadius: 18,
    alignItems: 'center',
    justifyContent: 'center',
  },
  kpiValue: {
    fontSize: 22,
    fontWeight: '800',
    color: '#6B2E3E',
  },
  topProductsList: {
    backgroundColor: '#FFFFFF',
    borderRadius: 16,
    padding: 12,
    borderWidth: 1,
    borderColor: '#E5D9C5',
    gap: 10,
  },
  topProductRow: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 8,
    borderBottomWidth: 1,
    borderBottomColor: '#F9F6F0',
  },
  rankBadge: {
    width: 28,
    height: 28,
    borderRadius: 8,
    backgroundColor: '#1E1E1E',
    alignItems: 'center',
    justifyContent: 'center',
  },
  rankText: {
    fontSize: 12,
    fontWeight: '700',
    color: '#C9A96E',
  },
  topProductName: {
    fontSize: 14,
    fontWeight: '600',
    color: '#2C2C2C',
  },
  topProductQty: {
    fontSize: 12,
    color: '#64748B',
  },
  topProductRevenue: {
    fontSize: 14,
    fontWeight: '700',
    color: '#C9A96E',
  },
  emptyBox: {
    backgroundColor: '#FFFFFF',
    borderRadius: 16,
    padding: 24,
    alignItems: 'center',
    borderWidth: 1,
    borderColor: '#E5D9C5',
  },
  emptyText: {
    color: '#94A3B8',
    fontSize: 14,
  },
});
