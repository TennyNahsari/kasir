<template>
  <MainLayout>
    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center h-64">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="max-w-4xl mx-auto">
      <div class="bg-red-50 border border-red-200 rounded-lg p-6">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
            </svg>
          </div>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-red-800">{{ error }}</h3>
            <button @click="loadTicket" class="mt-2 text-sm text-red-600 hover:text-red-800 underline">
              Try again
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Ticket Detail -->
    <div v-else-if="ticket" class="space-y-6">
      <!-- Operation Error Message -->
      <div v-if="error && !loading" class="bg-red-50 border border-red-200 rounded-lg p-4">
        <div class="flex items-start">
          <svg class="h-5 w-5 text-red-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
          </svg>
          <div class="ml-3 flex-1">
            <p class="text-sm text-red-800">{{ error }}</p>
          </div>
          <button @click="error = null" class="ml-3 text-red-400 hover:text-red-600">
            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Header -->
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
          <button 
            @click="$router.push('/tickets')"
            class="text-gray-600 hover:text-gray-900"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
          </button>
          <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ ticket.ticket_number }}</h1>
            <p class="text-sm text-gray-600 mt-1">Created {{ formatDateTime(ticket.created_at) }}</p>
          </div>
        </div>
        <div class="flex items-center gap-3">
          <button
            v-if="canDeleteTicket"
            @click="showDeleteConfirm = true"
            class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700"
          >
            Delete Ticket
          </button>
          <button
            v-if="canUpdateStatus"
            @click="showStatusModal = true"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
          >
            Update Status
          </button>
          <button
            v-if="canAddWorklog"
            @click="showWorklogModal = true"
            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700"
          >
            Add Worklog
          </button>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content - 2 columns -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Ticket Info Card -->
          <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ ticket.title }}</h2>
            
            <div class="flex flex-wrap gap-2 mb-4">
              <span :class="getStatusClass(ticket.status)" class="px-3 py-1 text-sm font-semibold rounded-full">
                {{ formatStatus(ticket.status) }}
              </span>
              <span :class="getTypeClass(ticket.type)" class="px-3 py-1 text-sm font-semibold rounded-full">
                {{ ticket.type }}
              </span>
              <span :class="getPriorityClass(ticket.priority)" class="px-3 py-1 text-sm font-semibold rounded-full">
                {{ ticket.priority }}
              </span>
              <span v-if="isOverdue(ticket)" class="px-3 py-1 text-sm font-semibold rounded-full bg-red-100 text-red-800">
                OVERDUE
              </span>
            </div>

            <div class="prose max-w-none">
              <h3 class="text-sm font-medium text-gray-700 mb-2">Description</h3>
              <p class="text-gray-900 whitespace-pre-wrap">{{ ticket.description }}</p>
            </div>
          </div>

          <!-- Timeline -->
          <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Timeline & Worklogs</h2>
            
            <div v-if="!ticket.worklogs || ticket.worklogs.length === 0" class="text-center py-8 text-gray-500">
              <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
              </svg>
              <p class="mt-2">No worklogs yet</p>
            </div>

            <div v-else class="flow-root">
              <ul class="-mb-8">
                <li v-for="(worklog, index) in ticket.worklogs" :key="worklog.id" class="relative pb-8">
                  <span
                    v-if="index !== ticket.worklogs.length - 1"
                    class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-gray-200"
                    aria-hidden="true"
                  ></span>
                  <div class="relative flex space-x-3">
                    <div>
                      <span 
                        class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white"
                        :class="worklog.worklog_type === 'STATUS_CHANGE' ? 'bg-purple-500' : 'bg-blue-500'"
                      >
                        <svg v-if="worklog.worklog_type === 'STATUS_CHANGE'" class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <svg v-else class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                      </span>
                    </div>
                    <div class="flex-1 min-w-0">
                      <div class="flex justify-between items-start">
                        <div>
                          <p class="text-sm font-medium text-gray-900">
                            {{ worklog.user?.name }}
                            <span v-if="worklog.worklog_type === 'STATUS_CHANGE'" class="font-normal text-gray-600">
                              changed ticket status
                            </span>
                            <span v-else-if="worklog.worklog_type === 'WORK_DONE'" class="font-normal text-gray-600">
                              added work log
                            </span>
                          </p>
                          <p class="text-sm text-gray-500">{{ formatDateTime(worklog.created_at) }}</p>
                        </div>
                        <span v-if="worklog.time_spent_minutes" class="text-xs text-gray-500">
                          {{ worklog.time_spent_minutes }} min
                        </span>
                      </div>
                      <div v-if="worklog.description" class="mt-2 text-sm text-gray-700 bg-gray-50 rounded p-3 whitespace-pre-wrap">
                        {{ worklog.description }}
                      </div>
                    </div>
                  </div>
                </li>
              </ul>
            </div>
          </div>

          <!-- Attachments -->
          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-center mb-4">
              <h2 class="text-lg font-semibold text-gray-900">Attachments</h2>
              <button
                @click="$refs.fileInput.click()"
                class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-blue-600 bg-blue-50 rounded-md hover:bg-blue-100"
              >
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Upload File
              </button>
              <input
                ref="fileInput"
                type="file"
                @change="handleFileUpload"
                class="hidden"
                accept="image/*,.pdf,.doc,.docx,.xls,.xlsx"
              />
            </div>

            <!-- Upload Progress -->
            <div v-if="uploading" class="mb-4 bg-blue-50 border border-blue-200 rounded-lg p-4">
              <div class="flex items-center">
                <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-blue-600 mr-3"></div>
                <span class="text-sm text-blue-800">Uploading file...</span>
              </div>
            </div>

            <!-- Attachments List -->
            <div v-if="ticket.attachments && ticket.attachments.length > 0" class="space-y-3">
              <div
                v-for="attachment in ticket.attachments"
                :key="attachment.id"
                class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100"
              >
                <div class="flex items-center flex-1 min-w-0">
                  <div class="flex-shrink-0">
                    <svg v-if="isImage(attachment.file_type)" class="w-8 h-8 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd" />
                    </svg>
                    <svg v-else class="w-8 h-8 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" />
                    </svg>
                  </div>
                  <div class="ml-3 flex-1 min-w-0">
                    <a
                      :href="attachment.file_url"
                      target="_blank"
                      class="text-sm font-medium text-gray-900 hover:text-blue-600 truncate block"
                    >
                      {{ attachment.file_name }}
                    </a>
                    <div class="text-xs text-gray-500 mt-0.5">
                      {{ formatFileSize(attachment.file_size) }} • 
                      Uploaded by {{ attachment.uploader?.name }} • 
                      {{ formatDateTime(attachment.created_at) }}
                    </div>
                  </div>
                </div>
                <button
                  v-if="canDeleteAttachment(attachment)"
                  @click="confirmDeleteAttachment(attachment)"
                  class="ml-3 text-red-600 hover:text-red-800"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                </button>
              </div>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-8 text-gray-500">
              <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
              </svg>
              <p class="mt-2">No attachments</p>
            </div>
          </div>
        </div>

        <!-- Sidebar - 1 column -->
        <div class="space-y-6">
          <!-- Asset Info -->
          <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-sm font-semibold text-gray-900 mb-4">Asset Information</h3>
            <dl class="space-y-3">
              <div>
                <dt class="text-xs font-medium text-gray-500">Product</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ ticket.asset?.product?.name }}</dd>
              </div>
              <div>
                <dt class="text-xs font-medium text-gray-500">Asset Code</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ ticket.asset?.asset_code }}</dd>
              </div>
              <div v-if="ticket.asset?.serial_number">
                <dt class="text-xs font-medium text-gray-500">Serial Number</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ ticket.asset.serial_number }}</dd>
              </div>
              <div v-if="ticket.asset?.pic">
                <dt class="text-xs font-medium text-gray-500">PIC</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ ticket.asset.pic }}</dd>
              </div>
              <div>
                <dt class="text-xs font-medium text-gray-500">Location</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ ticket.location?.name || '-' }}</dd>
              </div>
              <div>
                <dt class="text-xs font-medium text-gray-500">Status</dt>
                <dd class="mt-1">
                  <span :class="getAssetStatusClass(ticket.asset?.status)" class="px-2 py-1 text-xs font-semibold rounded-full">
                    {{ ticket.asset?.status }}
                  </span>
                </dd>
              </div>
            </dl>
          </div>

          <!-- Assignment Info -->
          <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-sm font-semibold text-gray-900 mb-4">Assignment</h3>
            <dl class="space-y-3">
              <div>
                <dt class="text-xs font-medium text-gray-500">Reported By</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ ticket.reporter?.name }}</dd>
              </div>
              <div>
                <dt class="text-xs font-medium text-gray-500">Assigned To</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ ticket.assigned_user?.name || 'Unassigned' }}</dd>
              </div>
              <div>
                <dt class="text-xs font-medium text-gray-500">Report Date</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ formatDate(ticket.created_at) }}</dd>
              </div>
              <div v-if="ticket.scheduled_date">
                <dt class="text-xs font-medium text-gray-500">Scheduled Date</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ formatDateTime(ticket.scheduled_date) }}</dd>
              </div>
            </dl>
          </div>

          <!-- SLA Info -->
          <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-sm font-semibold text-gray-900 mb-4">SLA Information</h3>
            <dl class="space-y-3">
              <div>
                <dt class="text-xs font-medium text-gray-500">SLA Due Date</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ formatDateTime(ticket.sla_due_date) }}</dd>
              </div>
              <div>
                <dt class="text-xs font-medium text-gray-500">Time Remaining</dt>
                <dd class="mt-1 text-sm font-semibold" :class="isOverdue(ticket) ? 'text-red-600' : 'text-green-600'">
                  {{ getTimeRemaining(ticket.sla_due_date) }}
                </dd>
              </div>
              <div v-if="ticket.resolved_at">
                <dt class="text-xs font-medium text-gray-500">Resolved At</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ formatDateTime(ticket.resolved_at) }}</dd>
              </div>
              <div v-if="ticket.rating">
                <dt class="text-xs font-medium text-gray-500">Rating</dt>
                <dd class="mt-1 text-sm text-gray-900">
                  <div class="flex items-center">
                    <span class="text-yellow-400">★</span>
                    <span class="ml-1">{{ ticket.rating }}/5</span>
                  </div>
                </dd>
              </div>
            </dl>
          </div>
        </div>
      </div>
    </div>

    <!-- Update Status Modal -->
    <UpdateStatusModal
      v-if="showStatusModal"
      :ticket="ticket"
      @close="showStatusModal = false"
      @updated="handleStatusUpdated"
    />

    <!-- Add Worklog Modal -->
    <AddWorklogModal
      v-if="showWorklogModal"
      :ticket="ticket"
      @close="showWorklogModal = false"
      @added="handleWorklogAdded"
    />
    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteConfirm" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click.self="showDeleteConfirm = false">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
            <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
          </div>
          <h3 class="text-lg font-medium text-gray-900 text-center mt-4">Delete Ticket</h3>
          <div class="mt-2 px-4 py-3">
            <p class="text-sm text-gray-500 text-center">
              Are you sure you want to delete this ticket?
            </p>
            <p class="text-sm font-semibold text-gray-900 text-center mt-2">
              {{ ticket.ticket_number }}
            </p>
            <p class="text-xs text-red-600 text-center mt-2">
              This action cannot be undone.
            </p>
          </div>
          <div class="flex gap-3 px-4 py-3">
            <button
              @click="showDeleteConfirm = false"
              :disabled="deleting"
              class="flex-1 px-4 py-2 bg-white text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50 text-sm font-medium disabled:opacity-50"
            >
              Cancel
            </button>
            <button
              @click="handleDelete"
              :disabled="deleting"
              class="flex-1 px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 text-sm font-medium disabled:opacity-50"
            >
              {{ deleting ? 'Deleting...' : 'Delete' }}
            </button>
          </div>
        </div>
      </div>
    </div>  </MainLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import MainLayout from '@/layouts/MainLayout.vue'
