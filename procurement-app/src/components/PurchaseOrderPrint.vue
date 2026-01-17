<template>
  <div v-if="show" class="pdf-modal-overlay">
    <div class="pdf-modal">
      <div class="pdf-modal-header">
        <h2>Generate Purchase Order PDF</h2>
        <button @click="close" class="btn-close">×</button>
      </div>
      <div class="pdf-modal-body">
        <p>Click the button below to download the Purchase Order PDF.</p>
        <div class="po-info">
          <p><strong>PO Number:</strong> {{ po.po_number }}</p>
          <p><strong>Vendor:</strong> {{ po.vendor?.name || 'N/A' }}</p>
          <p><strong>Total:</strong> Rp {{ formatNumber(calculateTotal()) }}</p>
        </div>
        <div class="pdf-actions">
          <button @click="generatePDF" class="btn-download">
            📥 Download PDF
          </button>
          <button @click="close" class="btn-cancel">Cancel</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { watch, computed } from 'vue'
import jsPDF from 'jspdf'
import 'jspdf-autotable'
import { useCompanyStore } from '@/stores/company'

const companyStore = useCompanyStore()
const companyConfig = computed(() => companyStore.company)

const props = defineProps({
  po: {
    type: Object,
    required: true
  },
  show: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['close'])

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString('id-ID', {
    day: '2-digit',
    month: 'long',
    year: 'numeric'
  })
}

const formatNumber = (number) => {
  return Number(number || 0).toLocaleString('id-ID')
}

const calculateSubtotal = () => {
  if (!props.po.items || !Array.isArray(props.po.items)) return 0
  return props.po.items.reduce((sum, item) => {
    return sum + ((item.quantity || 0) * (item.unit_price || 0))
  }, 0)
}

const calculateTotal = () => {
  let total = calculateSubtotal()
  if (props.po.tax_amount) total += Number(props.po.tax_amount)
  if (props.po.discount_amount) total -= Number(props.po.discount_amount)
  return total
}

