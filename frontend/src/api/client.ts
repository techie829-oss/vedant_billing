import axios from 'axios'

const client = axios.create({
    baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000/api',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    }
})

// Request interceptor to add token
client.interceptors.request.use((config) => {
    const token = localStorage.getItem('token')
    if (token) {
        config.headers.Authorization = `Bearer ${token}`
    }

    const activeBusiness = localStorage.getItem('activeBusiness')
    if (activeBusiness) {
        try {
            const business = JSON.parse(activeBusiness)
            if (business && business.id) {
                config.headers['X-Business-ID'] = business.id
            }
        } catch (e) {
            console.error('Failed to parse active business for header', e)
        }
    }

    return config
})

export default client
