import React, { useState, useEffect, useContext } from 'react';
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
  Dimensions,
} from 'react-native';
import { useTranslation } from 'react-i18next';
import { AuthContext } from '../context/AuthContext';
import api from '../services/api';
import {
  Search,
  ShoppingCart,
  Plus,
  Minus,
  Trash2,
  CheckCircle,
  CreditCard,
  Banknote,
  QrCode,
  X,
  Printer,
  ShoppingBag,
} from 'lucide-react-native';

const { width } = Dimensions.get('window');
const numColumns = width > 600 ? 3 : 2;

export default function PosScreen() {
  const { t } = useTranslation();
  const { user } = useContext(AuthContext);

  const [products, setProducts] = useState([]);
  const [categories, setCategories] = useState([]);
  const [selectedCategory, setSelectedCategory] = useState(null);
  const [searchQuery, setSearchQuery] = useState('');
  const [loading, setLoading] = useState(true);

  // Cart State
  // POS Pagination State
  const [currentPage, setCurrentPage] = useState(1);
  const perPage = 10;

  const [cart, setCart] = useState([]);
  const [showCartModal, setShowCartModal] = useState(false);
  const [discount, setDiscount] = useState(0);
  const [paymentMethod, setPaymentMethod] = useState('cash');
  const [paidAmount, setPaidAmount] = useState('');
  const [processingOrder, setProcessingOrder] = useState(false);

  // Completed Receipt State
  const [completedTransaction, setCompletedTransaction] = useState(null);
  const [showReceiptModal, setShowReceiptModal] = useState(false);

  useEffect(() => {
    fetchInitialData();
  }, []);

  const fetchInitialData = async () => {
    try {
      setLoading(true);
      const [prodRes, catRes] = await Promise.all([
        api.get('/products?per_page=100'),
        api.get('/categories'),
      ]);

      const prodData = prodRes.data.data || prodRes.data || [];
      const catData = catRes.data.data || catRes.data || [];

      const rawCat = Array.isArray(catData) ? catData : [];
      const fnbCatList = rawCat.filter((cat) => {
        const name = (cat.name || '').toLowerCase();
        return name.includes('fnb');
      });

      setProducts(prodData);
      setCategories(fnbCatList);
    } catch (error) {
      console.error('Error fetching POS data:', error);
      Alert.alert(t('common.error'), t('pos.loadDataFailed'));
    } finally {
      setLoading(false);
    }
  };

  // Filtered Products
  const filteredProducts = products.filter((p) => {
    const matchesSearch =
      p.name?.toLowerCase().includes(searchQuery.toLowerCase()) ||
      p.sku?.toLowerCase().includes(searchQuery.toLowerCase()) ||
      p.barcode?.toLowerCase().includes(searchQuery.toLowerCase());

    const matchesCategory = selectedCategory ? p.category_id === selectedCategory : true;

    return matchesSearch && matchesCategory;
  });

  // POS Product Pagination Calculation
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

  // Cart Functions
  const addToCart = (product) => {
    setCart((prevCart) => {
      const existing = prevCart.find((item) => item.product_id === product.id);
      if (existing) {
        return prevCart.map((item) =>
          item.product_id === product.id
            ? { ...item, quantity: item.quantity + 1 }
            : item
        );
      }
      return [
        ...prevCart,
        {
          product_id: product.id,
          name: product.name,
          price: parseFloat(product.selling_price || 0),
          quantity: 1,
          discount: 0,
        },
      ];
    });
  };

  const updateQuantity = (productId, delta) => {
    setCart((prevCart) =>
      prevCart
        .map((item) => {
          if (item.product_id === productId) {
            const newQty = item.quantity + delta;
            return newQty > 0 ? { ...item, quantity: newQty } : null;
          }
          return item;
        })
        .filter(Boolean)
    );
  };

  const removeFromCart = (productId) => {
    setCart((prevCart) => prevCart.filter((item) => item.product_id !== productId));
  };

  const clearCart = () => {
    setCart([]);
    setDiscount(0);
    setPaidAmount('');
  };

  // Calculations
  const cartSubtotal = cart.reduce((acc, item) => acc + item.price * item.quantity, 0);
  const discountVal = parseFloat(discount) || 0;
  const cartTotal = Math.max(0, cartSubtotal - discountVal);
  const paidVal = parseFloat(paidAmount) || 0;
  const changeVal = paidVal >= cartTotal ? paidVal - cartTotal : 0;

  const totalItemCount = cart.reduce((acc, item) => acc + item.quantity, 0);

  // Quick Cash preset buttons
  const handleQuickCash = (amount) => {
    setPaidAmount(amount.toString());
  };

  // Process Transaction
  const handleProcessTransaction = async () => {
    if (cart.length === 0) return;

    if (paymentMethod === 'cash' && paidVal < cartTotal) {
      Alert.alert(t('common.warning'), t('pos.insufficientPayment'));
      return;
    }

    try {
      setProcessingOrder(true);
      const payload = {
        outlet_id: user?.outlet_id || user?.outlet?.id || 1,
        items: cart.map((item) => ({
          product_id: item.product_id,
          quantity: item.quantity,
          price: item.price,
          discount: item.discount || 0,
        })),
        discount: discountVal,
        tax: 0,
        payment_method: paymentMethod,
        paid_amount: paymentMethod === 'cash' ? paidVal : cartTotal,
      };

      const res = await api.post('/transactions', payload);
      const transactionData = res.data.data || res.data;

      setCompletedTransaction(transactionData);
      setShowCartModal(false);
      setShowReceiptModal(true);
      clearCart();
    } catch (error) {
      console.error('Checkout error:', error);
      Alert.alert(t('common.error'), error.response?.data?.message || 'Checkout failed');
    } finally {
      setProcessingOrder(false);
    }
  };

  const formatCurrency = (val) => {
    return `Rp ${(val || 0).toLocaleString('id-ID')}`;
  };

  const renderProductItem = ({ item }) => (
    <TouchableOpacity
      style={styles.productCard}
      onPress={() => addToCart(item)}
      activeOpacity={0.7}
    >
      <View style={styles.productIconBox}>
        <ShoppingBag size={28} color="#2563EB" />
      </View>
      <Text style={styles.productName} numberOfLines={2}>
        {item.name}
      </Text>
      <Text style={styles.productPrice}>{formatCurrency(parseFloat(item.selling_price))}</Text>
      <View style={styles.productFooter}>
        <Text style={[styles.stockBadge, item.stock <= 0 && styles.outStockBadge]}>
          {item.stock > 0 ? `${t('pos.stock')}: ${item.stock}` : t('pos.outOfStock')}
        </Text>
        <TouchableOpacity
          style={styles.addBtn}
          onPress={() => addToCart(item)}
        >
          <Plus size={16} color="#FFFFFF" />
        </TouchableOpacity>
      </View>
    </TouchableOpacity>
  );

  return (
    <View style={styles.container}>
      {/* Search & Category Filter Header */}
      <View style={styles.headerContainer}>
        <View style={styles.searchBar}>
          <Search size={18} color="#64748B" style={{ marginRight: 8 }} />
          <TextInput
            style={styles.searchInput}
            placeholder={t('pos.scanOrSearch')}
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

        {/* Categories Bar */}
        <ScrollView
          horizontal
          showsHorizontalScrollIndicator={false}
          contentContainerStyle={styles.categoriesScroll}
        >
          <TouchableOpacity
            style={[
              styles.catChip,
              selectedCategory === null && styles.catChipActive,
            ]}
            onPress={() => setSelectedCategory(null)}
          >
            <Text
              style={[
                styles.catText,
                selectedCategory === null && styles.catTextActive,
              ]}
            >
              {t('pos.allCategories')}
            </Text>
          </TouchableOpacity>

          {categories.map((cat) => (
            <TouchableOpacity
              key={cat.id}
              style={[
                styles.catChip,
                selectedCategory === cat.id && styles.catChipActive,
              ]}
              onPress={() => setSelectedCategory(cat.id)}
            >
              <Text
                style={[
                  styles.catText,
                  selectedCategory === cat.id && styles.catTextActive,
                ]}
              >
                {cat.name}
              </Text>
            </TouchableOpacity>
          ))}
        </ScrollView>
      </View>

      {/* Products Grid */}
      {loading ? (
        <View style={styles.centerContainer}>
          <ActivityIndicator size="large" color="#2563EB" />
          <Text style={styles.loadingText}>{t('common.loading')}</Text>
        </View>
      ) : filteredProducts.length === 0 ? (
        <View style={styles.centerContainer}>
          <ShoppingBag size={48} color="#CBD5E1" />
          <Text style={styles.noDataText}>{t('pos.noProducts')}</Text>
        </View>
      ) : (
        <View style={{ flex: 1 }}>
          <FlatList
            data={paginatedProducts}
            keyExtractor={(item) => item.id.toString()}
            renderItem={renderProductItem}
            numColumns={numColumns}
            key={numColumns}
            contentContainerStyle={styles.gridContainer}
          />

          {/* POS Product Pagination Controls */}
          {totalPages > 1 && (
            <View style={styles.posPaginationRow}>
              <TouchableOpacity
                style={[styles.posPageNavBtn, currentPage === 1 && styles.posPageNavBtnDisabled]}
                disabled={currentPage === 1}
                onPress={() => setCurrentPage((prev) => Math.max(1, prev - 1))}
              >
                <Text style={[styles.posPageNavText, currentPage === 1 && styles.posPageNavTextDisabled]}>‹ Prev</Text>
              </TouchableOpacity>

              <View style={styles.posPageNumbersBox}>
                {getVisiblePages(totalPages, currentPage).map((pg, idx) => (
                  <TouchableOpacity
                    key={idx}
                    style={[
                      styles.posPageNumBtn,
                      pg === currentPage && styles.posPageNumBtnActive,
                      pg === '...' && styles.posPageNumEllipsis,
                    ]}
                    disabled={pg === '...'}
                    onPress={() => typeof pg === 'number' && setCurrentPage(pg)}
                  >
                    <Text
                      style={[
                        styles.posPageNumText,
                        pg === currentPage && styles.posPageNumTextActive,
                        pg === '...' && styles.posPageNumTextEllipsis,
                      ]}
                    >
                      {pg}
                    </Text>
                  </TouchableOpacity>
                ))}
              </View>

              <TouchableOpacity
                style={[styles.posPageNavBtn, currentPage === totalPages && styles.posPageNavBtnDisabled]}
                disabled={currentPage === totalPages}
                onPress={() => setCurrentPage((prev) => Math.min(totalPages, prev + 1))}
              >
                <Text style={[styles.posPageNavText, currentPage === totalPages && styles.posPageNavTextDisabled]}>Next ›</Text>
              </TouchableOpacity>
            </View>
          )}
        </View>
      )}

      {/* Bottom Cart Floating Bar */}
      {cart.length > 0 && (
        <View style={styles.floatingBar}>
          <View style={styles.cartSummaryText}>
            <View style={styles.cartBadge}>
              <Text style={styles.cartBadgeText}>{totalItemCount}</Text>
            </View>
            <View>
              <Text style={styles.floatingTotalLabel}>{t('pos.total')}</Text>
              <Text style={styles.floatingTotalVal}>{formatCurrency(cartTotal)}</Text>
            </View>
          </View>
          <TouchableOpacity
            style={styles.checkoutBtn}
            onPress={() => setShowCartModal(true)}
          >
            <ShoppingCart size={20} color="#FFFFFF" style={{ marginRight: 6 }} />
            <Text style={styles.checkoutBtnText}>{t('pos.checkout')}</Text>
          </TouchableOpacity>
        </View>
      )}

      {/* CART & CHECKOUT MODAL */}
      <Modal
        visible={showCartModal}
        animationType="slide"
        transparent={false}
        onRequestClose={() => setShowCartModal(false)}
      >
        <View style={styles.modalContainer}>
          {/* Modal Header */}
          <View style={styles.modalHeader}>
            <Text style={styles.modalTitle}>{t('pos.cart')} ({totalItemCount})</Text>
            <TouchableOpacity
              onPress={() => setShowCartModal(false)}
              style={styles.closeBtn}
            >
              <X size={24} color="#64748B" />
            </TouchableOpacity>
          </View>

          <ScrollView style={styles.modalBody}>
            {/* Cart Items List */}
            {cart.map((item) => (
              <View key={item.product_id} style={styles.cartItemRow}>
                <View style={{ flex: 1 }}>
                  <Text style={styles.cartItemName}>{item.name}</Text>
                  <Text style={styles.cartItemPrice}>
                    {formatCurrency(item.price)}
                  </Text>
                </View>

                {/* Qty Counter */}
                <View style={styles.qtyContainer}>
                  <TouchableOpacity
                    style={styles.qtyBtn}
                    onPress={() => updateQuantity(item.product_id, -1)}
                  >
                    <Minus size={16} color="#475569" />
                  </TouchableOpacity>
                  <Text style={styles.qtyText}>{item.quantity}</Text>
                  <TouchableOpacity
                    style={styles.qtyBtn}
                    onPress={() => updateQuantity(item.product_id, 1)}
                  >
                    <Plus size={16} color="#475569" />
                  </TouchableOpacity>
                </View>

                <Text style={styles.cartItemSubtotal}>
                  {formatCurrency(item.price * item.quantity)}
                </Text>

                <TouchableOpacity
                  onPress={() => removeFromCart(item.product_id)}
                  style={{ marginLeft: 8 }}
                >
                  <Trash2 size={18} color="#EF4444" />
                </TouchableOpacity>
              </View>
            ))}

            {/* Payment Method Selector */}
            <Text style={styles.sectionHeader}>{t('pos.paymentMethod')}</Text>
            <View style={styles.paymentMethodRow}>
              <TouchableOpacity
                style={[
                  styles.paymentOption,
                  paymentMethod === 'cash' && styles.paymentOptionActive,
                ]}
                onPress={() => setPaymentMethod('cash')}
              >
                <Banknote size={20} color={paymentMethod === 'cash' ? '#2563EB' : '#64748B'} />
                <Text style={[styles.paymentText, paymentMethod === 'cash' && styles.paymentTextActive]}>
                  {t('pos.cash')}
                </Text>
              </TouchableOpacity>

              <TouchableOpacity
                style={[
                  styles.paymentOption,
                  paymentMethod === 'qris' && styles.paymentOptionActive,
                ]}
                onPress={() => setPaymentMethod('qris')}
              >
                <QrCode size={20} color={paymentMethod === 'qris' ? '#2563EB' : '#64748B'} />
                <Text style={[styles.paymentText, paymentMethod === 'qris' && styles.paymentTextActive]}>
                  {t('pos.qris')}
                </Text>
              </TouchableOpacity>

              <TouchableOpacity
                style={[
                  styles.paymentOption,
                  paymentMethod === 'transfer' && styles.paymentOptionActive,
                ]}
                onPress={() => setPaymentMethod('transfer')}
              >
                <CreditCard size={20} color={paymentMethod === 'transfer' ? '#2563EB' : '#64748B'} />
                <Text style={[styles.paymentText, paymentMethod === 'transfer' && styles.paymentTextActive]}>
                  {t('pos.transfer')}
                </Text>
              </TouchableOpacity>
            </View>

            {/* Cash Paid Amount Section */}
            {paymentMethod === 'cash' && (
              <View style={styles.cashSection}>
                <Text style={styles.label}>{t('pos.amountPaid')}</Text>
                <TextInput
                  style={styles.paidInput}
                  keyboardType="numeric"
                  value={paidAmount}
                  onChangeText={setPaidAmount}
                  placeholder="0"
                />

                {/* Quick Cash Buttons */}
                <View style={styles.quickCashRow}>
                  <TouchableOpacity
                    style={styles.quickChip}
                    onPress={() => handleQuickCash(cartTotal)}
                  >
                    <Text style={styles.quickChipText}>{t('pos.exact')}</Text>
                  </TouchableOpacity>
                  {[20000, 50000, 100000].map((amt) => (
                    <TouchableOpacity
                      key={amt}
                      style={styles.quickChip}
                      onPress={() => handleQuickCash(amt)}
                    >
                      <Text style={styles.quickChipText}>
                        {amt >= 1000 ? `${amt / 1000}k` : amt}
                      </Text>
                    </TouchableOpacity>
                  ))}
                </View>
              </View>
            )}

            {/* Calculation Summary Card */}
            <View style={styles.summaryCard}>
              <View style={styles.summaryRow}>
                <Text style={styles.summaryLabel}>{t('pos.subtotal')}</Text>
                <Text style={styles.summaryVal}>{formatCurrency(cartSubtotal)}</Text>
              </View>
              <View style={styles.summaryRow}>
                <Text style={styles.summaryLabel}>{t('pos.discount')}</Text>
                <Text style={styles.summaryVal}>-{formatCurrency(discountVal)}</Text>
              </View>
              <View style={[styles.summaryRow, styles.totalRow]}>
                <Text style={styles.totalLabel}>{t('pos.total')}</Text>
                <Text style={styles.totalVal}>{formatCurrency(cartTotal)}</Text>
              </View>
              {paymentMethod === 'cash' && (
                <View style={styles.summaryRow}>
                  <Text style={styles.changeLabel}>{t('pos.change')}</Text>
                  <Text style={styles.changeVal}>{formatCurrency(changeVal)}</Text>
                </View>
              )}
            </View>
          </ScrollView>

          {/* Complete Transaction Footer */}
          <View style={styles.modalFooter}>
            <TouchableOpacity
              style={styles.processBtn}
              onPress={handleProcessTransaction}
              disabled={processingOrder}
            >
              {processingOrder ? (
                <ActivityIndicator color="#FFFFFF" />
              ) : (
                <>
                  <CheckCircle size={20} color="#FFFFFF" style={{ marginRight: 8 }} />
                  <Text style={styles.processBtnText}>{t('pos.processPayment')}</Text>
                </>
              )}
            </TouchableOpacity>
          </View>
        </View>
      </Modal>

      {/* RECEIPT MODAL */}
      <Modal
        visible={showReceiptModal}
        animationType="fade"
        transparent={true}
        onRequestClose={() => setShowReceiptModal(false)}
      >
        <View style={styles.receiptOverlay}>
          <View style={styles.receiptCard}>
            <View style={styles.receiptHeader}>
              <CheckCircle size={48} color="#10B981" />
              <Text style={styles.receiptSuccessTitle}>
                {t('pos.transactionSuccess')}
              </Text>
              <Text style={styles.receiptNo}>
                {completedTransaction?.transaction_no || '#TRX-SUCCESS'}
              </Text>
            </View>

            <View style={styles.receiptDivider} />

            <ScrollView style={{ maxHeight: 220 }}>
              {completedTransaction?.items?.map((it, idx) => (
                <View key={idx} style={styles.receiptItemRow}>
                  <Text style={styles.receiptItemName}>{it.product?.name || it.name}</Text>
                  <Text style={styles.receiptItemQty}>x{it.quantity}</Text>
                  <Text style={styles.receiptItemTotal}>
                    {formatCurrency((it.price || 0) * (it.quantity || 1))}
                  </Text>
                </View>
              ))}
            </ScrollView>

            <View style={styles.receiptDivider} />

            <View style={styles.receiptSummaryRow}>
              <Text style={styles.receiptLabel}>{t('pos.total')}:</Text>
              <Text style={styles.receiptVal}>
                {formatCurrency(completedTransaction?.total || 0)}
              </Text>
            </View>

            <View style={styles.receiptSummaryRow}>
              <Text style={styles.receiptLabel}>{t('pos.amountPaid')}:</Text>
              <Text style={styles.receiptVal}>
                {formatCurrency(completedTransaction?.paid_amount || 0)}
              </Text>
            </View>

            <View style={styles.receiptSummaryRow}>
              <Text style={styles.receiptLabel}>{t('pos.change')}:</Text>
              <Text style={styles.receiptVal}>
                {formatCurrency(completedTransaction?.change_amount || 0)}
              </Text>
            </View>

            <View style={styles.receiptActions}>
              <TouchableOpacity
                style={styles.newOrderBtn}
                onPress={() => setShowReceiptModal(false)}
              >
                <Text style={styles.newOrderText}>{t('pos.newOrder')}</Text>
              </TouchableOpacity>
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
  headerContainer: {
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
  categoriesScroll: {
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
  gridContainer: {
    padding: 12,
    gap: 12,
  },
  productCard: {
    flex: 1,
    margin: 6,
    backgroundColor: '#FFFFFF',
    borderRadius: 16,
    padding: 12,
    borderWidth: 1,
    borderColor: '#E5D9C5',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.04,
    shadowRadius: 6,
    elevation: 2,
  },
  productIconBox: {
    width: 44,
    height: 44,
    borderRadius: 12,
    backgroundColor: '#1E1E1E',
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 8,
  },
  productName: {
    fontSize: 14,
    fontWeight: '600',
    color: '#2C2C2C',
    marginBottom: 4,
    height: 38,
  },
  productPrice: {
    fontSize: 14,
    fontWeight: '700',
    color: '#C9A96E',
    marginBottom: 8,
  },
  productFooter: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
  },
  stockBadge: {
    fontSize: 11,
    color: '#16A34A',
    fontWeight: '500',
  },
  outStockBadge: {
    color: '#DC2626',
  },
  addBtn: {
    width: 32,
    height: 32,
    borderRadius: 16,
    backgroundColor: '#6B2E3E',
    alignItems: 'center',
    justifyContent: 'center',
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
  noDataText: {
    marginTop: 10,
    color: '#94A3B8',
    fontSize: 15,
  },
  floatingBar: {
    position: 'absolute',
    bottom: 12,
    left: 12,
    right: 12,
    backgroundColor: '#1E1E1E',
    borderWidth: 1,
    borderColor: '#C9A96E',
    borderRadius: 16,
    padding: 12,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.2,
    shadowRadius: 8,
    elevation: 6,
  },
  cartSummaryText: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
  },
  cartBadge: {
    backgroundColor: '#C9A96E',
    borderRadius: 12,
    width: 32,
    height: 32,
    alignItems: 'center',
    justifyContent: 'center',
  },
  cartBadgeText: {
    color: '#1E1E1E',
    fontWeight: '700',
    fontSize: 14,
  },
  floatingTotalLabel: {
    color: '#E5D9C5',
    fontSize: 11,
  },
  floatingTotalVal: {
    color: '#F9F6F0',
    fontSize: 16,
    fontWeight: '700',
  },
  checkoutBtn: {
    backgroundColor: '#6B2E3E',
    paddingVertical: 10,
    paddingHorizontal: 16,
    borderRadius: 12,
    flexDirection: 'row',
    alignItems: 'center',
  },
  checkoutBtnText: {
    color: '#F9F6F0',
    fontWeight: '700',
    fontSize: 14,
  },

  /* Modal Styles */
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
  /* POS Pagination Controls */
  posPaginationRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: 12,
    paddingVertical: 10,
    backgroundColor: '#FFFFFF',
    borderTopWidth: 1,
    borderTopColor: '#E2E8F0',
  },
  posPageNavBtn: {
    paddingHorizontal: 10,
    paddingVertical: 6,
    borderRadius: 8,
    backgroundColor: '#2563EB',
  },
  posPageNavBtnDisabled: {
    backgroundColor: '#E2E8F0',
  },
  posPageNavText: {
    fontSize: 12,
    fontWeight: '700',
    color: '#FFFFFF',
  },
  posPageNavTextDisabled: {
    color: '#94A3B8',
  },
  posPageNumbersBox: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
  },
  posPageNumBtn: {
    minWidth: 28,
    height: 28,
    paddingHorizontal: 6,
    borderRadius: 6,
    borderWidth: 1,
    borderColor: '#CBD5E1',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#FFFFFF',
  },
  posPageNumBtnActive: {
    backgroundColor: '#2563EB',
    borderColor: '#2563EB',
  },
  posPageNumEllipsis: {
    borderWidth: 0,
    backgroundColor: 'transparent',
  },
  posPageNumText: {
    fontSize: 12,
    fontWeight: '600',
    color: '#334155',
  },
  posPageNumTextActive: {
    color: '#FFFFFF',
  },
  posPageNumTextEllipsis: {
    color: '#94A3B8',
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
  cartItemRow: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 12,
    borderBottomWidth: 1,
    borderBottomColor: '#F1F5F9',
  },
  cartItemName: {
    fontSize: 14,
    fontWeight: '600',
    color: '#0F172A',
  },
  cartItemPrice: {
    fontSize: 13,
    color: '#64748B',
  },
  qtyContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#F1F5F9',
    borderRadius: 8,
    paddingHorizontal: 4,
  },
  qtyBtn: {
    padding: 6,
  },
  qtyText: {
    paddingHorizontal: 8,
    fontWeight: '600',
    fontSize: 14,
  },
  cartItemSubtotal: {
    fontSize: 14,
    fontWeight: '700',
    color: '#0F172A',
    marginLeft: 12,
    minWidth: 80,
    textAlign: 'right',
  },
  sectionHeader: {
    fontSize: 14,
    fontWeight: '700',
    color: '#334155',
    marginTop: 20,
    marginBottom: 10,
  },
  paymentMethodRow: {
    flexDirection: 'row',
    gap: 10,
  },
  paymentOption: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 6,
    paddingVertical: 12,
    borderWidth: 1,
    borderColor: '#CBD5E1',
    borderRadius: 12,
  },
  paymentOptionActive: {
    borderColor: '#2563EB',
    backgroundColor: '#EFF6FF',
  },
  paymentText: {
    fontSize: 13,
    fontWeight: '600',
    color: '#475569',
  },
  paymentTextActive: {
    color: '#2563EB',
  },
  cashSection: {
    marginTop: 16,
  },
  label: {
    fontSize: 13,
    fontWeight: '600',
    color: '#475569',
    marginBottom: 6,
  },
  paidInput: {
    backgroundColor: '#F8FAFC',
    borderWidth: 1,
    borderColor: '#CBD5E1',
    borderRadius: 10,
    paddingHorizontal: 12,
    height: 44,
    fontSize: 16,
    fontWeight: '700',
    color: '#0F172A',
  },
  quickCashRow: {
    flexDirection: 'row',
    gap: 8,
    marginTop: 10,
  },
  quickChip: {
    flex: 1,
    backgroundColor: '#F1F5F9',
    paddingVertical: 8,
    borderRadius: 8,
    alignItems: 'center',
  },
  quickChipText: {
    fontSize: 12,
    fontWeight: '600',
    color: '#334155',
  },
  summaryCard: {
    marginTop: 24,
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
    marginTop: 4,
  },
  totalLabel: {
    fontSize: 15,
    fontWeight: '700',
    color: '#0F172A',
  },
  totalVal: {
    fontSize: 16,
    fontWeight: '800',
    color: '#6B2E3E',
  },
  changeLabel: {
    fontSize: 14,
    fontWeight: '600',
    color: '#16A34A',
  },
  changeVal: {
    fontSize: 14,
    fontWeight: '700',
    color: '#16A34A',
  },
  modalFooter: {
    padding: 16,
    borderTopWidth: 1,
    borderTopColor: '#E2E8F0',
  },
  processBtn: {
    height: 48,
    backgroundColor: '#6B2E3E',
    borderRadius: 12,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
  },
  processBtnText: {
    color: '#F9F6F0',
    fontSize: 16,
    fontWeight: '700',
    letterSpacing: 0.5,
  },

  /* Receipt Modal */
  receiptOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.6)',
    alignItems: 'center',
    justifyContent: 'center',
    padding: 20,
  },
  receiptCard: {
    width: '100%',
    maxWidth: 380,
    backgroundColor: '#FFFFFF',
    borderRadius: 20,
    padding: 20,
  },
  receiptHeader: {
    alignItems: 'center',
  },
  receiptSuccessTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: '#0F172A',
    marginTop: 10,
  },
  receiptNo: {
    fontSize: 13,
    color: '#64748B',
    marginTop: 4,
  },
  receiptDivider: {
    height: 1,
    backgroundColor: '#E2E8F0',
    marginVertical: 12,
  },
  receiptItemRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: 6,
  },
  receiptItemName: {
    flex: 2,
    fontSize: 13,
    color: '#334155',
  },
  receiptItemQty: {
    flex: 1,
    fontSize: 13,
    color: '#64748B',
    textAlign: 'center',
  },
  receiptItemTotal: {
    flex: 1,
    fontSize: 13,
    fontWeight: '600',
    color: '#0F172A',
    textAlign: 'right',
  },
  receiptSummaryRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: 4,
  },
  receiptLabel: {
    fontSize: 13,
    color: '#64748B',
  },
  receiptVal: {
    fontSize: 13,
    fontWeight: '700',
    color: '#0F172A',
  },
  receiptActions: {
    marginTop: 16,
  },
  newOrderBtn: {
    height: 44,
    backgroundColor: '#6B2E3E',
    borderRadius: 12,
    alignItems: 'center',
    justifyContent: 'center',
  },
  newOrderText: {
    color: '#F9F6F0',
    fontWeight: '700',
    fontSize: 15,
  },
});