const generatePDF = () => {
  const doc = new jsPDF('p', 'mm', 'a4')
  const pageWidth = doc.internal.pageSize.getWidth()
  let yPos = 20
  let rightYPos = 20 // Separate Y position for right column

  // Header - PURCHASE ORDER (left side)
  doc.setFontSize(24)
  doc.setTextColor(37, 99, 235)
  doc.setFont(undefined, 'bold')
  doc.text('PURCHASE ORDER', 15, yPos)
  
  yPos += 8 // Move down for PO number
  doc.setFontSize(12)
  doc.setTextColor(0, 0, 0)
  doc.text(props.po.po_number || '', 15, yPos)
  
  // Company Name (right side, starts at same Y as PURCHASE ORDER title)
  doc.setFontSize(11)
  doc.setFont(undefined, 'bold')
  doc.setTextColor(0, 0, 0)
  doc.text(companyConfig.value.name, pageWidth - 15, rightYPos, { align: 'right' })
  
  rightYPos += 5 // Small gap after company name
  
  // Company details (right side, smaller font)
  doc.setFontSize(8)
  doc.setFont(undefined, 'normal')
  doc.setTextColor(80, 80, 80)
  
  if (companyConfig.value.address) {
    const addressLines = doc.splitTextToSize(companyConfig.value.address, 70)
    addressLines.forEach(line => {
      doc.text(line, pageWidth - 15, rightYPos, { align: 'right' })
      rightYPos += 3.5
    })
  }
  
  if (companyConfig.value.phone) {
    doc.text(`Tel: ${companyConfig.value.phone}`, pageWidth - 15, rightYPos, { align: 'right' })
    rightYPos += 3.5
  }
  
  if (companyConfig.value.email) {
    doc.text(companyConfig.value.email, pageWidth - 15, rightYPos, { align: 'right' })
    rightYPos += 3.5
  }
  
  if (companyConfig.value.website) {
    doc.text(companyConfig.value.website, pageWidth - 15, rightYPos, { align: 'right' })
  }
  
  // Set yPos to the maximum of left and right columns, plus some spacing
  yPos = Math.max(yPos, rightYPos) + 5
  
  // Line separator
  doc.setDrawColor(37, 99, 235)
  doc.setLineWidth(0.5)
  doc.line(15, yPos, pageWidth - 15, yPos)
  
  yPos += 8 // Space after line

  // Vendor Information & Order Details (two columns)
  doc.setFontSize(10)
  doc.setFont(undefined, 'bold')
  doc.text('INFORMASI VENDOR', 15, yPos)
  doc.text('DETAIL PESANAN', pageWidth / 2 + 10, yPos)
  doc.setFont(undefined, 'normal')
  
  yPos += 7
  
  // Vendor Info (left column)
  const vendorLines = [
    props.po.vendor?.name || 'N/A',
    props.po.vendor?.address || '',
    props.po.vendor?.phone ? `Phone: ${props.po.vendor.phone}` : '',
    props.po.vendor?.email ? `Email: ${props.po.vendor.email}` : ''
  ].filter(line => line)
  
  vendorLines.forEach(line => {
    doc.text(line, 15, yPos)
    yPos += 5
  })
  
  // Order Details (right column)
  const detailsStartY = yPos - (vendorLines.length * 5)
  const details = [
    ['No. PO:', props.po.po_number || 'N/A'],
    ['Tgl. Order:', formatDate(props.po.order_date)],
    ['Tgl. Kirim:', formatDate(props.po.expected_delivery_date)],
    ['Lokasi:', props.po.location?.name || 'N/A'],
    ['Status:', props.po.status || 'N/A']
  ]
  
  let detailY = detailsStartY
  details.forEach(([label, value]) => {
    doc.setFont(undefined, 'normal')
    doc.text(label, pageWidth / 2 + 10, detailY)
    doc.setFont(undefined, 'bold')
    doc.text(value, pageWidth / 2 + 45, detailY)
    detailY += 5
  })
  
  yPos = Math.max(yPos, detailY) + 5

  // Items Table
  const tableData = props.po.items?.map((item, index) => [
    (index + 1).toString(),
    `${item.product?.name || 'N/A'}\nSKU: ${item.product?.sku || 'N/A'}${item.notes ? `\nNote: ${item.notes}` : ''}`,
    item.product?.type || '-',
    `${item.quantity} ${item.product?.unit || 'pcs'}`,
    `Rp ${formatNumber(item.unit_price || 0)}`,
    `Rp ${formatNumber((item.quantity || 0) * (item.unit_price || 0))}`
  ]) || []

  doc.autoTable({
    startY: yPos,
    head: [['No', 'Produk / Deskripsi', 'Tipe', 'Jumlah', 'Harga Satuan', 'Total']],
    body: tableData,
    theme: 'grid',
    headStyles: {
      fillColor: [37, 99, 235],
      textColor: 255,
      fontSize: 9,
      fontStyle: 'bold',
      halign: 'center'
    },
    bodyStyles: {
      fontSize: 8,
      cellPadding: 3
    },
    columnStyles: {
      0: { cellWidth: 10, halign: 'center' },
      1: { cellWidth: 70 },
      2: { cellWidth: 20, halign: 'center' },
      3: { cellWidth: 25, halign: 'center' },
      4: { cellWidth: 30, halign: 'right' },
      5: { cellWidth: 30, halign: 'right' }
    },
    margin: { left: 15, right: 15 }
  })

  yPos = doc.lastAutoTable.finalY + 10

  // Totals Section
  const totalsX = pageWidth - 80
  const subtotal = calculateSubtotal()
  const total = calculateTotal()
  
  doc.setFontSize(9)
  doc.setDrawColor(200, 200, 200)
  
  // Subtotal
  doc.text('Subtotal:', totalsX, yPos)
  doc.text(`Rp ${formatNumber(subtotal)}`, pageWidth - 15, yPos, { align: 'right' })
  yPos += 6
  
  // Tax (if exists)
  if (props.po.tax_amount) {
    doc.text(`Tax (${props.po.tax_percentage || 11}%):`, totalsX, yPos)
    doc.text(`Rp ${formatNumber(props.po.tax_amount)}`, pageWidth - 15, yPos, { align: 'right' })
    yPos += 6
  }
  
  // Discount (if exists)
  if (props.po.discount_amount) {
    doc.text('Discount:', totalsX, yPos)
    doc.text(`- Rp ${formatNumber(props.po.discount_amount)}`, pageWidth - 15, yPos, { align: 'right' })
    yPos += 6
  }
  
  // Total (with background)
  doc.setFillColor(44, 62, 80)
  doc.rect(totalsX - 5, yPos - 5, 80, 8, 'F')
  doc.setTextColor(255, 255, 255)
  doc.setFont(undefined, 'bold')
  doc.setFontSize(11)
  doc.text('TOTAL:', totalsX, yPos)
  doc.text(`Rp ${formatNumber(total)}`, pageWidth - 15, yPos, { align: 'right' })
  
  yPos += 15
  doc.setTextColor(0, 0, 0)
  doc.setFont(undefined, 'normal')
  doc.setFontSize(9)

  // Notes
  if (props.po.notes) {
    doc.setFont(undefined, 'bold')
    doc.text('Catatan:', 15, yPos)
    doc.setFont(undefined, 'normal')
    yPos += 5
    const notesLines = doc.splitTextToSize(props.po.notes, pageWidth - 30)
    doc.text(notesLines, 15, yPos)
    yPos += (notesLines.length * 5) + 5
  }

  // Terms & Conditions
  doc.setFont(undefined, 'bold')
  doc.text('Syarat dan Ketentuan:', 15, yPos)
  doc.setFont(undefined, 'normal')
  yPos += 5
  
  const terms = [
    '1. Mohon konfirmasi penerimaan purchase order ini.',
    '2. Pengiriman harus sesuai tanggal yang tertera di atas.',
    '3. Semua barang harus dalam kondisi baik dan memenuhi standar kualitas.',
    '4. Pembayaran sesuai kesepakatan.',
    '5. Harap mencantumkan nomor PO ini pada semua korespondensi dan invoice.'
  ]
  
  terms.forEach(term => {
    doc.text(term, 15, yPos)
    yPos += 5
  })
  
  yPos += 10

  // Signature Section (ensuring it's on a new page if needed)
  if (yPos > 230) {
    doc.addPage()
    yPos = 20
  }

  doc.setFont(undefined, 'bold')
  doc.setFontSize(9)
  
  const sigWidth = (pageWidth - 30) / 3
  const sig1X = 15
  const sig2X = 15 + sigWidth
  const sig3X = 15 + (sigWidth * 2)
  
  // Signature labels
  doc.text('Dibuat Oleh', sig1X + (sigWidth / 2), yPos, { align: 'center' })
  doc.text('Disetujui Oleh', sig2X + (sigWidth / 2), yPos, { align: 'center' })
  doc.text('Penerimaan Vendor', sig3X + (sigWidth / 2), yPos, { align: 'center' })
  
  yPos += 20
  
  // Signature lines
  doc.setLineWidth(0.3)
  doc.setDrawColor(0, 0, 0)
  doc.line(sig1X + 5, yPos, sig1X + sigWidth - 5, yPos)
  doc.line(sig2X + 5, yPos, sig2X + sigWidth - 5, yPos)
  doc.line(sig3X + 5, yPos, sig3X + sigWidth - 5, yPos)
  
  yPos += 5
  doc.setFont(undefined, 'normal')
  
  // Names
  doc.text(props.po.created_by_name || '', sig1X + (sigWidth / 2), yPos, { align: 'center' })
  doc.text(props.po.approved_by_name || '', sig2X + (sigWidth / 2), yPos, { align: 'center' })
  
  yPos += 5
  doc.setFontSize(8)
  doc.setTextColor(100, 100, 100)
  
  // Dates
  doc.text(`Tanggal: ${formatDate(props.po.created_at)}`, sig1X + (sigWidth / 2), yPos, { align: 'center' })
  doc.text(`Tanggal: ${props.po.approved_at ? formatDate(props.po.approved_at) : '___________'}`, sig2X + (sigWidth / 2), yPos, { align: 'center' })
  doc.text('Tanggal: ___________', sig3X + (sigWidth / 2), yPos, { align: 'center' })

  // Footer
  yPos += 15
  doc.setFontSize(7)
  doc.setTextColor(150, 150, 150)
  doc.text(companyConfig.value.footerText, pageWidth / 2, yPos, { align: 'center' })

  // Save PDF
  doc.save(`PO-${props.po.po_number || 'document'}.pdf`)
  
  // Close modal after download
  setTimeout(() => {
    close()
  }, 500)
}

