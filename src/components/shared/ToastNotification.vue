<template>
  <teleport to="body">
    <transition name="toast">
      <div v-if="show" class="toast" :class="type">
        <svg v-if="type === 'success'" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
          <circle cx="12" cy="12" r="10"/><polyline points="20 6 9 17 4 12"/>
        </svg>
        <svg v-else width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
          <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        <span>{{ message }}</span>
        <button @click="$emit('hide')" class="toast-close">✕</button>
      </div>
    </transition>
  </teleport>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
const props = defineProps({ message: String, type: { type: String, default: 'success' }, show: { type: Boolean, default: true } })
const emit = defineEmits(['hide'])
let timer = null
onMounted(() => { timer = setTimeout(() => emit('hide'), 4000) })
</script>

<style scoped>
.toast {
  position: fixed;
  bottom: 32px;
  right: 32px;
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 16px 20px;
  border-radius: var(--radius-lg);
  background: #fff;
  box-shadow: 0 8px 32px rgba(15, 23, 42, 0.2);
  border-left: 4px solid;
  font-size: var(--font-base);
  font-weight: 600;
  z-index: 2000;
  max-width: 380px;
}
.toast.success { border-color: var(--color-primary); color: var(--color-primary); }
.toast.error   { border-color: var(--color-danger);  color: var(--color-danger); }
.toast span    { color: var(--color-text-primary); }
.toast-close   { margin-left: auto; color: var(--color-text-muted); font-size: 14px; }
.toast-close:hover { color: var(--color-text-primary); }

.toast-enter-active { animation: slideInRight 0.3s ease; }
.toast-leave-active { animation: slideInRight 0.2s ease reverse; }
@keyframes slideInRight {
  from { transform: translateX(120%); opacity: 0; }
  to   { transform: translateX(0);    opacity: 1; }
}
</style>
