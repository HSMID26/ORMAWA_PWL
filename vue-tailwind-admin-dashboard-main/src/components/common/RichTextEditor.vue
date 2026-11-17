<template>
  <div class="rounded-xl border border-gray-300 bg-white overflow-hidden dark:border-gray-700 dark:bg-gray-900 shadow-2xs">
    <!-- ─── Toolbar ──────────────────────────────────────────────────────────── -->
    <div
      v-if="editor"
      class="flex flex-wrap items-center gap-1 border-b border-gray-200 bg-gray-50/90 px-3 py-2 text-xs dark:border-gray-800 dark:bg-gray-800/80"
    >
      <!-- Media Library Insert Button -->
      <button
        type="button"
        @click="handleOpenPicker"
        class="inline-flex items-center gap-1.5 rounded-lg bg-brand-50 border border-brand-200 px-2.5 py-1.5 font-bold text-brand-700 hover:bg-brand-100 dark:bg-brand-950/40 dark:border-brand-800 dark:text-brand-300 dark:hover:bg-brand-900/50 shadow-2xs transition mr-1"
        title="Sisipkan Gambar dari Media Library"
      >
        <ImageIcon class="h-4 w-4 text-brand-600 dark:text-brand-400" />
        <span>Sisipkan Foto</span>
      </button>

      <span class="h-4 w-px bg-gray-300 dark:bg-gray-700 mx-1"></span>

      <!-- Paragraph & Headings -->
      <button
        type="button"
        @click="editor.chain().focus().setParagraph().run()"
        :class="['rounded px-2 py-1 font-bold transition text-xs', editor.isActive('paragraph') && !editor.isActive('heading') ? 'bg-brand-100 text-brand-700 dark:bg-brand-900/60 dark:text-brand-300' : 'text-gray-700 hover:bg-gray-200 dark:text-gray-300 dark:hover:bg-gray-700']"
        title="Paragraf Biasa"
      >
        P
      </button>
      <button
        type="button"
        @click="editor.chain().focus().toggleHeading({ level: 1 }).run()"
        :class="['rounded px-2 py-1 font-bold transition', editor.isActive('heading', { level: 1 }) ? 'bg-brand-100 text-brand-700 dark:bg-brand-900/60 dark:text-brand-300' : 'text-gray-700 hover:bg-gray-200 dark:text-gray-300 dark:hover:bg-gray-700']"
        title="Heading 1"
      >
        H1
      </button>
      <button
        type="button"
        @click="editor.chain().focus().toggleHeading({ level: 2 }).run()"
        :class="['rounded px-2 py-1 font-bold transition', editor.isActive('heading', { level: 2 }) ? 'bg-brand-100 text-brand-700 dark:bg-brand-900/60 dark:text-brand-300' : 'text-gray-700 hover:bg-gray-200 dark:text-gray-300 dark:hover:bg-gray-700']"
        title="Heading 2"
      >
        H2
      </button>
      <button
        type="button"
        @click="editor.chain().focus().toggleHeading({ level: 3 }).run()"
        :class="['rounded px-2 py-1 font-bold transition', editor.isActive('heading', { level: 3 }) ? 'bg-brand-100 text-brand-700 dark:bg-brand-900/60 dark:text-brand-300' : 'text-gray-700 hover:bg-gray-200 dark:text-gray-300 dark:hover:bg-gray-700']"
        title="Heading 3"
      >
        H3
      </button>

      <span class="h-4 w-px bg-gray-300 dark:bg-gray-700 mx-1"></span>

      <!-- Formatting -->
      <button
        type="button"
        @click="editor.chain().focus().toggleBold().run()"
        :class="['rounded p-1.5 transition', editor.isActive('bold') ? 'bg-brand-100 text-brand-700 dark:bg-brand-900/60 dark:text-brand-300' : 'text-gray-700 hover:bg-gray-200 dark:text-gray-300 dark:hover:bg-gray-700']"
        title="Tebal (Ctrl+B)"
      >
        <BoldIcon class="h-4 w-4" />
      </button>
      <button
        type="button"
        @click="editor.chain().focus().toggleItalic().run()"
        :class="['rounded p-1.5 transition', editor.isActive('italic') ? 'bg-brand-100 text-brand-700 dark:bg-brand-900/60 dark:text-brand-300' : 'text-gray-700 hover:bg-gray-200 dark:text-gray-300 dark:hover:bg-gray-700']"
        title="Miring (Ctrl+I)"
      >
        <ItalicIcon class="h-4 w-4" />
      </button>
      <button
        type="button"
        @click="editor.chain().focus().toggleUnderline().run()"
        :class="['rounded p-1.5 transition', editor.isActive('underline') ? 'bg-brand-100 text-brand-700 dark:bg-brand-900/60 dark:text-brand-300' : 'text-gray-700 hover:bg-gray-200 dark:text-gray-300 dark:hover:bg-gray-700']"
        title="Garis Bawah (Ctrl+U)"
      >
        <UnderlineIcon class="h-4 w-4" />
      </button>
      <button
        type="button"
        @click="editor.chain().focus().toggleStrike().run()"
        :class="['rounded p-1.5 transition', editor.isActive('strike') ? 'bg-brand-100 text-brand-700 dark:bg-brand-900/60 dark:text-brand-300' : 'text-gray-700 hover:bg-gray-200 dark:text-gray-300 dark:hover:bg-gray-700']"
        title="Coret (Strikethrough)"
      >
        <StrikethroughIcon class="h-4 w-4" />
      </button>

      <span class="h-4 w-px bg-gray-300 dark:bg-gray-700 mx-1"></span>

      <!-- Alignment -->
      <button
        type="button"
        @click="editor.chain().focus().setTextAlign('left').run()"
        :class="['rounded p-1.5 transition', editor.isActive({ textAlign: 'left' }) ? 'bg-brand-100 text-brand-700 dark:bg-brand-900/60 dark:text-brand-300' : 'text-gray-700 hover:bg-gray-200 dark:text-gray-300 dark:hover:bg-gray-700']"
        title="Rata Kiri"
      >
        <AlignLeftIcon class="h-4 w-4" />
      </button>
      <button
        type="button"
        @click="editor.chain().focus().setTextAlign('center').run()"
        :class="['rounded p-1.5 transition', editor.isActive({ textAlign: 'center' }) ? 'bg-brand-100 text-brand-700 dark:bg-brand-900/60 dark:text-brand-300' : 'text-gray-700 hover:bg-gray-200 dark:text-gray-300 dark:hover:bg-gray-700']"
        title="Rata Tengah"
      >
        <AlignCenterIcon class="h-4 w-4" />
      </button>
      <button
        type="button"
        @click="editor.chain().focus().setTextAlign('right').run()"
        :class="['rounded p-1.5 transition', editor.isActive({ textAlign: 'right' }) ? 'bg-brand-100 text-brand-700 dark:bg-brand-900/60 dark:text-brand-300' : 'text-gray-700 hover:bg-gray-200 dark:text-gray-300 dark:hover:bg-gray-700']"
        title="Rata Kanan"
      >
        <AlignRightIcon class="h-4 w-4" />
      </button>
      <button
        type="button"
        @click="editor.chain().focus().setTextAlign('justify').run()"
        :class="['rounded p-1.5 transition', editor.isActive({ textAlign: 'justify' }) ? 'bg-brand-100 text-brand-700 dark:bg-brand-900/60 dark:text-brand-300' : 'text-gray-700 hover:bg-gray-200 dark:text-gray-300 dark:hover:bg-gray-700']"
        title="Rata Kiri Kanan (Justify)"
      >
        <AlignJustifyIcon class="h-4 w-4" />
      </button>

      <span class="h-4 w-px bg-gray-300 dark:bg-gray-700 mx-1"></span>

      <!-- Lists -->
      <button
        type="button"
        @click="editor.chain().focus().toggleBulletList().run()"
        :class="['rounded p-1.5 transition', editor.isActive('bulletList') ? 'bg-brand-100 text-brand-700 dark:bg-brand-900/60 dark:text-brand-300' : 'text-gray-700 hover:bg-gray-200 dark:text-gray-300 dark:hover:bg-gray-700']"
        title="Daftar Poin (Bullet List)"
      >
        <ListIcon class="h-4 w-4" />
      </button>
      <button
        type="button"
        @click="editor.chain().focus().toggleOrderedList().run()"
        :class="['rounded p-1.5 transition', editor.isActive('orderedList') ? 'bg-brand-100 text-brand-700 dark:bg-brand-900/60 dark:text-brand-300' : 'text-gray-700 hover:bg-gray-200 dark:text-gray-300 dark:hover:bg-gray-700']"
        title="Daftar Angka (Numbered List)"
      >
        <ListOrderedIcon class="h-4 w-4" />
      </button>

      <span class="h-4 w-px bg-gray-300 dark:bg-gray-700 mx-1"></span>

      <!-- Blockquote, Code, Link, HR -->
      <button
        type="button"
        @click="editor.chain().focus().toggleBlockquote().run()"
        :class="['rounded p-1.5 transition', editor.isActive('blockquote') ? 'bg-brand-100 text-brand-700 dark:bg-brand-900/60 dark:text-brand-300' : 'text-gray-700 hover:bg-gray-200 dark:text-gray-300 dark:hover:bg-gray-700']"
        title="Kutipan (Quote)"
      >
        <QuoteIcon class="h-4 w-4" />
      </button>
      <button
        type="button"
        @click="editor.chain().focus().toggleCodeBlock().run()"
        :class="['rounded p-1.5 transition', editor.isActive('codeBlock') ? 'bg-brand-100 text-brand-700 dark:bg-brand-900/60 dark:text-brand-300' : 'text-gray-700 hover:bg-gray-200 dark:text-gray-300 dark:hover:bg-gray-700']"
        title="Blok Kode"
      >
        <CodeIcon class="h-4 w-4" />
      </button>
      <button
        type="button"
        @click="setLink"
        :class="['rounded p-1.5 transition', editor.isActive('link') ? 'bg-brand-100 text-brand-700 dark:bg-brand-900/60 dark:text-brand-300' : 'text-gray-700 hover:bg-gray-200 dark:text-gray-300 dark:hover:bg-gray-700']"
        title="Sisipkan Tautan (Link)"
      >
        <LinkIcon class="h-4 w-4" />
      </button>
      <button
        type="button"
        @click="editor.chain().focus().setHorizontalRule().run()"
        class="rounded p-1.5 text-gray-700 hover:bg-gray-200 dark:text-gray-300 dark:hover:bg-gray-700"
        title="Garis Pemisah (Horizontal Rule)"
      >
        <MinusIcon class="h-4 w-4" />
      </button>

      <span class="h-4 w-px bg-gray-300 dark:bg-gray-700 mx-1"></span>

      <!-- History -->
      <button
        type="button"
        @click="editor.chain().focus().undo().run()"
        :disabled="!editor.can().undo()"
        class="rounded p-1.5 text-gray-700 hover:bg-gray-200 dark:text-gray-300 dark:hover:bg-gray-700 disabled:opacity-30"
        title="Undo (Ctrl+Z)"
      >
        <Undo2Icon class="h-4 w-4" />
      </button>
      <button
        type="button"
        @click="editor.chain().focus().redo().run()"
        :disabled="!editor.can().redo()"
        class="rounded p-1.5 text-gray-700 hover:bg-gray-200 dark:text-gray-300 dark:hover:bg-gray-700 disabled:opacity-30"
        title="Redo (Ctrl+Y)"
      >
        <Redo2Icon class="h-4 w-4" />
      </button>
    </div>

    <!-- ─── Editor Content ───────────────────────────────────────────────────── -->
    <EditorContent
      :editor="editor"
      class="prose prose-sm dark:prose-invert max-w-none p-4 min-h-[360px] focus:outline-none text-gray-800 dark:text-gray-200"
    />
  </div>
