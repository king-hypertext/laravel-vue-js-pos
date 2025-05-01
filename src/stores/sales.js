import Alert from '@/utils/Notify';
import { defineStore } from 'pinia';
import { isSameDay, isAfter, subDays, subMonths, subWeeks, parseISO, formatDate, eachDayOfInterval, startOfWeek, addDays, format, isWithinInterval, isEqual } from 'date-fns';
import { useProductsStore } from './products';
import api from '@/config/axios-config';
import { useCartStore } from './cart';
export const useSaleStore = defineStore('sales', {
    state: () => ({
        sales: [],
        isLoading: false,
        error: null,
        isCheckoutLoading: false
    }),
    getters: {
        allSales: (state) => state.sales,

        lastWeekSaleItemsCount: (state) => {
            const oneWeekAgo = subWeeks(new Date(), 1); // Date one week ago
            return state.sales.filter(sale => isAfter(parseISO(sale.created_at), oneWeekAgo)) // Filter sales by date
                .reduce((total, sale) => total + sale.sale_items_count, 0); // Sum up sale_items_count
        },

        todaySales: (state) => {
            const today = new Date().toDateString();
            return state.sales.filter(sale => isSameDay(parseISO(sale.created_at), today));
        },

        todaySaleItemsCount: (state) => {
            const today = new Date().toDateString(); // Get today's date as a string
            return state.sales
                .filter(sale => new Date(sale.created_at).toDateString() === today) // Filter sales for today's date
                .reduce((total, sale) => total + sale.sale_items_count, 0); // Sum up sale_items_count
        },
        salesByDateRange: (state) => (startDate, endDate) => {
            if (!state.sales || state.sales.length === 0) {
                return {};
            }

            const filteredSales = state.sales.filter((sale) => {
                if (sale.sale_date) {
                    const saleDate = parseISO(sale.sale_date);
                    const start = startDate ? parseISO(startDate) : null;
                    const end = endDate ? parseISO(endDate) : null;

                    const isAfterOrEqualStart = !start || saleDate >= start;
                    const isBeforeOrEqualEnd = !end || saleDate <= end;

                    return isAfterOrEqualStart && isBeforeOrEqualEnd;
                }
                return false;
            });

            return filteredSales.reduce((groupedSales, sale) => {
                const dateKey = sale.sale_date;
                if (!groupedSales[dateKey]) {
                    groupedSales[dateKey] = [];
                }
                groupedSales[dateKey].push(sale);
                return groupedSales;
            }, {});
        },

        // Example: Total revenue within a specific date range
        revenueByDateRange: (state) => (startDate, endDate) => {
            const salesInDateRange = Object.values(state.sales.filter((sale) => {
                if (sale.sale_date) {
                    const saleDate = parseISO(sale.sale_date);
                    const start = startDate ? parseISO(startDate) : null;
                    const end = endDate ? parseISO(endDate) : null;

                    const isAfterOrEqualStart = !start || saleDate >= start;
                    const isBeforeOrEqualEnd = !end || saleDate <= end;

                    return isAfterOrEqualStart && isBeforeOrEqualEnd;
                }
                return false;
            }));
            return salesInDateRange.reduce((sum, sale) => sum + sale.total_amount, 0);
        },

        filterSales: (state) => (startDate, endDate, username) => {
            // Return an empty array if there are no sales
            if (!state.sales || state.sales.length === 0) {
                return [];
            }
            return state.sales.filter((sale) => {
                const saleDate = new Date(sale.sale_date);
                const start = new Date(startDate);
                const end = new Date(endDate);
                end.setDate(end.getDate() + 1); // Include the end date

                const dateCondition = saleDate >= start && saleDate < end;
                const userCondition = username ? sale.user?.username === username : true;
                
                return dateCondition && userCondition;
            });
        },

        // Get sales made in the last week using sale_items
        lastWeekSales: (state) => {
            const daysOfWeek = eachDayOfInterval({
                start: startOfWeek(new Date(), { weekStartsOn: 1 }), // Monday
                end: addDays(startOfWeek(new Date(), { weekStartsOn: 1 }), 6), // Sunday
            }).map(date => format(date, 'EEEE'));

            // Initialize groupedData with default values for all days of the week
            const groupedData = daysOfWeek.reduce((acc, day) => {
                acc[day] = { sales_items_count: 0, total_amount: 0 };
                return acc;
            }, {});

            // Aggregate sales data into groupedData
            state.sales.forEach((sale) => {
                const day = format(parseISO(sale.sale_date), 'EEEE');
                if (groupedData[day]) {
                    groupedData[day].sales_items_count += sale.sale_items_count;
                    groupedData[day].total_amount += sale.total_amount;
                }
            });

            return groupedData;
        },

        // Get sales made in the last month using sale_items
        lastMonthSaleItemsCount: (state) => {
            const oneMonthAgo = subMonths(new Date(), 1); // Date one month ago
            return state.sales
                .filter(sale => isAfter(parseISO(sale.created_at), oneMonthAgo)) // Filter sales by date
                .reduce((total, sale) => total + sale.sale_items_count, 0); // Sum up sale_items_count
        },

        lastMonthSales: (state) => {
            const oneMonthAgo = subMonths(new Date().toDateString(), 1); // Date one month ago
            return state.sales.filter((sale) => isAfter(parseISO(sale.created_at), oneMonthAgo));
        },
        // Get total items sold using sale_items
        totalItemsSoldToday: (state) => {
            const today = new Date();
            return state.sales.reduce((total, sale) => {
                return total + sale.sale_items.reduce((itemTotal, item) => {
                    return isSameDay(new Date(item.date), today) ? itemTotal + item.quantity : itemTotal;
                }, 0);
            }, 0);
        },
        salesViaCash: (state) => state.sales.filter(sale => sale.payment_method === 'cash'),
        salesViaMomo: (state) => state.sales.filter(sale => sale.payment_method === 'momo'),

        totalSalesAmount: (state) => {
            return state.sales.reduce((total, sale) => total + sale.total_amount, 0.00);
        },

        totalSaleItemsCount: (state) => {
            return state.sales.reduce((total, sale) => total + sale.sale_items_count, 0); // Sum up sale_items_count
        },
        // Get total sales amount by cash
        totalSalesByCash: (state) => {
            return state.sales
                .filter(sale => sale.payment_method === 'cash')
                .reduce((total, sale) => total + sale.total_amount, 0);
        },

        // Get total sales amount by momo
        totalSalesByMomo: (state) => {
            return state.sales
                .filter(sale => sale.payment_method === 'momo')
                .reduce((total, sale) => total + sale.total_amount, 0);
        },

        // Get total sales amount today
        totalSalesAmountToday: (state) => {
            const today = new Date().toDateString();
            return state.sales.filter(sale => isSameDay(new Date(sale.created_at), today)).reduce((total, sale) => total + sale.total_amount, 0);
        },

        // Get total sales amount last week
        totalSalesLastWeek: (state) => {
            const oneWeekAgo = subDays(new Date(), 7);
            return state.sales
                .filter(sale =>
                    sale.sale_items.some(item => isAfter(new Date(item.date), oneWeekAgo))
                )
                .reduce((total, sale) => total + sale.total_amount, 0);
        },

        // Get total sales amount last month
        totalSalesLastMonth: (state) => {
            const oneMonthAgo = subMonths(new Date(), 1);
            return state.sales
                .filter(sale =>
                    sale.sale_items.some(item => isAfter(new Date(item.date), oneMonthAgo))
                )
                .reduce((total, sale) => total + sale.total_amount, 0);
        },
    },
    actions: {
        async fetchSales() {
            this.isLoading = true;
            this.error = null;
            try {
                const response = await api.get('/sales');
                // Ensure consistent data access
                this.sales = response.data.data || response.data;
                // console.table('Sales data fetched:', this.sales); // Keep the console log, but after data assignment
            } catch (error) {
                // Handle errors more robustly
                this.error = error.response?.data?.message || error.message || 'Failed to fetch sales.';
                console.error('Error fetching sales:', error); // Keep the console.error
                // Optionally, re-throw the error if you want the component to handle it as well
                // throw error; 
            } finally {
                this.isLoading = false;
            }
        },
        // Action for adding a new product
        async addSale(sale = {}) {
            this.isCheckoutLoading = true;
            this.error = null;

            try {
                const response = await api.post('/sales', sale);

                if (![200, 201].includes(response.status)) {
                    Alert.error('An unexpected error occurred during checkout.');
                    return; // Exit the function if the status is not success
                }

                const newSale = response.data.sale; // Adjust based on your API response structure
                this.sales.unshift(newSale);

                const productsStore = useProductsStore();
                newSale.sale_items.forEach((item) => {
                    const product = productsStore.products.find(p => p.id === item.product_id);
                    productsStore.products.find(p => p.id === item.product_id); // Locate the product 
                    if (product) {
                        product.quantity -= item.quantity; // Reduce the product quantity
                    }
                });
                const cartStore = useCartStore();
                cartStore.clearCart();
                Alert.success('Checkout successful');
                return newSale; // Optionally return the new sale data
            } catch (error) {
                console.error('Error during checkout:', error);
                this.error = error.response?.data?.errors || 'Failed to complete checkout.';
                Alert.error(this.error);
                throw error; // Re-throw for component-level handling if needed
            } finally {
                this.isCheckoutLoading = false;
            }
        },

        async updateProduct(productId, productData) {
            this.isLoading = true;
            this.error = null;
            try {
                const response = await api.put(`/products/${productId}`, productData);
                const updatedProduct = response.data.data || response.data;
                const index = this.productList.findIndex(product => product.id === productId);
                if (index !== -1) {
                    this.productList[index] = updatedProduct;
                }
            } catch (error) {
                console.error('Error updating product:', error);
                this.error = error.message || 'Failed to update product.';
                throw error;
            } finally {
                this.isLoading = false;
            }
        },

        async deleteProduct(productId) {
            this.isLoading = true;
            this.error = null;
            try {
                await api.delete(`/products/${productId}`);
                this.productList = this.productList.filter(product => product.id !== productId);
            } catch (error) {
                console.error('Error deleting product:', error);
                this.error = error.message || 'Failed to delete product.';
                throw error;
            } finally {
                this.isLoading = false;
            }
        },
        updateProductQuantity(productId, newQuantity) {
            const product = this.productList.find(p => p.id === productId);
            if (product) {
                product.quantity = newQuantity;
            }
        },
        sortProducts(sortBy, sortDirection = 'asc') {
            this.productList.sort((a, b) => {
                const valueA = a[sortBy];
                const valueB = b[sortBy];

                if (typeof valueA === 'string' && typeof valueB === 'string') {
                    const comparison = valueA.localeCompare(valueB);
                    return sortDirection === 'asc' ? comparison : comparison * -1;
                } else if (typeof valueA === 'number' && typeof valueB === 'number') {
                    return sortDirection === 'asc' ? valueA - valueB : valueB - valueA;
                }
                return 0;
            });
        },
        filterProductsBy(filterBy, filterValue) {
            this.productList = this.productList.filter(product =>
                String(product[filterBy]).toLowerCase().includes(String(filterValue).toLowerCase())
            );
        },
        resetProductList(originalList) {
            this.productList = [...originalList];
        },
    },
});
