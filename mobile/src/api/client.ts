import axios from 'axios';
import AsyncStorage from '@react-native-async-storage/async-storage';

const API_BASE_URL = 'https://vedantbilling.com/api';

const apiClient = axios.create({
    baseURL: API_BASE_URL,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
});

// Request interceptor to attach bearer token
apiClient.interceptors.request.use(
    async (config) => {
        const token = await AsyncStorage.getItem('@auth_token');
        if (token) {
            config.headers.Authorization = `Bearer ${token}`;
        }
        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
);

// Response interceptor to handle 401 Unauthorized globally
apiClient.interceptors.response.use(
    (response) => response,
    async (error) => {
        if (error.response && error.response.status === 401) {
            // Token is invalid/expired. We should clear it and logout.
            // Easiest is to fire an event here or have the auth store listen, 
            // but we will keep it simple and just clear storage for now.
            await AsyncStorage.removeItem('@auth_token');
        }
        return Promise.reject(error);
    }
);

export default apiClient;
