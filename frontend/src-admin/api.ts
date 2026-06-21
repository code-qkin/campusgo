const API_BASE = 'http://127.0.0.1:8000/api'

const getToken = () => localStorage.getItem('admin_token')

export const adminApi = {
  get: async (endpoint: string) => {
    const res = await fetch(`${API_BASE}${endpoint}`, {
      headers: {
        'Authorization': `Bearer ${getToken()}`,
        'Accept': 'application/json',
      }
    })
    const data = await res.json()
    if (!res.ok) throw new Error(data.message || 'Request failed')
    return data
  },

  post: async (endpoint: string, body: any) => {
    const res = await fetch(`${API_BASE}${endpoint}`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${getToken()}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: JSON.stringify(body)
    })
    const data = await res.json()
    if (!res.ok) throw new Error(data.message || 'Request failed')
    return data
  },

  patch: async (endpoint: string, body: any = {}) => {
    const res = await fetch(`${API_BASE}${endpoint}`, {
      method: 'PATCH',
      headers: {
        'Authorization': `Bearer ${getToken()}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: JSON.stringify(body)
    })
    const data = await res.json()
    if (!res.ok) throw new Error(data.message || 'Request failed')
    return data
  },

  delete: async (endpoint: string) => {
    const res = await fetch(`${API_BASE}${endpoint}`, {
      method: 'DELETE',
      headers: {
        'Authorization': `Bearer ${getToken()}`,
        'Accept': 'application/json',
      }
    })
    const data = await res.json()
    if (!res.ok) throw new Error(data.message || 'Request failed')
    return data
  }
}