// Pinia store для нотаток
import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import { notesService } from '../services/notes.service'
import type { Note, CreateData, UpdateData } from '../types'

export const useNotesStore = defineStore('notes', () => {
  // State
  const notes = ref<Note[]>([])
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  // Getters
  const allNotes = computed(() => notes.value)
  
  const importantNotes = computed(() => 
    notes.value.filter(note => note.is_important)
  )

  const getNoteById = computed(() => (id: number) => 
    notes.value.find(note => note.id === id)
  )

  // Actions
  async function fetchNotes(userId?: number) {
    try {
      isLoading.value = true
      error.value = null
      notes.value = await notesService.getAll(userId)
    } catch (err: any) {
      error.value = err.message || 'Помилка завантаження нотаток'
      console.error('Fetch notes error:', err)
    } finally {
      isLoading.value = false
    }
  }

  async function fetchNoteById(id: number) {
    try {
      isLoading.value = true
      error.value = null
      const note = await notesService.getById(id)
      if (note) {
        // Оновлюємо або додаємо нотатку в список
        const index = notes.value.findIndex(n => n.id === id)
        if (index !== -1) {
          notes.value[index] = note
        } else {
          notes.value.push(note)
        }
      }
      return note
    } catch (err: any) {
      error.value = err.message || 'Помилка завантаження нотатки'
      console.error('Fetch note error:', err)
      return null
    } finally {
      isLoading.value = false
    }
  }

  async function createNote(data: CreateData<Note>) {
    try {
      isLoading.value = true
      error.value = null
      const newNote = await notesService.create(data)
      notes.value.push(newNote)
      return { success: true, note: newNote }
    } catch (err: any) {
      error.value = err.message || 'Помилка створення нотатки'
      console.error('Create note error:', err)
      return { success: false, error: error.value }
    } finally {
      isLoading.value = false
    }
  }

  async function updateNote(id: number, data: UpdateData<Note>) {
    try {
      isLoading.value = true
      error.value = null
      const updatedNote = await notesService.update(id, data)
      if (updatedNote) {
        const index = notes.value.findIndex(n => n.id === id)
        if (index !== -1) {
          notes.value[index] = updatedNote
        }
        return { success: true, note: updatedNote }
      }
      return { success: false, error: 'Нотатку не знайдено' }
    } catch (err: any) {
      error.value = err.message || 'Помилка оновлення нотатки'
      console.error('Update note error:', err)
      return { success: false, error: error.value }
    } finally {
      isLoading.value = false
    }
  }

  async function deleteNote(id: number) {
    try {
      isLoading.value = true
      error.value = null
      const success = await notesService.delete(id)
      if (success) {
        notes.value = notes.value.filter(note => note.id !== id)
      }
      return { success }
    } catch (err: any) {
      error.value = err.message || 'Помилка видалення нотатки'
      console.error('Delete note error:', err)
      return { success: false, error: error.value }
    } finally {
      isLoading.value = false
    }
  }

  async function searchNotes(userId: number, query: string) {
    try {
      isLoading.value = true
      error.value = null
      const results = await notesService.search(userId, query)
      return { success: true, notes: results }
    } catch (err: any) {
      error.value = err.message || 'Помилка пошуку'
      console.error('Search notes error:', err)
      return { success: false, error: error.value, notes: [] }
    } finally {
      isLoading.value = false
    }
  }

  return {
    // State
    notes,
    isLoading,
    error,
    
    // Getters
    allNotes,
    importantNotes,
    getNoteById,
    
    // Actions
    fetchNotes,
    fetchNoteById,
    createNote,
    updateNote,
    deleteNote,
    searchNotes
  }
})
