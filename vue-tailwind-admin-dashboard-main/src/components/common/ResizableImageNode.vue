<template>
  <node-view-wrapper
    :class="[
      'image-node-view my-4 select-none relative block w-full',
      wrapperAlignClass
    ]"
    :data-align="node.attrs.alignment || 'center'"
  >
    <div
      ref="containerRef"
      class="relative inline-block group max-w-full"
      :style="{
        width: displayWidth ? `${displayWidth}px` : 'auto',
        maxWidth: '100%'
      }"
    >
      <!-- Image Element -->
      <img
        ref="imgRef"
        :src="node.attrs.src"
        :alt="node.attrs.alt || ''"
        :title="node.attrs.title || ''"
        :style="{
          width: displayWidth ? `${displayWidth}px` : 'auto',
          maxWidth: '100%',
          height: 'auto'
        }"
        :class="[
          'rounded-xl block object-cover transition-all shadow-xs cursor-pointer',
          selected || isResizing
            ? 'ring-2 ring-brand-500 ring-offset-2 ring-offset-white dark:ring-offset-gray-900 shadow-md'
            : 'hover:ring-1 hover:ring-brand-300 dark:hover:ring-brand-700'
        ]"
        @click="selectImage"
        @load="onImageLoaded"
        draggable="false"
      />

      <!-- Caption / Title Overlay -->
      <div
        v-if="node.attrs.title && !isResizing"
        class="text-center text-xs text-gray-500 dark:text-gray-400 mt-1 italic"
      >
        {{ node.attrs.title }}
      </div>

      <!-- Active Selection & Resize Handles -->
      <template v-if="selected || isResizing">
        <!-- 4 Corner Handles -->
        <!-- Top-Left (NW) -->
        <div
          class="resize-handle top-0 left-0 -translate-x-1/2 -translate-y-1/2 cursor-nwse-resize"
          @mousedown.prevent.stop="startResize('nw', $event)"
          title="Tarik untuk mengubah ukuran"
        />
        <!-- Top-Right (NE) -->
        <div
          class="resize-handle top-0 right-0 translate-x-1/2 -translate-y-1/2 cursor-nesw-resize"
          @mousedown.prevent.stop="startResize('ne', $event)"
          title="Tarik untuk mengubah ukuran"
        />
        <!-- Bottom-Left (SW) -->
        <div
          class="resize-handle bottom-0 left-0 -translate-x-1/2 translate-y-1/2 cursor-nesw-resize"
          @mousedown.prevent.stop="startResize('sw', $event)"
          title="Tarik untuk mengubah ukuran"
        />
        <!-- Bottom-Right (SE) -->
        <div
          class="resize-handle bottom-0 right-0 translate-x-1/2 translate-y-1/2 cursor-nwse-resize"
          @mousedown.prevent.stop="startResize('se', $event)"
          title="Tarik untuk mengubah ukuran"
        />

        <!-- 2 Side Handles (W, E) -->
        <!-- Middle-Left (W) -->
        <div
          class="resize-handle top-1/2 left-0 -translate-x-1/2 -translate-y-1/2 cursor-ew-resize"
          @mousedown.prevent.stop="startResize('w', $event)"
          title="Ubah lebar"
        />
        <!-- Middle-Right (E) -->
        <div
          class="resize-handle top-1/2 right-0 translate-x-1/2 -translate-y-1/2 cursor-ew-resize"
          @mousedown.prevent.stop="startResize('e', $event)"
          title="Ubah lebar"
        />

        <!-- Width Dimension Badge -->
        <div
          class="absolute bottom-2 right-2 px-2 py-0.5 rounded bg-gray-900/85 text-white text-[10px] font-mono shadow-sm pointer-events-none z-20 flex items-center gap-1"
        >
          <span>{{ Math.round(displayWidth || currentCalculatedWidth) }} &times; {{ Math.round((displayWidth || currentCalculatedWidth) / (aspectRatio || 1.33)) }} px</span>
        </div>

        <!-- Floating Image Quick Alignment Toolbar -->
        <div
          v-if="!isResizing"
          class="absolute -top-10 left-1/2 -translate-x-1/2 flex items-center gap-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-lg rounded-lg px-2 py-1 z-30"
          @mousedown.prevent.stop
        >
          <!-- Align Left -->
          <button
            type="button"
            @click.stop="setAlignment('left')"
            :class="[
              'p-1 rounded text-xs transition',
              (node.attrs.alignment === 'left') ? 'bg-brand-100 text-brand-700 dark:bg-brand-900 dark:text-brand-300' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
            ]"
            title="Rata Kiri"
          >
            <AlignLeftIcon class="w-3.5 h-3.5" />
          </button>
          <!-- Align Center -->
          <button
            type="button"
            @click.stop="setAlignment('center')"
            :class="[
              'p-1 rounded text-xs transition',
              (!node.attrs.alignment || node.attrs.alignment === 'center') ? 'bg-brand-100 text-brand-700 dark:bg-brand-900 dark:text-brand-300' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
            ]"
            title="Rata Tengah"
          >
            <AlignCenterIcon class="w-3.5 h-3.5" />
          </button>
          <!-- Align Right -->
          <button
            type="button"
            @click.stop="setAlignment('right')"
            :class="[
              'p-1 rounded text-xs transition',
              (node.attrs.alignment === 'right') ? 'bg-brand-100 text-brand-700 dark:bg-brand-900 dark:text-brand-300' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
            ]"
            title="Rata Kanan"
          >
            <AlignRightIcon class="w-3.5 h-3.5" />
          </button>

          <span class="w-px h-3.5 bg-gray-200 dark:bg-gray-700 mx-0.5" />

          <!-- Quick Presets -->
          <button
            type="button"
            @click.stop="setPresetWidth(0.5)"
            class="px-1.5 py-0.5 rounded text-[10px] font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
            title="Ukuran 50%"
          >
            50%
          </button>
          <button
            type="button"
            @click.stop="setPresetWidth(1.0)"
            class="px-1.5 py-0.5 rounded text-[10px] font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
            title="Ukuran Penuh (100%)"
          >
            100%
          </button>
          <button
            type="button"
            @click.stop="resetSize"
            class="px-1.5 py-0.5 rounded text-[10px] font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
            title="Reset Ukuran Natural"
          >
            Auto
          </button>
        </div>
      </template>
    </div>
  </node-view-wrapper>
