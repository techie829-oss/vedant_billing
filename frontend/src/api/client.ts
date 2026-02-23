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

// Response interceptor to handle 401 Unauthorized
client.interceptors.response.use(
    (response) => {
        return response
    },
    (error) => {
        if (error.response && error.response.status === 401) {
            // Token is invalid/expired. Clear auth data and redirect to login.
            localStorage.removeItem('token')
            localStorage.removeItem('user')
            localStorage.removeItem('activeBusiness')
            localStorage.removeItem('userBusinesses')

            // Only redirect if not already on login/register to avoid loops
            if (!window.location.pathname.startsWith('/login') && !window.location.pathname.startsWith('/register')) {
                window.location.href = '/login'
            }
        }
        return Promise.reject(error)
    }
)

export default client
