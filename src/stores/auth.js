import api from '@/config/axios-config';
import { defineStore } from 'pinia';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: JSON.parse(localStorage.getItem('Q_AUTH_USER')) || null,
        _token: localStorage.getItem('Q_POS_TOKEN') || null,
        errors: null,
        isLoading: false,
        isAddingUser: false,
        isUpdating: false,
        employees: [],
    }),

    getters: {
        isAuthenticated: (state) => !!state._token,
        getUser: (state) => state.user,
        getEmployees: state => state.employees,
    },

    actions: {
        async getAuthUser() {
            this.isLoading = true;
            this.errors = null;
            try {
                const response = await api.get('/user');
                this.user = response.data.user;
            } catch (error) {
                this.errors = error.response.data?.errors || 'Login failed. Please check your credentials.';
                // Optionally log the error for debugging
                console.error('Login error:', error);
                throw error; // Re-throw the error to be caught by the component
            } finally {
                this.isLoading = false;
            }
        },
        async login(credentials) {
            this.isLoading = true;
            this.errors = null;
            try {
                const response = await api.post('/login', credentials);
                this.user = response.data.user;
                this._token = response.data._token;
                localStorage.setItem('Q_AUTH_USER', JSON.stringify(this.user));
                localStorage.setItem('Q_POS_TOKEN', this._token);
            } catch (error) {
                this.errors = error.response.data?.errors || 'Login failed. Please check your credentials.';
                // Optionally log the error for debugging
                console.error('Login error:', error);
                throw error; // Re-throw the error to be caught by the component
            } finally {
                this.isLoading = false;
            }
        },

        async logout() {
            this.isLoading = true;
            this.errors = null;
            try {
                // You might want to call an API endpoint for server-side logout
                await api.post('/logout');
                this.user = null;
                this._token = null;
                localStorage.removeItem('Q_AUTH_USER');
                localStorage.removeItem('Q_POS_TOKEN');
            } catch (error) {
                this.errors = error.response?.data?.message || 'Logout failed.';
                console.error('Logout error:', error);
                throw error; // Re-throw the error
            } finally {
                this.isLoading = false;
            }
        },

        async fetchUsers() {
            this.isLoading = true;
            this.errors = null;
            try {
                const response = await api.get('/users');
                // console.log(response.data.data); 
                this.employees = response.data.data || [];
            } catch (error) {
                this.errors = error.response?.data?.errors || 'Failed to update user information.';
                console.error('Update user error:', error);
                throw error; // Re-throw the error
            } finally {
                this.isLoading = false;
            }
        },

        async addUser(user = {}) {
            this.isAddingUser = true;
            this.errors = null;
            try {
                const response = await api.post('/users', user);
                this.employees.unshift(response.data.user);
            } catch (error) {
                this.errors = error.response?.data?.errors;
                throw error
            } finally {
                this.isAddingUser = false;
            }
        },
        async updateUser(userData) {
            if (!this._token) {
                this.errors = 'Unauthorized. Please log in.';
                return;
            }
            this.isUpdating = true;
            this.errors = null;
            try {
                const response = await api.put('/user', userData);
                this.user = response.data.user;
                localStorage.setItem('Q_AUTH_USER', JSON.stringify(this.user));
            } catch (error) {
                this.errors = error.response.data?.errors || 'Failed to update user information.';
                console.error('Update user error:', error);
                throw error; // Re-throw the error
            } finally {
                this.isUpdating = false;
            }
        },
    },
});