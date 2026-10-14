<script setup>
import { ref, onMounted } from 'vue'
import { obtenerServicios } from '../services/api'

const servicios = ref([])
const loading = ref(true)
const error = ref('')

const cargarServicios = async () => {
  try {
    const data = await obtenerServicios()
    servicios.value = data
  } catch (err) {
    error.value = err.message
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  cargarServicios()
})
</script>

<template>
  <section class="services-container">
    <div class="services-header">
      <h1>Catálogo de Servicios</h1>
      <p>Servicios disponibles consultados mediante la API autenticada.</p>
    </div>

    <div v-if="loading" class="loading">
      Cargando servicios...
    </div>

    <div v-else-if="error" class="error-message">
      {{ error }}
    </div>

    <div v-else class="services-grid">
      <article v-for="servicio in servicios" :key="servicio.id" class="service-card">
        <div class="service-icon">🛠️</div>
        <h2>{{ servicio.nombre }}</h2>
        <p>{{ servicio.descripcion }}</p>
        <div class="service-price">${{ servicio.precio }} MXN</div>
      </article>
    </div>
  </section>
</template>