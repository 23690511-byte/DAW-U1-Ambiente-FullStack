import { ref } from 'vue'
import { apiFetch } from './http'

const user = ref(null)
const initialized = ref(false)
const authenticated = ref(false)

export function useAuth() {
  async function checkSession() {
    try {
      const response = await apiFetch('/api/session.php')
      if (response && response.ok && (response.user || response.data)) {
        user.value = response.user || response.data
        authenticated.value = true
      } else {
        user.value = null
        authenticated.value = false
      }
    } catch (e) {
      user.value = null
      authenticated.value = false
    } finally {
      initialized.value = true
    }
  }

  async function login(credentials) {
    const response = await apiFetch('/api/login.php', {
      method: 'POST',
      body: JSON.stringify(credentials)
    })
    user.value = response.user || response.data
    authenticated.value = true
    return response
  }

  async function logout() {
    try {
      await apiFetch('/api/logout.php', { method: 'POST' })
    } finally {
      user.value = null
      authenticated.value = false
    }
  }

  return {
    user,
    initialized,
    authenticated,
    checkSession,
    login,
    logout
  }
}