</template>

<script setup lang="ts">
import { watch, onBeforeUnmount } from 'vue'
import { useEditor, EditorContent, VueNodeViewRenderer, mergeAttributes } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'
import Underline from '@tiptap/extension-underline'
import Link from '@tiptap/extension-link'
import Image from '@tiptap/extension-image'
import TextAlign from '@tiptap/extension-text-align'
import ResizableImageNode from './ResizableImageNode.vue'
import {
  BoldIcon,
  ItalicIcon,
  UnderlineIcon,
  StrikethroughIcon,
  AlignLeftIcon,
  AlignCenterIcon,
  AlignRightIcon,
  AlignJustifyIcon,
  ListIcon,
  ListOrderedIcon,
  QuoteIcon,
  CodeIcon,
  LinkIcon,
  MinusIcon,
  Undo2Icon,
  Redo2Icon,
  ImageIcon
} from 'lucide-vue-next'

const props = defineProps<{
  modelValue?: string
  placeholder?: string
}>()

const emit = defineEmits<{
  (e: 'update:modelValue', value: string): void
  (e: 'open-media-picker'): void
}>()

let savedSelection: any = null

/**
 * Custom TipTap Image Node with interactive resizing & persistence
 */
const ResizableImage = Image.extend({
  name: 'image',

  addAttributes() {
    return {
      src: {
        default: null,
      },
      alt: {
        default: null,
      },
      title: {
        default: null,
      },
      width: {
        default: null,
        parseHTML: (element) => {
          const widthAttr = element.getAttribute('width')
          if (widthAttr) {
            const parsed = parseInt(widthAttr, 10)
            if (!isNaN(parsed) && parsed > 0) return parsed
          }
          const styleWidth = element.style.width
          if (styleWidth) {
            const parsed = parseInt(styleWidth, 10)
            if (!isNaN(parsed) && parsed > 0) return parsed
          }
          return null
        },
        renderHTML: (attributes) => {
          if (!attributes.width) return {}
          return {
            width: attributes.width,
            style: `width: ${attributes.width}px; max-width: 100%; height: auto;`
          }
        }
      },
      height: {
        default: null,
        parseHTML: (element) => {
          const heightAttr = element.getAttribute('height')
          if (heightAttr) {
            const parsed = parseInt(heightAttr, 10)
            if (!isNaN(parsed) && parsed > 0) return parsed
          }
          return null
        },
        renderHTML: (attributes) => {
          if (!attributes.height) return {}
          return {
            height: attributes.height
          }
        }
      },
      alignment: {
        default: 'center',
        parseHTML: (element) => element.getAttribute('data-align') || 'center',
        renderHTML: (attributes) => {
          if (!attributes.alignment || attributes.alignment === 'center') {
            return { 'data-align': 'center' }
          }
          return { 'data-align': attributes.alignment }
        }
      }
    }
  },

  renderHTML({ HTMLAttributes }) {
    const { width, style, alignment, ...rest } = HTMLAttributes
    let alignStyle = ''
    if (alignment === 'left') {
      alignStyle = 'margin-left: 0; margin-right: auto;'
    } else if (alignment === 'right') {
      alignStyle = 'margin-left: auto; margin-right: 0;'
    } else {
      alignStyle = 'margin-left: auto; margin-right: auto;'
    }

    const widthStyle = width ? `width: ${width}px;` : ''
    const styleStr = `${widthStyle} max-width: 100%; height: auto; display: block; ${alignStyle}`

    return [
      'img',
      mergeAttributes(this.options.HTMLAttributes, rest, {
        ...(width ? { width } : {}),
        style: styleStr,
        ...(alignment ? { 'data-align': alignment } : {})
      })
    ]
  },

  addNodeView() {
    return VueNodeViewRenderer(ResizableImageNode)
  }
})

