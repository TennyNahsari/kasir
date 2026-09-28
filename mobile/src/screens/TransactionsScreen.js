import React, { useState, useEffect } from 'react';
import {
  StyleSheet,
  View,
  Text,
  TextInput,
  TouchableOpacity,
  FlatList,
  Modal,
  ScrollView,
  ActivityIndicator,
  Alert,
} from 'react-native';
import { useTranslation } from 'react-i18next';
import api from '../services/api';
import {
  Search,
  Receipt,
  CheckCircle,
  XCircle,
  Clock,
  ChevronRight,
  X,
  Ban,
  Calendar,
} from 'lucide-react-native';

export default function TransactionsScreen() {
  const { t } = useTranslation();

  const [transactions, setTransactions] = useState([]);
  const [searchQuery, setSearchQuery] = useState('');
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  // Pagination State
  const [currentPage, setCurrentPage] = useState(1);
  const perPage = 8;

  // Detail Modal State
  const [selectedTx, setSelectedTx] = useState(null);
  const [showDetailModal, setShowDetailModal] = useState(false);
  const [voiding, setVoiding] = useState(false);

  useEffect(() => {
    fetchTransactions();
  }, []);

  const fetchTransactions = async () => {
    try {
      setLoading(true);
      const res = await api.get('/transactions?per_page=50');
      const data = res.data.data || res.data || [];
      setTransactions(data);
    } catch (error) {
      console.error('Error fetching transactions:', error);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  const onRefresh = () => {
    setRefreshing(true);
    fetchTransactions();
  };

  const filteredTransactions = transactions.filter((tx) => {
    const query = searchQuery.toLowerCase();
    return (
      tx.transaction_no?.toLowerCase().includes(query) ||
      tx.payment_method?.toLowerCase().includes(query) ||
      tx.user?.name?.toLowerCase().includes(query)
    );
  });

  const totalPages = Math.ceil(filteredTransactions.length / perPage) || 1;
  const paginatedTransactions = filteredTransactions.slice(
    (currentPage - 1) * perPage,
    currentPage * perPage
  );

  useEffect(() => {
    setCurrentPage(1);
  }, [searchQuery]);

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

  const handleOpenDetail = (tx) => {
    setSelectedTx(tx);
    setShowDetailModal(true);
  };

  const handleVoidTransaction = (tx) => {
    Alert.alert(
      t('transactions.voidTransaction'),
      t('transactions.voidConfirm'),
      [
        { text: t('common.cancel'), style: 'cancel' },
        {
          text: t('common.yes'),
          style: 'destructive',
          onPress: async () => {
            try {
              setVoiding(true);
              await api.post(`/transactions/${tx.id}/void`);
              Alert.alert(t('common.success'), t('transactions.voidSuccess'));
              setShowDetailModal(false);
              fetchTransactions();
            } catch (err) {
              Alert.alert(t('common.error'), err.response?.data?.message || 'Void failed');
            } finally {
              setVoiding(false);
            }
          },
        },
      ]
    );
  };

  const formatCurrency = (val) => {
    return `Rp ${(val || 0).toLocaleString('id-ID')}`;
  };

  const renderStatusBadge = (status) => {
    switch (status) {
      case 'completed':
        return (
          <View style={[styles.badge, styles.badgeSuccess]}>
            <CheckCircle size={12} color="#16A34A" style={{ marginRight: 4 }} />
            <Text style={[styles.badgeText, { color: '#16A34A' }]}>
              {t('transactions.statusCompleted')}
            </Text>
          </View>
        );
      case 'voided':
      case 'cancelled':
        return (
          <View style={[styles.badge, styles.badgeDanger]}>
            <XCircle size={12} color="#DC2626" style={{ marginRight: 4 }} />
            <Text style={[styles.badgeText, { color: '#DC2626' }]}>
              {t('transactions.statusVoided')}
            </Text>
          </View>
        );
      default:
        return (
          <View style={[styles.badge, styles.badgeWarning]}>
            <Clock size={12} color="#D97706" style={{ marginRight: 4 }} />
            <Text style={[styles.badgeText, { color: '#D97706' }]}>
              {t('transactions.statusPending')}
            </Text>
          </View>
        );
    }
  };

  const renderTxItem = ({ item }) => (
    <TouchableOpacity
      style={styles.txCard}
      onPress={() => handleOpenDetail(item)}
      activeOpacity={0.7}
    >
      <View style={styles.txHeader}>
        <View style={styles.txIconBox}>
          <Receipt size={20} color="#2563EB" />
        </View>
        <View style={{ flex: 1, marginLeft: 10 }}>
          <Text style={styles.txNo}>{item.transaction_no}</Text>
          <Text style={styles.txDate}>
            {new Date(item.created_at).toLocaleString('id-ID', {
              day: 'numeric',
              month: 'short',
              hour: '2-digit',
              minute: '2-digit',
            })}
          </Text>
        </View>
        {renderStatusBadge(item.status)}
      </View>

      <View style={styles.txDivider} />

      <View style={styles.txFooter}>
        <View>
          <Text style={styles.paymentMethodText}>
            💳 {item.payment_method?.toUpperCase()}
          </Text>
          <Text style={styles.cashierName}>
            👤 {item.user?.name || 'Kasir'}
          </Text>
        </View>
        <View style={{ alignItems: 'flex-end' }}>
          <Text style={styles.txTotal}>{formatCurrency(item.total)}</Text>
          <ChevronRight size={18} color="#94A3B8" />
        </View>
      </View>
    </TouchableOpacity>
  );

  return (
    <View style={styles.container}>
      {/* Search Header */}
      <View style={styles.header}>
        <View style={styles.searchBar}>
          <Search size={18} color="#64748B" style={{ marginRight: 8 }} />
          <TextInput
            style={styles.searchInput}
            placeholder={t('transactions.searchPlaceholder')}
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
      </View>

      {/* Transactions List */}
      {loading ? (
        <View style={styles.centerContainer}>
          <ActivityIndicator size="large" color="#2563EB" />
        </View>
      ) : filteredTransactions.length === 0 ? (
        <View style={styles.centerContainer}>
          <Receipt size={48} color="#CBD5E1" />
          <Text style={styles.noDataText}>{t('transactions.noTransactions')}</Text>
        </View>
      ) : (
        <FlatList
          data={filteredTransactions}
          keyExtractor={(item) => item.id.toString()}
          renderItem={renderTxItem}
          contentContainerStyle={styles.listContainer}
          refreshing={refreshing}
          onRefresh={onRefresh}
        />
      )}

      {/* DETAIL MODAL */}
      <Modal
        visible={showDetailModal}
        animationType="slide"
        transparent={false}
        onRequestClose={() => setShowDetailModal(false)}
      >
        {selectedTx && (
          <View style={styles.modalContainer}>
            <View style={styles.modalHeader}>
              <Text style={styles.modalTitle}>{t('transactions.detailTitle')}</Text>
              <TouchableOpacity
                onPress={() => setShowDetailModal(false)}
                style={styles.closeBtn}
              >
                <X size={24} color="#64748B" />
              </TouchableOpacity>
            </View>

            <ScrollView style={styles.modalBody}>
              <View style={styles.txInfoCard}>
                <Text style={styles.infoTxNo}>{selectedTx.transaction_no}</Text>
                <Text style={styles.infoDate}>
                  {new Date(selectedTx.created_at).toLocaleString('id-ID')}
                </Text>

                <View style={{ marginTop: 10 }}>
                  {renderStatusBadge(selectedTx.status)}
                </View>
              </View>

              {/* Items List */}
              <Text style={styles.sectionTitle}>{t('pos.cart')}</Text>
              <View style={styles.itemsBox}>
                {selectedTx.items?.map((it, idx) => (
                  <View key={idx} style={styles.itemRow}>
                    <View style={{ flex: 2 }}>
                      <Text style={styles.itemName}>{it.product?.name || it.name}</Text>
                      <Text style={styles.itemUnitPrice}>
                        {formatCurrency(it.price)}
                      </Text>
                    </View>
                    <Text style={styles.itemQty}>x{it.quantity}</Text>
                    <Text style={styles.itemSubtotal}>
                      {formatCurrency((it.price || 0) * (it.quantity || 1))}
                    </Text>
                  </View>
                ))}
              </View>

              {/* Summary Calculations */}
              <View style={styles.summaryCard}>
                <View style={styles.summaryRow}>
                  <Text style={styles.summaryLabel}>{t('pos.subtotal')}</Text>
                  <Text style={styles.summaryVal}>
                    {formatCurrency(selectedTx.subtotal)}
                  </Text>
                </View>
                <View style={styles.summaryRow}>
                  <Text style={styles.summaryLabel}>{t('pos.discount')}</Text>
                  <Text style={styles.summaryVal}>
                    -{formatCurrency(selectedTx.discount)}
                  </Text>
                </View>
                <View style={[styles.summaryRow, styles.totalRow]}>
                  <Text style={styles.totalLabel}>{t('pos.total')}</Text>
                  <Text style={styles.totalVal}>
                    {formatCurrency(selectedTx.total)}
                  </Text>
                </View>

                <View style={styles.summaryRow}>
                  <Text style={styles.summaryLabel}>{t('pos.amountPaid')}</Text>
                  <Text style={styles.summaryVal}>
                    {formatCurrency(selectedTx.paid_amount)}
                  </Text>
                </View>
                <View style={styles.summaryRow}>
                  <Text style={styles.summaryLabel}>{t('pos.change')}</Text>
                  <Text style={styles.summaryVal}>
                    {formatCurrency(selectedTx.change_amount)}
                  </Text>
                </View>
              </View>
            </ScrollView>

            {/* Modal Footer Actions */}
            {selectedTx.status === 'completed' && (
              <View style={styles.modalFooter}>
                <TouchableOpacity
                  style={styles.voidBtn}
                  onPress={() => handleVoidTransaction(selectedTx)}
                  disabled={voiding}
                >
                  <Ban size={20} color="#FFFFFF" style={{ marginRight: 8 }} />
                  <Text style={styles.voidBtnText}>
                    {t('transactions.voidTransaction')}
                  </Text>
                </TouchableOpacity>
              </View>
            )}
          </View>
        )}
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
  },
  searchInput: {
    flex: 1,
    fontSize: 14,
    color: '#2C2C2C',
  },
  listContainer: {
    padding: 12,
    gap: 10,
  },
  txCard: {
    backgroundColor: '#FFFFFF',
    borderRadius: 16,
    padding: 14,
    borderWidth: 1,
    borderColor: '#E5D9C5',
  },
  txHeader: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  txIconBox: {
    width: 40,
    height: 40,
    borderRadius: 12,
    backgroundColor: '#1E1E1E',
    alignItems: 'center',
    justifyContent: 'center',
  },
  txNo: {
    fontSize: 14,
    fontWeight: '700',
    color: '#2C2C2C',
  },
  txDate: {
    fontSize: 12,
    color: '#64748B',
    marginTop: 2,
  },
  badge: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 4,
    paddingHorizontal: 8,
    borderRadius: 12,
  },
  badgeSuccess: {
    backgroundColor: '#DCFCE7',
  },
  badgeDanger: {
    backgroundColor: '#FEE2E2',
  },
  badgeWarning: {
    backgroundColor: '#FEF3C7',
  },
  badgeText: {
    fontSize: 11,
    fontWeight: '700',
  },
  txDivider: {
    height: 1,
    backgroundColor: '#F9F6F0',
    marginVertical: 10,
  },
  txFooter: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
  },
  paymentMethodText: {
    fontSize: 12,
    fontWeight: '600',
    color: '#2C2C2C',
  },
  cashierName: {
    fontSize: 12,
    color: '#64748B',
  },
  txTotal: {
    fontSize: 15,
    fontWeight: '800',
    color: '#C9A96E',
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

  /* Detail Modal */
  modalContainer: {
    flex: 1,
    backgroundColor: '#FFFFFF',
  },
  modalHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    padding: 16,
    borderBottomWidth: 1,
    borderBottomColor: '#E2E8F0',
  },
  modalTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: '#0F172A',
  },
  closeBtn: {
    padding: 4,
  },
  modalBody: {
    flex: 1,
    padding: 16,
  },
  txInfoCard: {
    backgroundColor: '#F8FAFC',
    borderRadius: 12,
    padding: 14,
    borderWidth: 1,
    borderColor: '#E2E8F0',
    marginBottom: 16,
  },
  infoTxNo: {
    fontSize: 16,
    fontWeight: '700',
    color: '#0F172A',
  },
  infoDate: {
    fontSize: 13,
    color: '#64748B',
    marginTop: 2,
  },
  sectionTitle: {
    fontSize: 15,
    fontWeight: '700',
    color: '#0F172A',
    marginBottom: 8,
  },
  itemsBox: {
    backgroundColor: '#FFFFFF',
    borderWidth: 1,
    borderColor: '#E2E8F0',
    borderRadius: 12,
    padding: 12,
    marginBottom: 16,
    gap: 8,
  },
  itemRow: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 4,
  },
  itemName: {
    fontSize: 13,
    fontWeight: '600',
    color: '#0F172A',
  },
  itemUnitPrice: {
    fontSize: 12,
    color: '#64748B',
  },
  itemQty: {
    fontSize: 13,
    color: '#64748B',
    marginHorizontal: 8,
  },
  itemSubtotal: {
    fontSize: 13,
    fontWeight: '700',
    color: '#0F172A',
  },
  summaryCard: {
    backgroundColor: '#F8FAFC',
    borderRadius: 12,
    padding: 14,
    borderWidth: 1,
    borderColor: '#E2E8F0',
    gap: 8,
  },
  summaryRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
  },
  summaryLabel: {
    fontSize: 13,
    color: '#64748B',
  },
  summaryVal: {
    fontSize: 13,
    fontWeight: '600',
    color: '#334155',
  },
  totalRow: {
    borderTopWidth: 1,
    borderTopColor: '#E2E8F0',
    paddingTop: 8,
  },
  totalLabel: {
    fontSize: 15,
    fontWeight: '700',
    color: '#0F172A',
  },
  totalVal: {
    fontSize: 16,
    fontWeight: '800',
    color: '#2563EB',
  },
  modalFooter: {
    padding: 16,
    borderTopWidth: 1,
    borderTopColor: '#E2E8F0',
  },
  voidBtn: {
    height: 48,
    backgroundColor: '#EF4444',
    borderRadius: 12,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
  },
  voidBtnText: {
    color: '#FFFFFF',
    fontSize: 16,
    fontWeight: '700',
  },
});
