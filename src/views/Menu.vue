<script setup>
import BarChart from '@/components/BarChart.vue';
import LineChart from '@/components/LineChart.vue';
import api from '@/config/axios-config';
import { useProductsStore } from '@/stores/products';
import { useSaleStore } from '@/stores/sales';
import { formatCurrency, toPercentage, truncateText } from '@/utils';
import { faDollar, faShoppingCart, faUsers } from '@fortawesome/free-solid-svg-icons'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { computed, onMounted, ref } from 'vue';

const isFetching = ref(false);
const statData = ref({});
const fetchData = () => {
  isFetching.value = true;
  api.get('/stats').then((response) => {
    // console.log(response.data.data);
    statData.value = response.data.data;
  }).catch((error) => {
    console.log(error);
  }).finally(() => {
    isFetching.value = false;
  })
}
const productsStore = useProductsStore();

// Use computed properties to access data from the store.
const productListComputed = computed(() => productsStore.items);
const outOfStockProducts = computed(() => productsStore.outOfStockProducts); // Assuming these getters exist
const InStockProducts = computed(() => productsStore.inStockProducts);     // Assuming these getters exist
const lowStockProducts = computed(() => productsStore.lowStockProducts);

const InStockProductsAmount = computed(() => productsStore.inStockProductsAmount)
const allProductsAmount = computed(() => productsStore.productsAmount)
const fetchProductData = async () => { // Make fetchData async
  await productsStore.fetchProducts(); // Await the completion of fetchProducts
};

const saleStore = useSaleStore();
const fetchSaleData = async () => { // Make fetchData async
  await saleStore.fetchSales(); // Await the completion of fetchProducts
};

const totalSalesByCash = computed(() => saleStore.totalSalesByCash);
const totalSalesByMomo = computed(() => saleStore.totalSalesByMomo);
const totalSalesAmountToday = computed(() => saleStore.totalSalesAmountToday);
const totalSalesLastWeek = computed(() => saleStore.totalSalesLastWeek);
// Use storeToRefs to destructure getters and keep reactivity
const allSales = computed(() => saleStore.allSales);

const lastWeekSaleItemsCount = computed(() => saleStore.lastWeekSaleItemsCount);
const todaySaleItemsCount = computed(() => saleStore.todaySaleItemsCount);
const totalSalesAmount = computed(() => saleStore.totalSalesAmount);

const lastWeekSales = computed(() => saleStore.lastWeekSales)
onMounted(() => {
  fetchData();
  fetchProductData();
  fetchSaleData();
});

</script>

