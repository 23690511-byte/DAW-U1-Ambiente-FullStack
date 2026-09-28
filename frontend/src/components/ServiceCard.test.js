import { mount } from '@vue/test-utils'
import { describe, expect, it } from 'vitest'
import ServiceCard from './ServiceCard.vue'

describe('ServiceCard', () => {
  it('muestra el nombre del servicio', () => {
    const servicio = {
      id: 1,
      nombre: 'Desarrollo Web',
      descripcion: 'Aplicaciones web',
      precio: 8500
    }

    const wrapper = mount(ServiceCard, {
      props: {
        servicio
      }
    })

    expect(wrapper.text()).toContain('Desarrollo Web')
  })
})