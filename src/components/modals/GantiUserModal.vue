<template>
  <div class="modal-overlay" @click.self="$emit('close')">
    <div class="modal-container">
      <div class="modal-header">
        <h2>Ganti User / Pilih Akun</h2>
        <button class="close-btn" @click="$emit('close')">&times;</button>
      </div>

      <div class="user-list">
        <div 
          v-for="user in users" 
          :key="user.id" 
          class="user-item" 
          :class="{ active: user.id === currentUser.id }"
          @click="selectUser(user)"
        >
          <div class="user-avatar" :style="{ backgroundColor: user.color }">
            {{ user.initials }}
          </div>
          <div class="user-info">
            <span class="user-name">{{ user.name }}</span>
            <span class="user-role">{{ user.roleName }}</span>
          </div>
          <div class="user-status">
            <span v-if="user.id === currentUser.id" class="active-badge">Aktif</span>
            <i v-else class="fas fa-chevron-right"></i>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn-add-account" @click="$emit('close')">
          <i class="fas fa-user-plus"></i> Tambah Akun Lain
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
  currentUser: {
    type: Object,
    default: () => ({ id: 1 })
  }
})

const emit = defineEmits(['close', 'select'])

const users = ref([
  { id: 1, name: 'Admin Utama', initials: 'A', roleName: 'Pemilik Toko', color: '#059669' },
  { id: 2, name: 'Siti Kasir', initials: 'S', roleName: 'Kasir Shift Pagi', color: '#2563EB' },
  { id: 3, name: 'Budi Gudang', initials: 'B', roleName: 'Admin Stok', color: '#D97706' },
  { id: 4, name: 'Rani Penjual', initials: 'R', roleName: 'Kasir Shift Sore', color: '#7C3AED' }
])

const selectUser = (user) => {
  emit('select', user)
  emit('close')
}
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(15, 23, 42, 0.6);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-container {
  background: white;
  width: 100%;
  max-width: 440px;
  border-radius: 24px;
  overflow: hidden;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  animation: modalIn 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes modalIn {
  from { opacity: 0; transform: scale(0.95) translateY(10px); }
  to { opacity: 1; transform: scale(1) translateY(0); }
}

.modal-header {
  padding: 24px;
  border-bottom: 1px solid #F1F5F9;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h2 {
  font-size: 18px;
  font-weight: 700;
  color: #1E293B;
}

.close-btn {
  background: none;
  border: none;
  font-size: 24px;
  color: #94A3B8;
  cursor: pointer;
}

.user-list {
  padding: 12px;
}

.user-item {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 16px;
  border-radius: 16px;
  cursor: pointer;
  transition: all 0.2s;
  margin-bottom: 4px;
}

.user-item:hover {
  background: #F8FAFC;
}

.user-item.active {
  background: #F1F5F9;
}

.user-avatar {
  width: 48px;
  height: 48px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 700;
  font-size: 20px;
}

.user-info {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.user-name {
  font-size: 16px;
  font-weight: 600;
  color: #1E293B;
}

.user-role {
  font-size: 13px;
  color: #64748B;
}

.active-badge {
  background: #ECFDF5;
  color: #059669;
  font-size: 11px;
  font-weight: 700;
  padding: 4px 10px;
  border-radius: 100px;
}

.user-status i {
  color: #CBD5E1;
  font-size: 14px;
}

.modal-footer {
  padding: 24px;
  background: #F8FAFC;
  border-top: 1px solid #F1F5F9;
}

.btn-add-account {
  width: 100%;
  padding: 12px;
  background: white;
  border: 1.5px dashed #CBD5E1;
  border-radius: 12px;
  color: #64748B;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.btn-add-account:hover {
  border-color: #0F172A;
  color: #0F172A;
  background: #F1F5F9;
}
</style>
