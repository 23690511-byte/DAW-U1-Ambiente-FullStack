<script setup>
import { useAuth } from '../services/auth'
const { user } = useAuth()
</script>

<template>
  <section class="dashboard">
    <div class="dashboard-hero">
      <div>
        <span class="badge">Panel principal</span>
        <h1>Bienvenido, {{ user?.nombre }}</h1>
        <p>Desde este espacio puedes consultar y gestionar los módulos disponibles de la aplicación.</p>
      </div>
      <div class="hero-icon">🚀</div>
    </div>

    <div class="dashboard-grid">
      <article class="dashboard-card card-blue">
        <span class="card-icon">⚙️</span>
        <h2>Servicios</h2>
        <p>Consulta el catálogo de servicios disponibles.</p>
        <RouterLink to="/servicios" class="card-link">Ver catálogo →</RouterLink>
      </article>

      <article
        v-if="user?.rol === 'ADMIN_SISTEMA' || user?.rol === 'ADMIN_SERVICIOS'"
        class="dashboard-card card-orange"
      >
        <span class="card-icon">🛠️</span>
        <h2>Gestión de Servicios</h2>
        <p>Administra los servicios, precios, duración y estado.</p>
        <RouterLink to="/servicios/admin" class="card-link">Administrar servicios →</RouterLink>
      </article>

      <article
        v-if="user?.rol === 'ADMIN_SISTEMA'"
        class="dashboard-card card-blue"
      >
        <span class="card-icon">👥</span>
        <h2>Usuarios</h2>
        <p>Administra las cuentas de usuario, roles y estados.</p>
        <RouterLink to="/usuarios" class="card-link">Administrar usuarios →</RouterLink>
      </article>

      <article class="dashboard-card card-green">
        <span class="card-icon">👤</span>
        <h2>Mi Perfil</h2>
        <p>{{ user?.usuario }} ({{ user?.email }})</p>
        <strong>Rol: {{ user?.rol }}</strong>
      </article>
    </div>
  </section>
</template>
