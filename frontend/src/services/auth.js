import { ref, readonly, computed } from 'vue'

const user = ref(null)
const initialized = ref(false)

const authenticated = computed(() => user.value !== null)

async function checkSession() {
  try {
    const response = await fetch('/api/check_session.php', {
      credentials: 'include',
      headers: {
        'Accept': 'application/json'
      }
    })
    
    if (response.ok) {
      const data = await response.json()
      if (data.authenticated) {
        user.value = data.user
      } else {
        user.value = null
      }
    } else {
      user.value = null
    }
  } catch (err) {
    user.value = null
  } finally {
    initialized.value = true
  }
}

async function login(credentials) {
  const response = await fetch('/api/login.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    },
    credentials: 'include',
    body: JSON.stringify(credentials)
  })

  const data = await response.json()

  if (!response.ok || !data.ok) {
    throw new Error(data.message || 'Error al iniciar sesión')
  }

  user.value = data.user
  return data
}

async function logout() {
  try {
    await fetch('/api/logout.php', {
      method: 'POST',
      credentials: 'include',
      headers: {
        'Accept': 'application/json'
      }
    })
  } finally {
    user.value = null
    initialized.value = true
  }
}

export function useAuth() {
  return {
    user: readonly(user),
    initialized: readonly(initialized),
    authenticated,
    login,
    checkSession,
    logout
  }
}