import UpdateStatusModal from '@/components/UpdateStatusModal.vue'
import AddWorklogModal from '@/components/AddWorklogModal.vue'
import ticketService from '@/services/ticketService'
import {
  getStatusClass,
  getPriorityClass,
  getTypeClass,
  getAssetStatusClass,
  formatStatus,
  formatDate,
  formatDateTime,
  isOverdue,
  getTimeRemaining
} from '@/utils/ticketHelpers'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const ticket = ref(null)
const loading = ref(false)
const error = ref(null)
const showStatusModal = ref(false)
const showWorklogModal = ref(false)
const showDeleteConfirm = ref(false)
const deleting = ref(false)
const uploading = ref(false)
const attachmentToDelete = ref(null)

const canUpdateStatus = computed(() => {
  const user = authStore.user
  if (user?.role === 'owner' || user?.is_technician === true) return true
  if (user?.role === 'supervisor') return true
  return false
})

const canAddWorklog = computed(() => {
  const user = authStore.user
  if (user?.role === 'owner' || user?.is_technician === true) return true
  if (['supervisor', 'staff', 'kasir'].includes(user?.role)) return true
  return false
})

const canDeleteTicket = computed(() => {
  const user = authStore.user
  return user?.role === 'owner' || user?.is_technician === true || user?.role === 'supervisor'
})

