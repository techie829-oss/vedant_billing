import { create } from 'zustand';
import AsyncStorage from '@react-native-async-storage/async-storage';
import apiClient from '../api/client';

interface User {
    id: number;
    name: string;
    email: string;
}

interface AuthState {
    isAuthenticated: boolean;
    user: User | null;
    token: string | null;
    isLoading: boolean;
    error: string | null;
    login: (email: string, password: string) => Promise<void>;
    logout: () => Promise<void>;
    restoreSession: () => Promise<void>;
}

export const useAuthStore = create<AuthState>((set) => ({
    isAuthenticated: false,
    user: null,
    token: null,
    isLoading: false,
    error: null,

    restoreSession: async () => {
        const token = await AsyncStorage.getItem('@auth_token');
        const userJson = await AsyncStorage.getItem('@auth_user');
        if (token && userJson) {
            const user = JSON.parse(userJson);
            set({ isAuthenticated: true, token, user });
        }
    },

    login: async (email: string, password: string) => {
        set({ isLoading: true, error: null });
        try {
            const response = await apiClient.post('/login', { email, password });
            const { token, user } = response.data;

            await AsyncStorage.setItem('@auth_token', token);
            await AsyncStorage.setItem('@auth_user', JSON.stringify(user));

            set({ isAuthenticated: true, user, token, isLoading: false });
        } catch (err: any) {
            const message =
                err?.response?.data?.message || 'Login failed. Please check your credentials.';
            set({ isLoading: false, error: message });
            throw new Error(message);
        }
    },

    logout: async () => {
        try {
            await apiClient.post('/logout');
        } catch (_) {
            // Ignore logout errors
        } finally {
            await AsyncStorage.removeItem('@auth_token');
            await AsyncStorage.removeItem('@auth_user');
            set({ isAuthenticated: false, user: null, token: null });
        }
    },
}));
