import { computed } from 'vue'
import { useAuthStore } from '@/stores/auth'

export function useProcurementPermissions() {
  const authStore = useAuthStore()
  
  const user = computed(() => authStore.user)
  
  const isOwner = computed(() => user.value?.role === 'owner')
  
  const isProcurementUser = computed(() => {
    if (!user.value?.location) return false
    return user.value.location.type === 'DEPARTMENT' && 
           user.value.location.name?.toLowerCase().includes('procurement')
  })
  
  const isSupervisor = computed(() => user.value?.role === 'supervisor')
  
  const userLocationId = computed(() => user.value?.location_id)
  
  // Rule 2: Can see all PRs
  const canViewAllPRs = computed(() => {
    return isOwner.value || isProcurementUser.value
  })
  
  // Rule 3: Can approve PR
  // - If PR created by kasir: owner or any kasir can approve
  // - If PR created by non-kasir: supervisor from same department can approve
  const canApprovePR = (pr) => {
    if (!pr || !user.value) return false
    
    // Check if PR was created by kasir - try multiple ways to get the role
    const prCreatorRole = pr.requested_by_role || 
                          pr.requestedBy?.role || 
                          pr.requested_by?.role ||
                          (pr.requested_by && typeof pr.requested_by === 'object' ? pr.requested_by.role : null)
    
    console.log('canApprovePR check:', {
      prCreatorRole,
      userRole: user.value.role,
      isOwner: isOwner.value,
      pr
    })
    
    if (prCreatorRole === 'kasir') {
      // Owner can approve kasir PRs
      if (isOwner.value) return true
      
      // Any kasir can approve kasir PRs (including their own)
      if (user.value.role === 'kasir') {
        return true
      }
      
      return false
    }
    
    // For non-kasir PRs: must be supervisor from same department
    if (!isSupervisor.value) return false
    return pr.location_id === userLocationId.value
  }
  
  // Rule 4: Can create PO from PR
  const canCreatePO = computed(() => {
    return isOwner.value || isProcurementUser.value
  })
  
  // Rule 5: Can approve PO (only owner)
  const canApprovePO = computed(() => {
    return isOwner.value
  })
  
  // Rule 6: Can send PO to vendor
  const canSendPO = computed(() => {
    return isOwner.value || isProcurementUser.value
  })
  
  // Rule 6: Can create GRN
  const canCreateGRN = computed(() => {
    return isOwner.value || isProcurementUser.value
  })
  
  // Can submit for quality check
  const canSubmitQC = computed(() => {
    return isOwner.value || isProcurementUser.value
  })
  
  // Can approve quality check
  const canApproveQC = computed(() => {
    return isOwner.value || isProcurementUser.value
  })
  
  // Rule 6: Can post GRN to inventory
  const canPostGRN = computed(() => {
    return isOwner.value || isProcurementUser.value
  })
  
  return {
    isOwner,
    isProcurementUser,
    isSupervisor,
    userLocationId,
    canViewAllPRs,
    canApprovePR,
    canCreatePO,
    canApprovePO,
    canSendPO,
    canCreateGRN,
    canPostGRN,
    canSubmitQC,
    canApproveQC
  }
}
