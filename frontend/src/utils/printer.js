// Thermal Printer Utility for 80mm Browser Thermal Receipt Printing

export function printReceiptBrowser(transaction, labels = {}, locale = 'id', activeOutlet = null) {
  const printWindow = window.open('', '_blank')
  if (!printWindow) return

  const loc = locale === 'en' ? 'en-US' : 'id-ID'
  const formatCurr = (amt) => new Intl.NumberFormat(loc, { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amt || 0)
  const formatDateStr = (dt) => new Date(dt).toLocaleString(loc, { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })

  // Determine Location/Outlet Name: Prioritize transaction.location.name (FNB location name)
  const storeName = transaction.location?.name || transaction.outlet?.name || activeOutlet?.location?.name || activeOutlet?.name || labels.store || 'Resto & Cafe Utama'
  const storeAddress = transaction.location?.address || transaction.outlet?.address || activeOutlet?.address || ''
  const storePhone = transaction.location?.phone || transaction.outlet?.phone || activeOutlet?.phone || ''

  const labelTxNo = labels.transactionNo || 'No. TRX'
  const labelDate = labels.date || 'Tanggal'
  const labelCashier = labels.cashier || 'Kasir'
  const labelTable = labels.table || 'Meja'
  const labelSubtotal = labels.subtotal || 'Subtotal'
  const labelDiscount = labels.discount || 'Diskon'
  const labelTax = labels.tax || 'Pajak'
  const labelTotal = labels.totalAmount || 'TOTAL'
  const labelPaid = labels.paid || 'Dibayar'
  const labelChange = labels.change || 'Kembali'
  const labelPayment = labels.paymentMethod || 'Metode Pembayaran'
  const labelThankYou = labels.thankYou || 'Terima kasih atas kunjungan Anda!'
  const labelPolicy = labels.nonRefundable || 'Barang yang sudah dibeli tidak dapat dikembalikan'

  let tableNo = null
  if (transaction.table?.table_number) {
    tableNo = transaction.table.table_number
  } else if (transaction.notes) {
    const match = transaction.notes.match(/Meja:\s*([^|]+)/i)
    if (match && match[1]) tableNo = match[1].trim()
  }

  const html = `
    <!DOCTYPE html>
    <html>
    <head>
      <title>Receipt - ${transaction.transaction_no}</title>
      <style>
        @media print {
          @page { margin: 0; size: 80mm auto; }
          body { margin: 0; padding: 5mm; }
        }
        body {
          font-family: 'Courier New', monospace;
          font-size: 11px;
          line-height: 1.4;
          width: 80mm;
          margin: 0 auto;
          color: #000;
        }
        .center { text-align: center; }
        .bold { font-weight: bold; }
        .large { font-size: 16px; }
        .line { border-top: 1px dashed #000; margin: 6px 0; }
        table { width: 100%; border-collapse: collapse; margin: 4px 0; }
        td, th { padding: 2px 0; font-size: 11px; }
        .right { text-align: right; }
        .uppercase { text-transform: uppercase; }
        .footer { font-size: 10px; margin-top: 8px; }
      </style>
    </head>
    <body>
      <div class="center large bold">${storeName}</div>
      ${storeAddress ? `<div class="center">${storeAddress}</div>` : ''}
      ${storePhone ? `<div class="center">${storePhone}</div>` : ''}
      <div class="line"></div>
      <div>${labelTxNo}: ${transaction.transaction_no}</div>
      <div>${labelDate}: ${formatDateStr(transaction.created_at)}</div>
      <div>${labelCashier}: ${transaction.user?.name || transaction.customer_name || '-'}</div>
      ${tableNo ? `<div>${labelTable}: ${tableNo}</div>` : ''}
      <div class="line"></div>
      ${(transaction.items || []).map(item => `
        <div>${item.product_name}</div>
        <table>
          <tr>
            <td>${item.quantity} x ${formatCurr(item.price)}</td>
            <td class="right">${formatCurr(item.subtotal)}</td>
          </tr>
        </table>
      `).join('')}
      <div class="line"></div>
      <table>
        <tr>
          <td>${labelSubtotal}:</td>
          <td class="right">${formatCurr(transaction.subtotal)}</td>
        </tr>
        ${transaction.discount > 0 ? `
        <tr>
          <td>${labelDiscount}:</td>
          <td class="right">-${formatCurr(transaction.discount)}</td>
        </tr>
        ` : ''}
        ${transaction.tax > 0 ? `
        <tr>
          <td>${labelTax}:</td>
          <td class="right">${formatCurr(transaction.tax)}</td>
        </tr>
        ` : ''}
        <tr class="bold">
          <td>${labelTotal}:</td>
          <td class="right">${formatCurr(transaction.total)}</td>
        </tr>
        ${transaction.paid_amount ? `
        <tr>
          <td>${labelPaid}:</td>
          <td class="right">${formatCurr(transaction.paid_amount)}</td>
        </tr>
        ` : ''}
        ${transaction.change_amount !== undefined && transaction.change_amount !== null ? `
        <tr>
          <td>${labelChange}:</td>
          <td class="right">${formatCurr(transaction.change_amount)}</td>
        </tr>
        ` : ''}
      </table>
      ${transaction.payment_method ? `
      <div class="line"></div>
      <div>${labelPayment}: <span class="uppercase">${transaction.payment_method}</span></div>
      ` : ''}
      <div class="line"></div>
      <div class="center footer">${labelThankYou}</div>
      <div class="center footer">${labelPolicy}</div>
      <script>
        window.onload = function() {
          window.print()
          setTimeout(() => window.close(), 200)
        }
      </script>
    </body>
    </html>
  `
  
  printWindow.document.write(html)
  printWindow.document.close()
}
