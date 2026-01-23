<!-- Приклад використання сервісів та store -->
<template>
  <div class="notes-example">
    <h2>Приклад роботи з нотатками</h2>
    
    <div v-if="notesStore.isLoading">Завантаження...</div>
    <div v-if="notesStore.error" class="error">{{ notesStore.error }}</div>
    
    <!-- Форма створення нотатки -->
    <form @submit.prevent="handleCreateNote" class="note-form">
      <input 
        v-model="newNote.title" 
        placeholder="Назва нотатки" 
        required 
      />
      <textarea 
        v-model="newNote.content" 
        placeholder="Вміст нотатки" 
        required
      ></textarea>
      <label>
        <input 
          type="checkbox" 
          v-model="newNote.is_important" 
        />
        Важлива
      </label>
      <button type="submit">Створити нотатку</button>
    </form>
    
    <!-- Список нотаток -->
    <div class="notes-list">
      <h3>Всі нотатки ({{ notesStore.allNotes.length }})</h3>
      <div 
        v-for="note in notesStore.allNotes" 
        :key="note.id" 
        class="note-item"
      >
        <h4>{{ note.title }}</h4>
        <p>{{ note.content }}</p>
        <span v-if="note.is_important" class="important">⭐ Важлива</span>
        <div class="note-actions">
          <button @click="handleEditNote(note)">Редагувати</button>
          <button @click="handleDeleteNote(note.id)">Видалити</button>
        </div>
      </div>
    </div>
    
    <!-- Важливі нотатки -->
    <div class="important-notes">
      <h3>Важливі нотатки ({{ notesStore.importantNotes.length }})</h3>
      <div 
        v-for="note in notesStore.importantNotes" 
        :key="note.id"
        class="note-item important"
      >
        <h4>{{ note.title }}</h4>
        <p>{{ note.content }}</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useNotesStore } from '../stores/useNotesStore'
import { useAuthStore } from '../stores/useAuthStore'
import type { CreateData } from '../types'
import type { Note } from '../types'

const notesStore = useNotesStore()
const authStore = useAuthStore()

const newNote = ref<CreateData<Note>>({
  user_id: 1, // В реальному додатку брати з authStore.getUser?.id
  title: '',
  content: '',
  is_important: false
})

onMounted(async () => {
  // Завантажуємо нотатки при монтуванні компонента
  const userId = authStore.getUser?.id || 1
  await notesStore.fetchNotes(userId)
})

const handleCreateNote = async () => {
  const result = await notesStore.createNote(newNote.value)
  if (result.success) {
    // Очищаємо форму
    newNote.value = {
      user_id: 1,
      title: '',
      content: '',
      is_important: false
    }
    alert('Нотатку створено!')
  } else {
    alert('Помилка: ' + result.error)
  }
}

const handleEditNote = async (note: Note) => {
  const newTitle = prompt('Нова назва:', note.title)
  if (newTitle && newTitle !== note.title) {
    await notesStore.updateNote(note.id, { title: newTitle })
  }
}

const handleDeleteNote = async (id: number) => {
  if (confirm('Ви впевнені, що хочете видалити цю нотатку?')) {
    const result = await notesStore.deleteNote(id)
    if (result.success) {
      alert('Нотатку видалено!')
    }
  }
}
</script>

<style scoped>
.notes-example {
  padding: 2rem;
  max-width: 800px;
  margin: 0 auto;
}

.note-form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  margin-bottom: 2rem;
  padding: 1rem;
  border: 1px solid #ddd;
  border-radius: 8px;
}

.note-form input,
.note-form textarea {
  padding: 0.5rem;
  border: 1px solid #ddd;
  border-radius: 4px;
}

.note-form textarea {
  min-height: 100px;
  resize: vertical;
}

.notes-list,
.important-notes {
  margin-top: 2rem;
}

.note-item {
  padding: 1rem;
  margin-bottom: 1rem;
  border: 1px solid #ddd;
  border-radius: 8px;
  background: #f9f9f9;
}

.note-item.important {
  border-color: #f59e0b;
  background: #fef3c7;
}

.note-actions {
  margin-top: 0.5rem;
  display: flex;
  gap: 0.5rem;
}

.note-actions button {
  padding: 0.25rem 0.5rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  cursor: pointer;
}

.error {
  color: red;
  padding: 1rem;
  background: #fee;
  border-radius: 4px;
  margin-bottom: 1rem;
}
</style>