<template>
  <div class="card shadow-1 vh-100">

    <div class="card-body">
      <div v-if="!isFetching" class="row">
        <div class="col-auto col-sm-12  col-md-6 col-lg-6 col-xl-4 gy-3">
          <div class="card bg-secondary-subtle shadow-2-strong">
            <div class="card-body px-2">
              <div class="d-flex align-items-center">
                <div class="col-sm-3 d-flex justify-content-center">
                  <FontAwesomeIcon :icon="faShoppingCart" size="2x" class="text-primary mr-3" />
                </div>
                <div class="col-sm-9">
                  <h5 class="card-title text-uppercase">all items </h5>
                  <p class="card-text fs-5 fw-semibold">{{ productListComputed?.length }}
                    (<span class="price">{{ formatCurrency(allProductsAmount) }}</span>)
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-auto col-sm-12  col-md-6 col-lg-6 col-xl-4 gy-3">
          <div class="card bg-success-subtle text-success-emphasis">
            <div class="card-body px-2 shadow-2-strong">
              <div class="d-flex align-items-center">
                <div class="col-sm-3 d-flex justify-content-center">
                  <FontAwesomeIcon :icon="faShoppingCart" size="2x" class="text-success-emphasis mr-3" />
                </div>
                <div class="col-sm-9">
                  <h5 class="card-title text-uppercase">available items
                    <!-- <small class="fs-6 text-capitalize d-inline-block">(in stock) (revenue)</small> -->
                  </h5>
                  <p class="card-text fs-5 fw-semibold">{{ InStockProducts?.length }}
                    (<span class="price">{{ formatCurrency(InStockProductsAmount) }}</span>)
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-auto col-sm-12  col-md-6 col-lg-6 col-xl-4 gy-3">
          <div class="card bg-warning-subtle text-warning-emphasis">
            <div class="card-body px-2 shadow-2-strong">
              <div class="d-flex align-items-center">
                <div class="col-sm-3 d-flex justify-content-center">
                  <FontAwesomeIcon :icon="faShoppingCart" size="2x" class="text-warning-emphasis mr-3" />
                </div>
                <div class="col-sm-9">
                  <h5 class="card-title text-uppercase">items-low stock
                    <!-- <small class="fs-6 text-capitalize d-inline-block">(low stock)</small> -->
                  </h5>
                  <p class="card-text fs-5 fw-semibold">{{ lowStockProducts?.length }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-auto col-sm-12  col-md-6 col-lg-6 col-xl-4 gy-3">
          <div class="card bg-danger-subtle text-danger-emphasis shadow-2-strong">
            <div class="card-body px-2">
              <div class="d-flex align-items-center">
                <div class="col-sm-3 d-flex justify-content-center">
                  <FontAwesomeIcon :icon="faShoppingCart" size="2x" class="text-danger mr-3" />
                </div>
                <div class="col-sm-9">
                  <h5 class="card-title text-uppercase">items-out of stock
                    <!-- <small class="fs-6 text-capitalize d-inline-block">(out of stock)</small> -->
                  </h5>
                  <p class="card-text fs-5 fw-semibold">{{ outOfStockProducts?.length }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>


        <div class="col-auto col-sm-12  col-md-6 col-lg-6 col-xl-4 gy-3">
          <div class="card bg-secondary-subtle shadow-2-strong">
            <div class="card-body px-2">
              <div class="d-flex align-items-center">
                <div class="col-sm-3 d-flex justify-content-center">
                  <FontAwesomeIcon :icon="faDollar" size="2x" class="text-primary mr-3" />
                </div>
                <div class="col-sm-9">
                  <h5 class="card-title text-uppercase">sales
                    <span class="fw-semibold">(today)</span>
                    <!-- <small class="fs-6 text-capitalize d-block">(items)(revenue)</small> -->
                  </h5>
                  <p class="card-text fw-semibold fs-5">{{ todaySaleItemsCount }}
                    (<span class="price">{{ formatCurrency(totalSalesAmountToday) }}</span>)
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-auto col-sm-12  col-md-6 col-lg-6 col-xl-4 gy-3">
          <div class="card bg-secondary-subtle shadow-2-strong">
            <div class="card-body px-2">
              <div class="d-flex align-items-center">
                <div class="col-sm-3 d-flex justify-content-center">
                  <FontAwesomeIcon :icon="faDollar" size="2x" class="text-primary mr-3" />
                </div>
                <div class="col-sm-9">
                  <h5 class="card-title text-uppercase">sales <span class="fw-semibold">
                      (via momo) </span>

                    <!-- <small class="fs-6 text-capitalize d-block">(via MoMo)</small> -->
                  </h5>
                  <p class="card-text fs-5 fw-semibold price">
                    {{ formatCurrency(totalSalesByMomo) }}
                    (<span> {{ toPercentage(totalSalesByMomo, totalSalesAmount) }} </span>)
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-auto col-sm-12  col-md-6 col-lg-6 col-xl-4 gy-3">
          <div class="card bg-secondary-subtle shadow-2-strong">
            <div class="card-body px-2">
              <div class="d-flex align-items-center">
                <div class="col-sm-3 d-flex justify-content-center">
                  <FontAwesomeIcon :icon="faDollar" size="2x" class="text-primary mr-3" />
                </div>
                <div class="col-sm-9">
                  <h5 class="card-title text-uppercase">sales
                    <span class="fw-semibold">
                      (via cash)
                    </span>
                    <!-- <small class="fs-6 text-capitalize d-block">(via Cash) (revenue)</small> -->
                  </h5>
                  <p class="card-text fs-5 fw-semibold">
                    {{ formatCurrency(totalSalesByCash) }}
                    (<span class="price">
                      {{ toPercentage(totalSalesByCash, totalSalesAmount) }}
                    </span>)
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-auto col-sm-12  col-md-6 col-lg-6 col-xl-4 gy-3">
          <div class="card bg-secondary-subtle shadow-2-strong">
            <div class="card-body px-2">
              <div class="d-flex align-items-center">
                <div class="col-sm-3 d-flex justify-content-center">
                  <FontAwesomeIcon :icon="faDollar" size="2x" class="text-primary mr-3" />
                </div>
                <div class="col-sm-9">
                  <h5 class="card-title text-uppercase">sales
                    <span class="fw-semibold">
                      (last 7 days)
                    </span>
                    <!-- <small class="fs-6 text-capitalize d-block">(Last   Weeek)(revenue)</small> -->
                  </h5>
                  <p class="card-text fw-semibold fs-5">{{ lastWeekSaleItemsCount }}
                    (<span class="price">{{ formatCurrency(totalSalesLastWeek) }}</span>)
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- </div> -->
        <div class="col-auto col-sm-12  col-md-6 col-lg-6 col-xl-4 gy-3">
          <div class="card bg-secondary-subtle shadow-2-strong">
            <div class="card-body px-2">
              <div class="d-flex align-items-center">
                <div class="col-sm-3 d-flex justify-content-center">
                  <FontAwesomeIcon :icon="faUsers" size="2x" class="text-primary mr-3" />
                </div>
                <div class="col-sm-9">
                  <h5 class="card-title text-uppercase">customers</h5>
                  <p class="card-text fs-5 fw-semibold">{{ statData.customers_count }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div v-else class="row">
        <div class="col-auto col-sm-12  col-md-6 col-lg-6 col-xl-4 gy-3">
          <div class="card placeholder-card">
            <div class="card-body px-2">
              <div class="d-flex align-items-center">
                <div class="col-sm-3 d-flex justify-content-center">
                  <div class="placeholder-glow">
                    <span class="placeholder col-8"></span>
                  </div>
                </div>
                <div class="col-sm-9">
                  <h5 class="card-title text-uppercase placeholder-glow">
                    <span class="placeholder col-6"></span>
                  </h5>
                  <p class="card-text placeholder-glow">
                    <span class="placeholder col-8"></span>
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-auto col-sm-12  col-md-6 col-lg-6 col-xl-4 gy-3">
          <div class="card placeholder-card">
            <div class="card-body px-2">
              <div class="d-flex align-items-center">
                <div class="col-sm-3 d-flex justify-content-center">
                  <div class="placeholder-glow">
                    <span class="placeholder col-8"></span>
                  </div>
                </div>
                <div class="col-sm-9">
                  <h5 class="card-title text-uppercase placeholder-glow">
                    <span class="placeholder col-6"></span>
                  </h5>
                  <p class="card-text placeholder-glow">
                    <span class="placeholder col-8"></span>
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-auto col-sm-12  col-md-6 col-lg-6 col-xl-4 gy-3">
          <div class="card placeholder-card">
            <div class="card-body px-2">
              <div class="d-flex align-items-center">
                <div class="col-sm-3 d-flex justify-content-center">
                  <div class="placeholder-glow">
                    <span class="placeholder col-8"></span>
                  </div>
                </div>
                <div class="col-sm-9">
                  <h5 class="card-title text-uppercase placeholder-glow">
                    <span class="placeholder col-6"></span>
                  </h5>
                  <p class="card-text placeholder-glow">
                    <span class="placeholder col-8"></span>
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-auto col-sm-12  col-md-6 col-lg-6 col-xl-4 gy-3">
          <div class="card placeholder-card">
            <div class="card-body px-2">
              <div class="d-flex align-items-center">
                <div class="col-sm-3 d-flex justify-content-center">
                  <div class="placeholder-glow">
                    <span class="placeholder col-8"></span>
                  </div>
                </div>
                <div class="col-sm-9">
                  <h5 class="card-title text-uppercase placeholder-glow">
                    <span class="placeholder col-6"></span>
                  </h5>
                  <p class="card-text placeholder-glow">
                    <span class="placeholder col-8"></span>
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-auto col-sm-12  col-md-6 col-lg-6 col-xl-4 gy-3">
          <div class="card placeholder-card">
            <div class="card-body px-2">
              <div class="d-flex align-items-center">
                <div class="col-sm-3 d-flex justify-content-center">
                  <div class="placeholder-glow">
                    <span class="placeholder col-8"></span>
                  </div>
                </div>
                <div class="col-sm-9">
                  <h5 class="card-title text-uppercase placeholder-glow">
                    <span class="placeholder col-6"></span>
                  </h5>
                  <p class="card-text placeholder-glow">
                    <span class="placeholder col-8"></span>
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div v-if="!isFetching" class="row mt-5">
        <div class="col-sm-6 col-xxl-5 gy-3">
          <BarChart title="Top 10 Selling items (last 7 days)" :data="statData.top_selling_products_last_week"
            xAxisLabel="Product" yAxisLabel="Quantity Sold" />
        </div>
        <div class="col-sm-6 col-xxl-7 gy-3">
          <LineChart :data="lastWeekSales" />
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped></style>
