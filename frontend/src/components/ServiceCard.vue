<script setup>
defineProps({
  servicio: {
    type: Object,
    required: true
  }
})

const emit = defineEmits([
  'seleccionar'
])
</script>

<template>
  <article class="service-card">
    <h3>
      {{ servicio.nombre }}
    </h3>
    <p>
      {{ servicio.descripcion }}
    </p>
    <p>
      ${{ servicio.precio }}
    </p>
    <button
      type="button"
      @click="emit('seleccionar', servicio)"
    >
      Ver información
    </button>
  </article>
</template>