</template>

<script setup lang="ts">
import { ref, computed, onBeforeUnmount } from 'vue'
import { NodeViewWrapper, nodeViewProps } from '@tiptap/vue-3'
import { AlignLeftIcon, AlignCenterIcon, AlignRightIcon } from 'lucide-vue-next'

const props = defineProps(nodeViewProps)

const imgRef = ref<HTMLImageElement | null>(null)
const containerRef = ref<HTMLDivElement | null>(null)

const isResizing = ref(false)
const localWidth = ref<number | null>(null)
const currentCalculatedWidth = ref(0)

// Natural dimensions
const naturalWidth = ref(0)
const naturalHeight = ref(0)
const aspectRatio = ref(1.33)

const onImageLoaded = () => {
  if (imgRef.value) {
    naturalWidth.value = imgRef.value.naturalWidth
    naturalHeight.value = imgRef.value.naturalHeight
    if (imgRef.value.naturalHeight > 0) {
      aspectRatio.value = imgRef.value.naturalWidth / imgRef.value.naturalHeight
    }
    currentCalculatedWidth.value = imgRef.value.clientWidth
  }
}

// Display width is either active localWidth during drag, saved node.attrs.width, or null
const displayWidth = computed(() => {
  if (localWidth.value !== null) return localWidth.value
  if (props.node.attrs.width) return Number(props.node.attrs.width)
  return null
})