const editor = useEditor({
  content: props.modelValue || '',
  extensions: [
    StarterKit.configure({
      heading: {
        levels: [1, 2, 3]
      }
    }),
    Underline,
    Link.configure({
      openOnClick: false,
      HTMLAttributes: {
        class: 'text-brand-600 underline font-medium hover:text-brand-700',
        target: '_blank',
        rel: 'noopener noreferrer'
      }
    }),
    ResizableImage.configure({
      inline: false,
      allowBase64: false,
      HTMLAttributes: {
        class: 'rounded-xl max-h-[650px] object-cover shadow-sm block'
      }
    }),
    TextAlign.configure({
      types: ['heading', 'paragraph']
    })
  ],
  editorProps: {
    attributes: {
      class: 'focus:outline-none min-h-[340px]'
    }
  },
  onUpdate: () => {
    emit('update:modelValue', editor.value?.getHTML() || '')
  }
})

watch(
  () => props.modelValue,
  (val) => {
    if (editor.value && editor.value.getHTML() !== (val || '')) {
      editor.value.commands.setContent(val || '', { emitUpdate: false })
    }
  }
)

const handleOpenPicker = () => {
  if (editor.value) {
    savedSelection = editor.value.state.selection
  }
  emit('open-media-picker')
}

const setLink = () => {
  if (!editor.value) return
  const previousUrl = editor.value.getAttributes('link').href
  const url = window.prompt('Masukkan URL tautan:', previousUrl || 'https://')

  if (url === null) return
  if (url === '') {
    editor.value.chain().focus().extendMarkRange('link').unsetLink().run()
    return
  }

  editor.value.chain().focus().extendMarkRange('link').setLink({ href: url }).run()
}

/**
 * Sisipkan gambar Media Library ke posisi kursor editor
 */
const insertImage = (options: { src: string; alt?: string; title?: string; caption?: string }) => {
  if (!editor.value) return

  if (savedSelection) {
    editor.value.commands.setTextSelection(savedSelection)
  }

  editor.value
    .chain()
    .focus()
    .setImage({
      src: options.src,
      alt: options.alt || 'Foto Dokumentasi',
      title: options.caption || options.title || options.alt || ''
    })
    .run()
}

defineExpose({
  insertImage
})

onBeforeUnmount(() => {
  editor.value?.destroy()
})
</script>

<style>
/* TipTap Prose Custom Styling */
.ProseMirror p.is-editor-empty:first-child::before {
  content: attr(data-placeholder);
  float: left;
  color: #9ca3af;
  pointer-events: none;
  height: 0;
}
.ProseMirror:focus {
  outline: none;
}
.ProseMirror .image-node-view {
  margin: 1rem 0;
}
.ProseMirror .image-node-view[data-align="left"] {
  text-align: left;
}
.ProseMirror .image-node-view[data-align="center"] {
  text-align: center;
}
.ProseMirror .image-node-view[data-align="right"] {
  text-align: right;
}
</style>