const canDeleteAttachment = (attachment) => {
  const user = authStore.user
  if (user?.role === 'owner' || user?.is_technician === true || user?.role === 'supervisor') return true
  if (attachment.uploaded_by === user?.id) return true
  return false
}

const isImage = (fileType) => {
  return fileType?.startsWith('image/')
}

const formatFileSize = (bytes) => {
  const units = ['B', 'KB', 'MB', 'GB']
  let size = bytes
  let unitIndex = 0
  
  while (size > 1024 && unitIndex < units.length - 1) {
    size /= 1024
    unitIndex++
  }
  
  return `${size.toFixed(2)} ${units[unitIndex]}`
}

const loadTicket = async () => {
  try {
    loading.value = true
    error.value = null
    const response = await ticketService.getTicket(route.params.id)
    ticket.value = response.data
  } catch (err) {
    console.error('Error loading ticket:', err)
    error.value = err.response?.data?.message || 'Failed to load ticket details'
  } finally {
    loading.value = false
  }
}

const handleStatusUpdated = () => {
  showStatusModal.value = false
  loadTicket()
}

const handleWorklogAdded = () => {
  showWorklogModal.value = false
  loadTicket()
}

const handleDelete = async () => {
  try {
    deleting.value = true
    await ticketService.deleteTicket(route.params.id)
    router.push({ path: '/tickets', query: { deleted: 'true' } })
  } catch (err) {
    console.error('Error deleting ticket:', err)
    error.value = err.response?.data?.message || 'Failed to delete ticket'
    showDeleteConfirm.value = false
  } finally {
    deleting.value = false
  }
}

const handleFileUpload = async (event) => {
  const file = event.target.files[0]
  if (!file) return

  // Validate file size (max 10MB)
  if (file.size > 10 * 1024 * 1024) {
    error.value = 'File size must be less than 10MB'
    return
  }

  try {
    uploading.value = true
    error.value = null

    const formData = new FormData()
    formData.append('file', file)

    const response = await ticketService.uploadAttachment(route.params.id, formData)
    
    // Refresh ticket to get updated attachments
    await loadTicket()
    
    // Clear file input
    event.target.value = ''
  } catch (err) {
    console.error('Error uploading file:', err)
    error.value = err.response?.data?.message || 'Failed to upload file'
  } finally {
    uploading.value = false
  }
}

const confirmDeleteAttachment = (attachment) => {
  if (confirm(`Delete ${attachment.file_name}?`)) {
    deleteAttachment(attachment.id)
  }
}

const deleteAttachment = async (attachmentId) => {
  try {
    await ticketService.deleteAttachment(route.params.id, attachmentId)
    await loadTicket()
  } catch (err) {
    console.error('Error deleting attachment:', err)
    error.value = err.response?.data?.message || 'Failed to delete attachment'
  }
}

onMounted(() => {
  loadTicket()
})
</script>