const close = () => {
  emit('close')
}

// Lock body scroll when modal is open
watch(() => props.show, (newVal) => {
  if (newVal) {
    document.body.style.overflow = 'hidden'
  } else {
    document.body.style.overflow = ''
  }
})
</script>

<style scoped>
.pdf-modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: 9999;
  display: flex;
  justify-content: center;
  align-items: center;
}

.pdf-modal {
  background: white;
  border-radius: 8px;
  width: 500px;
  max-width: 90%;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
}

.pdf-modal-header {
  padding: 20px;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.pdf-modal-header h2 {
  margin: 0;
  font-size: 20px;
  color: #1f2937;
}

.btn-close {
  background: none;
  border: none;
  font-size: 28px;
  color: #6b7280;
  cursor: pointer;
  padding: 0;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 4px;
  transition: background 0.2s;
}

.btn-close:hover {
  background: #f3f4f6;
  color: #111827;
}

.pdf-modal-body {
  padding: 24px;
}

.po-info {
  background: #f9fafb;
  padding: 16px;
  border-radius: 6px;
  margin: 16px 0;
}

.po-info p {
  margin: 8px 0;
  font-size: 14px;
  color: #374151;
}

.pdf-actions {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
  margin-top: 24px;
}

.btn-download {
  background: #10b981;
  color: white;
  border: none;
  padding: 10px 24px;
  border-radius: 6px;
  font-size: 15px;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.2s;
}

.btn-download:hover {
  background: #059669;
}

.btn-cancel {
  background: #e5e7eb;
  color: #374151;
  border: none;
  padding: 10px 24px;
  border-radius: 6px;
  font-size: 15px;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.2s;
}

.btn-cancel:hover {
  background: #d1d5db;
}
</style>
