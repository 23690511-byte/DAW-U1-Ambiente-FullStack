<script setup>
import { RouterView } from 'vue-router'
import AppHeader from './components/AppHeader.vue'
import { useAuth } from './services/auth'

const { user } = useAuth()
</script>

<template>
  <div class="app-layout">
    <AppHeader v-if="user" />
    <main class="main-content">
      <RouterView />
    </main>
  </div>
</template>

<style>
.app-layout {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.main-content {
  flex: 1;
  padding: 2rem;
  max-width: 1280px;
  width: 100%;
  margin: 0 auto;
  box-sizing: border-box;
}
</style>