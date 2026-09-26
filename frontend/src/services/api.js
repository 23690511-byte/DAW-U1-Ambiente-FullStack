const API_URL = 'http://localhost:8080/api'

export async function obtenerServicios() {
  const respuesta = await fetch(`${API_URL}/servicios.php`, {
    headers: {
      Accept: 'application/json'
    }
  })

  if (!respuesta.ok) {
    throw new Error(`Error HTTP ${respuesta.status}`)
  }

  const contentType = respuesta.headers.get('content-type') || ''
  if (!contentType.includes('application/json')) {
    const contenido = await respuesta.text()
    console.error('Respuesta recibida:', contenido)
    throw new Error('El servidor no devolvió JSON')
  }

  return respuesta.json()
}