const wrapperAlignClass = computed(() => {
  const align = props.node.attrs.alignment || 'center'
  if (align === 'left') return 'text-left'
  if (align === 'right') return 'text-right'
  return 'text-center'
})

const selectImage = () => {
  if (typeof props.getPos === 'function') {
    const pos = props.getPos()
    if (typeof pos === 'number') {
      props.editor.commands.setNodeSelection(pos)
    }
  }
}

const setAlignment = (align: 'left' | 'center' | 'right') => {
  props.updateAttributes({ alignment: align })
}

const setPresetWidth = (fraction: number) => {
  const editorEl = props.editor.view.dom
  const maxW = editorEl.clientWidth - 32 // padding
  const newW = Math.round(Math.max(120, maxW * fraction))
  localWidth.value = null
  props.updateAttributes({ width: newW })
}

const resetSize = () => {
  localWidth.value = null
  props.updateAttributes({ width: null, height: null })
}

// ─── Drag Resize Handler ───────────────────────────────────────────────────
let startX = 0
let startY = 0
let startWidth = 0
let startHeight = 0
let activeDirection = ''
let maxContainerWidth = 800

const onMouseMove = (e: MouseEvent) => {
  if (!isResizing.value) return

  const deltaX = e.clientX - startX
  let newWidth = startWidth

  if (activeDirection === 'se' || activeDirection === 'e' || activeDirection === 'ne') {
    newWidth = startWidth + deltaX
  } else if (activeDirection === 'sw' || activeDirection === 'w' || activeDirection === 'nw') {
    newWidth = startWidth - deltaX
  }

  // Constraints: minimum 100px, maximum container width
  const minWidth = 100
  newWidth = Math.max(minWidth, Math.min(newWidth, maxContainerWidth))

  localWidth.value = Math.round(newWidth)
}

const onMouseUp = () => {
  if (!isResizing.value) return

  isResizing.value = false
  window.removeEventListener('mousemove', onMouseMove)
  window.removeEventListener('mouseup', onMouseUp)

  if (localWidth.value !== null) {
    const finalWidth = localWidth.value
    localWidth.value = null
    props.updateAttributes({
      width: finalWidth
    })
  }
}

const startResize = (direction: string, event: MouseEvent) => {
  event.preventDefault()
  event.stopPropagation()

  selectImage()

  activeDirection = direction
  startX = event.clientX
  startY = event.clientY

  if (imgRef.value) {
    const rect = imgRef.value.getBoundingClientRect()
    startWidth = rect.width
    startHeight = rect.height
    if (startHeight > 0) {
      aspectRatio.value = startWidth / startHeight
    }
  } else {
    startWidth = Number(props.node.attrs.width) || 400
    startHeight = 300
    aspectRatio.value = 4 / 3
  }

  const editorDom = props.editor.view.dom
  maxContainerWidth = editorDom.clientWidth - 16

  isResizing.value = true
  localWidth.value = startWidth

  window.addEventListener('mousemove', onMouseMove)
  window.addEventListener('mouseup', onMouseUp)
}

onBeforeUnmount(() => {
  window.removeEventListener('mousemove', onMouseMove)
  window.removeEventListener('mouseup', onMouseUp)
})
</script>

<style scoped>
.resize-handle {
  position: absolute;
  width: 10px;
  height: 10px;
  background-color: #ffffff;
  border: 2px solid #2563eb;
  border-radius: 9999px;
  box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.25);
  z-index: 20;
  transition: transform 0.15s ease, background-color 0.15s ease;
}

.resize-handle:hover {
  transform: scale(1.4);
  background-color: #3b82f6;
}

:global(.dark) .resize-handle {
  background-color: #1f2937;
  border-color: #60a5fa;
}

:global(.dark) .resize-handle:hover {
  background-color: #60a5fa;
}
</style>
