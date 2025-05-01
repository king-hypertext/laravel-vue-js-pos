import axios from 'axios'

// Create an instance of axios
axios.defaults.withCredentials = true
axios.defaults.withXSRFToken = true

const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL ?? 'http://localhost:8000/api',
  headers: {
    'Content-Type': 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
  },
  withCredentials: true,
});


api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('Q_POS_TOKEN')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => {
    Promise.reject(error)
  },
)

// Remove token from header for unauthenticated requests

api.interceptors.response.use(
  (response) => response,
  (error) => {
    const { response } = error;
    if (response.status === 401) {
      localStorage.removeItem('Q_POS_TOKEN');
      console.error('Authentication failed, please login again!');
      window.location.href = '/';
    }
    return Promise.reject(error)
  },
)

export default api
