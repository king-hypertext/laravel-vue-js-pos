<script setup>
import { defineEmits, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  },
  static: {
    type: Boolean,
    default: true
  },
  modalTitle: {
    type: String,
    default: 'Modal',
    required: true
  },
})

const handleKeyboardShortcut = (event) => {
  if (event.key === 'Escape') {
    event.preventDefault();
    emit('close');
  }
}
onMounted(() => {
  // Add the event listener to the entire document when the component is mounted
  document.addEventListener('keydown', handleKeyboardShortcut)
})

onUnmounted(() => {
  // Clean up the event listener when the component is unmounted to prevent memory leaks
  document.removeEventListener('keydown', handleKeyboardShortcut)
})

const emit = defineEmits(['close'])
</script>
<template>
  <div v-if="props.isOpen" class="pos-modal-overlay" @click.self="!props.static && $emit('close')">
    <div class="pos-modal">
      <slot name="modal-title">
        <h3>{{ props.modalTitle }}</h3>
      </slot>
      <div class="pos-modal-body">
        <slot></slot>
      </div>
      <div class="modal-footer">
        <slot name="modal-footer">
          <button class="btn btn-warning" @click="$emit('close')">Close</button>
        </slot>
      </div>
    </div>
  </div>
</template>

<style scoped>
.pos-modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  /* Semi-transparent background */
  display: flex;
  justify-content: center;
  align-items: baseline;
  z-index: 102;
  overflow-y: scroll !important;

  &.show {
    overflow: hidden;
    -webkit-user-select: none;
    user-select: none;
  }

  >.pos-modal {
    background-color: white;
    width: 100%;
    max-width: 580px;
    padding: 1rem 1.25rem;
    border-radius: 5px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
    z-index: 99;
    margin: 45px auto;

    &.large {
      max-width: 640px !important;
    }

    &.xl {
      max-width: 800px !important;
    }


    >.pos-modal-body {
      padding: 15px 0;

      ~.modal-footer {
        display: flex;
        justify-content: end;
        align-items: center;
      }
    }
  }
}
</style>