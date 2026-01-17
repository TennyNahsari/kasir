<template>
  <div v-if="show" class="pdf-modal-overlay">
    <div class="pdf-modal">
      <div class="pdf-modal-header">
        <h2>Generate Goods Receipt PDF</h2>
        <button @click="close" class="btn-close">×</button>
      </div>
      <div class="pdf-modal-body">
        <p>Click the button below to download the Goods Receipt Note PDF.</p>
        <div class="po-info">
          <p><strong>GRN Number:</strong> {{ grn.grn_no }}</p>
          <p><strong>PO Number:</strong> {{ grn.purchase_order?.po_no || 'N/A' }}</p>
          <p><strong>Vendor:</strong> {{ grn.purchase_order?.vendor?.name || 'N/A' }}</p>
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
  grn: {
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

const generatePDF = () => {
  const doc = new jsPDF('p', 'mm', 'a4')
  const pageWidth = doc.internal.pageSize.getWidth()
  let yPos = 20
  let rightYPos = 20

  // Header - GOODS RECEIPT NOTE (left side)
  doc.setFontSize(24)
  doc.setTextColor(16, 185, 129) // Green color
  doc.setFont(undefined, 'bold')
  doc.text('GOODS RECEIPT NOTE', 15, yPos)
  
  yPos += 8
  doc.setFontSize(12)
  doc.setTextColor(0, 0, 0)
  doc.text(props.grn.grn_no || '', 15, yPos)
  
  // Company Name (right side)
  doc.setFontSize(11)
  doc.setFont(undefined, 'bold')
  doc.setTextColor(0, 0, 0)
  doc.text(companyConfig.value.name, pageWidth - 15, rightYPos, { align: 'right' })
  
  rightYPos += 5
  
  // Company details (right side)
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
  
  yPos = Math.max(yPos, rightYPos) + 5
  
  // Line separator
  doc.setDrawColor(16, 185, 129)
  doc.setLineWidth(0.5)
  doc.line(15, yPos, pageWidth - 15, yPos)
  
  yPos += 8

  // PO & Vendor Information
  doc.setFontSize(10)
  doc.setFont(undefined, 'bold')
  doc.text('INFORMASI PURCHASE ORDER', 15, yPos)
  doc.text('INFORMASI PENERIMAAN', pageWidth / 2 + 10, yPos)
  doc.setFont(undefined, 'normal')
  
  yPos += 7
  
  // Left column - PO Info
  const leftYStart = yPos
  doc.setFontSize(9)
  doc.text(`No. PO: ${props.grn.purchase_order?.po_no || 'N/A'}`, 15, yPos)
  yPos += 5
  doc.text(`Vendor: ${props.grn.purchase_order?.vendor?.name || 'N/A'}`, 15, yPos)
  yPos += 5
  if (props.grn.purchase_order?.vendor?.address) {
    doc.text(`Alamat: ${props.grn.purchase_order.vendor.address}`, 15, yPos)
    yPos += 5
  }
  
  // Right column - Receipt Info
  let rightY = leftYStart
  doc.text(`Tgl. Terima: ${formatDate(props.grn.receipt_date)}`, pageWidth / 2 + 10, rightY)
  rightY += 5
  if (props.grn.delivery_note_no) {
    doc.text(`No. Surat Jalan: ${props.grn.delivery_note_no}`, pageWidth / 2 + 10, rightY)
    rightY += 5
  }
  doc.text(`Lokasi: ${props.grn.location?.name || 'N/A'}`, pageWidth / 2 + 10, rightY)
  rightY += 5
  doc.text(`Status: ${props.grn.status || 'N/A'}`, pageWidth / 2 + 10, rightY)
  
  yPos = Math.max(yPos, rightY) + 5

  // Items Table
  const tableData = props.grn.items?.map((item, index) => {
    const productName = item.product?.name || item.product_name || 'N/A'
    const productSKU = item.product?.sku || item.product_sku || 'N/A'
    const productType = item.product?.type || item.product_type || '-'
    const serialInfo = productType === 'ASSET' && item.serial_numbers?.length > 0
      ? `\nSerial: ${item.serial_numbers.join(', ')}`
      : ''
    
    return [
      (index + 1).toString(),
      `${productName}\nSKU: ${productSKU}${serialInfo}`,
      productType,
      formatNumber(item.quantity_ordered || item.ordered_quantity || 0),
      formatNumber(item.quantity_received || item.received_quantity || 0),
      formatNumber(item.quantity_rejected || item.rejected_quantity || 0)
    ]
  }) || []
  
  console.log('GRN Items for PDF:', props.grn.items)
  console.log('Table Data:', tableData)

  doc.autoTable({
    startY: yPos,
    head: [['No', 'Produk / Serial', 'Tipe', 'Dipesan', 'Diterima', 'Ditolak']],
    body: tableData,
    theme: 'grid',
    headStyles: {
      fillColor: [16, 185, 129],
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
      1: { cellWidth: 80 },
      2: { cellWidth: 20, halign: 'center' },
      3: { cellWidth: 20, halign: 'right' },
      4: { cellWidth: 20, halign: 'right' },
      5: { cellWidth: 20, halign: 'right' }
    },
    margin: { left: 15, right: 15 }
  })

  yPos = doc.lastAutoTable.finalY + 10

  // Notes
  if (props.grn.notes) {
    doc.setFontSize(9)
    doc.setFont(undefined, 'bold')
    doc.text('Catatan:', 15, yPos)
    doc.setFont(undefined, 'normal')
    yPos += 5
    const notesLines = doc.splitTextToSize(props.grn.notes, pageWidth - 30)
    doc.text(notesLines, 15, yPos)
    yPos += (notesLines.length * 5) + 10
  }

  // Signature Section
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
  doc.text('Diterima Oleh', sig1X + (sigWidth / 2), yPos, { align: 'center' })
  doc.text('Diperiksa Oleh', sig2X + (sigWidth / 2), yPos, { align: 'center' })
  doc.text('Disetujui Oleh', sig3X + (sigWidth / 2), yPos, { align: 'center' })
  
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
  doc.text(props.grn.received_by_name || '', sig1X + (sigWidth / 2), yPos, { align: 'center' })
  doc.text(props.grn.inspected_by_name || '', sig2X + (sigWidth / 2), yPos, { align: 'center' })
  doc.text(props.grn.approved_by_name || '', sig3X + (sigWidth / 2), yPos, { align: 'center' })
  
  yPos += 5
  doc.setFontSize(8)
  doc.setTextColor(100, 100, 100)
  
  // Dates
  doc.text(`Tanggal: ${formatDate(props.grn.receipt_date)}`, sig1X + (sigWidth / 2), yPos, { align: 'center' })
  doc.text(`Tanggal: ${props.grn.qc_checked_at ? formatDate(props.grn.qc_checked_at) : '___________'}`, sig2X + (sigWidth / 2), yPos, { align: 'center' })
  doc.text(`Tanggal: ${props.grn.approved_at ? formatDate(props.grn.approved_at) : '___________'}`, sig3X + (sigWidth / 2), yPos, { align: 'center' })

  // Footer
  yPos += 15
  doc.setFontSize(7)
  doc.setTextColor(150, 150, 150)
  doc.text(companyConfig.value.footerText, pageWidth / 2, yPos, { align: 'center' })

  // Save PDF
  doc.save(`GRN-${props.grn.grn_no || 'document'}.pdf`)
  
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
