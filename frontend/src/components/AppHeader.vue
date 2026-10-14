<script setup>
import { useAuth } from '../services/auth'

const { user } = useAuth()
</script>

<template>
  <header class="app-header">
    <div class="header-container">
      <div class="header-brand">
        <span class="brand-badge">V</span>
        <div class="brand-text">
          <span class="brand-title">Aplicación Full Stack</span>
          <span class="brand-subtitle">Vue + PHP</span>
        </div>
      </div>

      <nav class="header-nav">
        <RouterLink to="/dashboard" class="nav-link">Dashboard</RouterLink>
        <RouterLink to="/servicios" class="nav-link">Servicios</RouterLink>
      </nav>

      <div class="header-user">
        <span class="user-name">{{ user?.nombre }}</span>
      </div>
    </div>
  </header>
</template>