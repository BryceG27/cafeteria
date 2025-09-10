<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { reactive, ref, computed } from "vue";

// vue-chartjs, for more info and examples you can check out https://vue-chartjs.org/ and http://www.chartjs.org/docs/ -->
import { Line, Bar } from "vue-chartjs";
import { Chart, registerables } from "chart.js";
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import InputText from 'primevue/inputtext';
import InputGroup from 'primevue/inputgroup';
import InputGroupAddon from 'primevue/inputgroupaddon';
import DatePicker from 'primevue/datepicker';
import Dropdown from 'primevue/dropdown';

import { FilterMatchMode } from '@primevue/core/api';

import moment from 'moment';

const props = defineProps({
    auth: Object,
    customers : Array,
    orders : Array,
    ordered_food : Array,
    errors: Object,
});

const todayOrders = computed(() => {
    return props.orders.filter(order => moment(order.order_date).isSame(moment(), 'day') && order.status == 1);
})

const amountOrders = computed(() => {
    return props.orders.reduce((acc, order) => {
        return acc + (order.status == 1 ? order.total_amount - order.to_be_paid : 0);
    }, 0);
})

const mostOrderedDish = computed(() => {
    if (props.ordered_food.length > 0) {
        return props.ordered_food.reduce((max, item) => item.count > max.count ? item : max, props.ordered_food[0]);
    }
    return 'Nessun dato disponibile';
});

Chart.register(...registerables);

// Set Global Chart.js configuration
Chart.defaults.color = "#818d96";
Chart.defaults.scale.grid.lineWidth = 0;
Chart.defaults.scale.beginAtZero = true;
Chart.defaults.datasets.bar.maxBarThickness = 45;
Chart.defaults.elements.bar.borderRadius = 4;
Chart.defaults.elements.bar.borderSkipped = false;
Chart.defaults.elements.point.radius = 0;
Chart.defaults.elements.point.hoverRadius = 0;
Chart.defaults.plugins.tooltip.radius = 3;
Chart.defaults.plugins.legend.labels.boxWidth = 10;

// Helper variables
const ordersViews = ref([
    { label : 'Ordini recenti', id : 1 },
    { label : 'Ordini di oggi', id : 2 }
]);

const currentView = ref(1);

const filteredOrders = computed(() => {
    return props.orders.filter(order => currentView.value == 1 ? true : order.order_date == moment().format('YYYY-MM-DD'))
})

const expandedRows = ref([]);
const filters = ref({
    child_name : { value : null, matchMode : FilterMatchMode.CONTAINS },
    'menu.name' : { value : null, matchMode : FilterMatchMode.CONTAINS },
    order_date : { value : null, matchMode : FilterMatchMode.DATE_IS},
    'status_info.id' : { value : null, matchMode : FilterMatchMode.EQUALS}
})

const get_order_data_by_week = (starting_day) => {
    let data = [];

    const startOfWeek = moment(starting_day).isoWeekday(1).startOf('week').format('YYYY-MM-DD');
    const endOfWeek = moment(starting_day).endOf('week').add(1, 'days').format('YYYY-MM-DD');

    for (let day = 0; day < 7; day++) {
        const currentDay = moment(startOfWeek).add(day, 'days').format('YYYY-MM-DD');
        const dayOrders = props.orders.filter(order => moment(order.order_date).isSame(currentDay, 'day') && order.status == 1);
        const dayTotal = dayOrders.reduce((acc, order) => acc + (order.total_amount - order.to_be_paid), 0);
        data.push(dayTotal);
    }

    return data;
}

// Chart Earnings data
const earningsData = reactive({
    labels: ["LUN", "MAR", "MER", "GIO", "VEN", "SAB", "DOM"],
    datasets: [
        {
            label: "Settimana corrente",
            fill: true,
            backgroundColor: "rgba(100, 116, 139, .7)",
            borderColor: "transparent",
            pointBackgroundColor: "rgba(100, 116, 139, 1)",
            pointBorderColor: "#fff",
            pointHoverBackgroundColor: "#fff",
            pointHoverBorderColor: "rgba(100, 116, 139, 1)",
            data: get_order_data_by_week(moment().format('YYYY-MM-DD')),
        },
        {
            label: "Settimana passata",
            fill: true,
            backgroundColor: "rgba(100, 116, 139, .15)",
            borderColor: "transparent",
            pointBackgroundColor: "rgba(100, 116, 139, 1)",
            pointBorderColor: "#fff",
            pointHoverBackgroundColor: "#fff",
            pointHoverBorderColor: "rgba(100, 116, 139, 1)",
            data: get_order_data_by_week(moment().subtract(1, 'weeks').format('YYYY-MM-DD')),
        },
    ],
});

// Chart Earnings options
const earningsOptions = reactive({
    responsive: true,
    maintainAspectRatio: false,
    scales: {
        x: {
            display: false,
            grid: {
                drawBorder: false,
            },
        },
        y: {
            display: false,
            grid: {
                drawBorder: false,
            },
        },
    },
    interaction: {
        intersect: false,
    },
    plugins: {
        legend: {
            labels: {
                boxHeight: 10,
                font: {
                    size: 14,
                },
            },
        },
        tooltip: {
            callbacks: {
                label: function (context) {
                    return context.dataset.label + ": $" + context.parsed.y;
                },
            },
        },
    },
});

// Chart Total Orders data
const totalOrdersData = reactive({
    labels: [
        "LUN",
        "MAR",
        "MER",
        "GIO",
        "VEN",
        "SAB",
        "DOM",
        "LUN",
        "MAR",
        "MER",
        "GIO",
        "VEN",
        "SAB",
        "DOM",
    ],
    datasets: [
        {
            label: "Totale ordini",
            fill: true,
            backgroundColor: "rgba(220, 38, 38, .15)",
            borderColor: "transparent",
            pointBackgroundColor: "rgba(220, 38, 38, 1)",
            pointBorderColor: "#fff",
            pointHoverBackgroundColor: "#fff",
            pointHoverBorderColor: "rgba(220, 38, 38, 1)",
            data: [...new Set([get_order_data_by_week(moment().format('YYYY-MM-DD')), get_order_data_by_week(moment().subtract(1, 'weeks').format('YYYY-MM-DD'))].flat())],
        },
    ],
});

// Chart Total Orders options
const totalOrdersOptions = reactive({
    maintainAspectRatio: false,
    tension: 0.4,
    scales: {
        x: {
            display: false,
        },
        y: {
            display: false,
        },
    },
    interaction: {
        intersect: false,
    },
    plugins: {
        legend: {
            display: false,
        },
        tooltip: {
            callbacks: {
                label: function (context) {
                    return " " + context.parsed.y + " Orders";
                },
            },
        },
    },
});

// Chart Total Earnings data
const totalEarningsData = reactive({
    labels: [
        "LUN",
        "MAR",
        "MER",
        "GIO",
        "VEN",
        "SAB",
        "DOM",
        "LUN",
        "MAR",
        "MER",
        "GIO",
        "VEN",
        "SAB",
        "DOM",
    ],
    datasets: [
        {
            label: "Total Earnings",
            fill: true,
            backgroundColor: "rgba(101, 163, 13, .15)",
            borderColor: "transparent",
            pointBackgroundColor: "rgba(101, 163, 13, 1)",
            pointBorderColor: "#fff",
            pointHoverBackgroundColor: "#fff",
            pointHoverBorderColor: "rgba(101, 163, 13, 1)",
            data: [
                716, 1185, 750, 1365, 956, 890, 1200, 968, 1158, 1025, 920, 1190, 720,
                1352,
            ],
        },
    ],
});

// Chart Total Earnings options
const totalEarningsOptions = reactive({
    maintainAspectRatio: false,
    tension: 0.4,
    scales: {
        x: {
            display: false,
        },
        y: {
            display: false,
        },
    },
    interaction: {
        intersect: false,
    },
    plugins: {
        legend: {
            display: false,
        },
        tooltip: {
            callbacks: {
                label: function (context) {
                    return " $" + context.parsed.y;
                },
            },
        },
    },
});

// Chart New Customers data
const newCustomersData = reactive({
    labels: [
        "MON",
        "TUE",
        "WED",
        "THU",
        "FRI",
        "SAT",
        "SUN",
        "MON",
        "TUE",
        "WED",
        "THU",
        "FRI",
        "SAT",
        "SUN",
    ],
    datasets: [
        {
            label: "Total Orders",
            fill: true,
            backgroundColor: "rgba(101, 163, 13, .15)",
            borderColor: "transparent",
            pointBackgroundColor: "rgba(101, 163, 13, 1)",
            pointBorderColor: "#fff",
            pointHoverBackgroundColor: "#fff",
            pointHoverBorderColor: "rgba(101, 163, 13, 1)",
            data: [25, 15, 36, 14, 29, 19, 36, 41, 28, 26, 29, 33, 23, 41],
        },
    ],
});

// Chart New Customers options
const newCustomersOptions = reactive({
    maintainAspectRatio: false,
    tension: 0.4,
    scales: {
        x: {
            display: false,
        },
        y: {
            display: false,
        },
    },
    interaction: {
        intersect: false,
    },
    plugins: {
        legend: {
            display: false,
        },
        tooltip: {
            callbacks: {
                label: function (context) {
                    return " " + context.parsed.y + " Customers";
                },
            },
        },
    },
});
</script>

<template>
    <AuthenticatedLayout>
        
        <Head title="Dashboard" />
        <!-- Hero -->
        <div class="content">
            <div
                class="d-flex flex-column flex-md-row justify-content-md-between align-items-md-center py-2 text-center text-md-start">
                <div class="flex-grow-1 mb-1 mb-md-0">
                    <h1 class="h3 fw-bold mb-2">Statistiche</h1>
                    <h2 class="h6 fw-medium fw-medium text-muted mb-0">
                        Ciao
                        <em>
                            {{ $page.props.auth.user.name }}
                        </em>
                    </h2>
                </div>
                <div class="mt-3 mt-md-0 ms-md-3 space-x-1">
                    <a href="javascript:void(0)" class="btn btn-sm btn-alt-secondary space-x-1">
                        <i class="fa fa-cogs opacity-50"></i>
                        <span>Settings</span>
                    </a>
                    <div class="dropdown d-inline-block">
                        <button type="button" class="btn btn-sm btn-alt-secondary space-x-1"
                            id="dropdown-analytics-overview" data-bs-toggle="dropdown" aria-haspopup="true"
                            aria-expanded="false">
                            <i class="fa fa-fw fa-calendar-alt opacity-50"></i>
                            <span>All time</span>
                            <i class="fa fa-fw fa-angle-down"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end fs-sm" aria-labelledby="dropdown-analytics-overview">
                            <a class="dropdown-item fw-medium" href="javascript:void(0)">Last 30 days</a>
                            <a class="dropdown-item fw-medium" href="javascript:void(0)">Last month</a>
                            <a class="dropdown-item fw-medium" href="javascript:void(0)">Last 3 months</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item fw-medium" href="javascript:void(0)">This year</a>
                            <a class="dropdown-item fw-medium" href="javascript:void(0)">Last Year</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item fw-medium d-flex align-items-center justify-content-between"
                                href="javascript:void(0)">
                                <span>All time</span>
                                <i class="fa fa-check"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- END Hero -->

        <!-- Page Content -->
        <div class="content">
            <!-- Overview -->
            <div class="row items-push">
                <div class="col-sm-6 col-xxl-3">
                    <!-- Pending Orders -->
                    <BaseBlock class="d-flex flex-column h-100 mb-0">
                        <template #content>
                            <div
                                class="block-content block-content-full flex-grow-1 d-flex justify-content-between align-items-center">
                                <dl class="mb-0">
                                    <dt class="fs-3 fw-bold" v-text="todayOrders.length" />
                                    <dd class="fs-sm fw-medium fs-sm fw-medium text-muted mb-0">
                                        Ordini di oggi pagati
                                    </dd>
                                </dl>
                                <div class="item item-rounded-lg bg-body-light">
                                    <i class="fa fa-shopping-cart fs-3 text-primary"></i>
                                </div>
                            </div>
                            <div class="bg-body-light rounded-bottom">
                                <Link 
                                    class="block-content block-content-full block-content-sm fs-sm fw-medium d-flex align-items-center justify-content-between"
                                    :href="route('orders.index')"
                                >
                                    <span>Visualizza tutti gli ordini</span>
                                    <i class="fa fa-arrow-alt-circle-right ms-1 opacity-25 fs-base"></i>
                                </Link>
                            </div>
                        </template>
                    </BaseBlock>
                    <!-- END Pending Orders -->
                </div>
                <div class="col-sm-6 col-xxl-3">
                    <!-- New Customers -->
                    <BaseBlock class="d-flex flex-column h-100 mb-0">
                        <template #content>
                            <div
                                class="block-content block-content-full flex-grow-1 d-flex justify-content-between align-items-center">
                                <dl class="mb-0">
                                    <dt class="fs-3 fw-bold" v-text="customers.length" />
                                    <dd class="fs-sm fw-medium fs-sm fw-medium text-muted mb-0">
                                        Clienti attivi
                                    </dd>
                                </dl>
                                <div class="item item-rounded-lg bg-body-light">
                                    <i class="far fa-user-circle fs-3 text-primary"></i>
                                </div>
                            </div>
                            <div class="bg-body-light rounded-bottom">
                                <Link class="block-content block-content-full block-content-sm fs-sm fw-medium d-flex align-items-center justify-content-between"
                                    :href="route('customers.index')">
                                    <span>Visualizza tutti i clienti</span>
                                    <i class="fa fa-arrow-alt-circle-right ms-1 opacity-25 fs-base"></i>
                                </Link>
                            </div>
                        </template>
                    </BaseBlock>
                    <!-- END New Customers -->
                </div>
                <div class="col-sm-6 col-xxl-3">
                    <!-- Messages -->
                    <BaseBlock class="d-flex flex-column h-100 mb-0">
                        <template #content>
                            <div
                                class="block-content block-content-full flex-grow-1 d-flex justify-content-between align-items-center">
                                <dl class="mb-0">
                                    <dt class="fs-3 fw-bold" v-text="`${mostOrderedDish.name} (${mostOrderedDish.count})`" />
                                    <dd class="fs-sm fw-medium fs-sm fw-medium text-muted mb-0">
                                        Prodotto più ordinato del mese
                                    </dd>
                                </dl>
                                <div class="item item-rounded-lg bg-body-light">
                                    <!-- <i class="far fa-paper-plane fs-3 text-primary"></i> -->
                                    <i class="fa fa-plate-wheat fs-3 text-primary"></i>
                                </div>
                            </div>
                            <div class="bg-body-light rounded-bottom">
                                <Link class="block-content block-content-full block-content-sm fs-sm fw-medium d-flex align-items-center justify-content-between"
                                    :href="route('products.index')">
                                    <span>Visualizza tutti i prodotti</span>
                                    <i class="fa fa-arrow-alt-circle-right ms-1 opacity-25 fs-base"></i>
                                </Link>
                            </div>
                        </template>
                    </BaseBlock>
                    <!-- END Messages -->
                </div>
                <div class="col-sm-6 col-xxl-3">
                    <!-- Conversion Rate -->
                    <BaseBlock class="d-flex flex-column h-100 mb-0">
                        <template #content>
                            <div
                                class="block-content block-content-full flex-grow-1 d-flex justify-content-between align-items-center">
                                <dl class="mb-0">
                                    <dt class="fs-3 fw-bold">4.5%</dt>
                                    <dd class="fs-sm fw-medium fs-sm fw-medium text-muted mb-0">
                                        Conversion Rate
                                    </dd>
                                </dl>
                                <div class="item item-rounded-lg bg-body-light">
                                    <i class="fa fa-chart-bar fs-3 text-primary"></i>
                                </div>
                            </div>
                            <div class="bg-body-light rounded-bottom">
                                <a class="block-content block-content-full block-content-sm fs-sm fw-medium d-flex align-items-center justify-content-between"
                                    href="javascript:void(0)">
                                    <span>View statistics</span>
                                    <i class="fa fa-arrow-alt-circle-right ms-1 opacity-25 fs-base"></i>
                                </a>
                            </div>
                        </template>
                    </BaseBlock>
                    <!-- END Conversion Rate-->
                </div>
            </div>
            <!-- END Overview -->

            <!-- Statistics -->
            <div class="row">
                <div class="col-xl-8 col-xxl-9 d-flex flex-column">
                    <!-- Earnings Summary -->
                    <BaseBlock title="Riassunto guadagni" class="flex-grow-1 d-flex flex-column">
                        <template #options>
                            <button type="button" class="btn-block-option">
                                <i class="si si-settings"></i>
                            </button>
                        </template>

                        <template #content>
                            <div class="block-content block-content-full flex-grow-1 d-flex items-center">
                                <Bar :chart-data="earningsData" :chart-options="earningsOptions" class="w-100" />
                            </div>
                            <div class="block-content bg-body-light">
                                <div class="row items-push text-center w-100">
                                    <div class="col-sm-4">
                                        <dl class="mb-0">
                                            <dt class="fs-3 fw-bold d-inline-flex align-items-center space-x-2">
                                                <i class="fa fa-caret-up fs-base text-success"></i>
                                                <span>2.5%</span>
                                            </dt>
                                            <dd class="fs-sm fw-medium text-muted mb-0">
                                                Customer Growth
                                            </dd>
                                        </dl>
                                    </div>
                                    <div class="col-sm-4">
                                        <dl class="mb-0">
                                            <dt class="fs-3 fw-bold d-inline-flex align-items-center space-x-2">
                                                <i class="fa fa-caret-up fs-base text-success"></i>
                                                <span>3.8%</span>
                                            </dt>
                                            <dd class="fs-sm fw-medium text-muted mb-0">Page Views</dd>
                                        </dl>
                                    </div>
                                    <div class="col-sm-4">
                                        <dl class="mb-0">
                                            <dt class="fs-3 fw-bold d-inline-flex align-items-center space-x-2">
                                                <i class="fa fa-caret-down fs-base text-danger"></i>
                                                <span>1.7%</span>
                                            </dt>
                                            <dd class="fs-sm fw-medium text-muted mb-0">
                                                New Products
                                            </dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </BaseBlock>
                    <!-- END Earnings Summary -->
                </div>
                <div class="col-xl-4 col-xxl-3 d-flex flex-column">
                    <!-- Last 2 Weeks -->
                    <div class="row items-push flex-grow-1">
                        <div class="col-md-6 col-xl-12">
                            <BaseBlock class="d-flex flex-column h-100 mb-0">
                                <template #content>
                                    <div class="block-content flex-grow-1 d-flex justify-content-between">
                                        <dl class="mb-0">
                                            <dt class="fs-3 fw-bold" v-text="orders.length" />
                                            <dd class="fs-sm fw-medium text-muted mb-0">
                                                Totale ordini
                                            </dd>
                                        </dl>
                                        <div>
                                            <div
                                                class="d-inline-block px-2 py-1 rounded-3 fs-xs fw-semibold text-danger bg-danger-light">
                                                <i class="fa fa-caret-down me-1"></i>
                                                2.2%
                                            </div>
                                        </div>
                                    </div>
                                    <div class="block-content p-1 text-center overflow-hidden">
                                        <Line :chart-data="totalOrdersData" :chart-options="totalOrdersOptions"
                                            style="height: 90px" />
                                    </div>
                                </template>
                            </BaseBlock>
                        </div>
                        <div class="col-md-6 col-xl-12">
                            <BaseBlock class="d-flex flex-column h-100 mb-0">
                                <template #content>
                                    <div class="block-content flex-grow-1 d-flex justify-content-between">
                                        <dl class="mb-0">
                                            <dt class="fs-3 fw-bold">{{ parseFloat(amountOrders).toFixed(2) }} &euro;</dt>
                                            <dd class="fs-sm fw-medium text-muted mb-0">
                                                Guadagno totale
                                            </dd>
                                        </dl>
                                        <div>
                                            <div
                                                class="d-inline-block px-2 py-1 rounded-3 fs-xs fw-semibold text-success bg-success-light">
                                                <i class="fa fa-caret-up me-1"></i>
                                                4.2%
                                            </div>
                                        </div>
                                    </div>
                                    <div class="block-content p-1 text-center overflow-hidden">
                                        <Line :chart-data="totalEarningsData" :chart-options="totalEarningsOptions"
                                            style="height: 90px" />
                                    </div>
                                </template>
                            </BaseBlock>
                        </div>
                        <div class="col-xl-12">
                            <BaseBlock class="d-flex flex-column h-100 mb-0">
                                <template #content>
                                    <div class="block-content flex-grow-1 d-flex justify-content-between">
                                        <dl class="mb-0">
                                            <dt class="fs-3 fw-bold">264</dt>
                                            <dd class="fs-sm fw-medium text-muted mb-0">
                                                New Customers
                                            </dd>
                                        </dl>
                                        <div>
                                            <div
                                                class="d-inline-block px-2 py-1 rounded-3 fs-xs fw-semibold text-success bg-success-light">
                                                <i class="fa fa-caret-up me-1"></i>
                                                9.3%
                                            </div>
                                        </div>
                                    </div>
                                    <div class="block-content p-1 text-center overflow-hidden">
                                        <!-- New Customers Chart Container -->
                                        <Line :chart-data="newCustomersData" :chart-options="newCustomersOptions"
                                            style="height: 90px" />
                                    </div>
                                </template>
                            </BaseBlock>
                        </div>
                    </div>
                    <!-- END Last 2 Weeks -->
                </div>
            </div>
            <!-- END Statistics -->

            <!-- Recent Orders -->
            <BaseBlock :title="ordersViews.find(view => view.id == currentView).label">
                <template #options>
                    <div class="d-flex justify-content-center">
                        <button class="btn btn-link" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="si si-settings"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <template v-for="view in ordersViews" :key="view.id">
                                <button class="dropdown-item clickable" @click="currentView = view.id">
                                    {{ view.label }}
                                </button>
                                <li class="dropdown-divider" v-if="view.id % 2 != 0" />
                            </template>
                        </ul>
                    </div>
                </template>
                <div class="content">
                    <DataTable
                        paginator
                        :rows="5"
                        :value="filteredOrders"
                        v-model:expandedRows="expandedRows"
                        v-model:filters="filters"
                        filterDisplay="row"
                    >
                        <template #empty>
                            <div class="p-4 text-center">
                                <i class="fa fa-exclamation-triangle fa-2x"></i>
                                <p class="mt-2">Nessun ordine trovato</p>
                            </div>
                        </template>
                        <template #expansion="{ data }">
                            <div class="p-4">
                                <h5>Prodotti nel menu</h5>
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Primo piatto</th>
                                            <th>Secondo piatto</th>
                                            <th>Contorno</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <span v-if="data.first_dish" v-text="data.first_dish.name" />
                                                <span v-else>---</span>
                                            </td>
                                            <td>
                                                <span v-if="data.second_dish" v-text="data.second_dish.name" />
                                                <span v-else>---</span>
                                            </td>
                                            <td>
                                                <span v-if="data.side_dish" v-text="data.side_dish.name" />
                                                <span v-else>---</span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </template>
                        <Column style="width: 5%" expander />
                        <Column style="width: 30%" header="Cliente" field="child_name" :showFilterMenu="false">
                            <template #body="{ data }">
                                <div class="d-flex align-items-center">
                                    {{ data.child_name }}
                                    <a class="link-info ms-2" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fa fa-info"></i>
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li class="dropdown-item">
                                            <strong>Genitore: </strong><em v-text="`${data.customer.name} ${data.customer.surname}`" />
                                        </li>
                                    </ul>
                                </div>
                            </template>
                            <template #filter="{ filterModel, filterCallback }">
                                <InputGroup>
                                    <InputText 
                                        class="w-100"
                                        inputClass="w-100"
                                        v-model="filterModel.value"
                                        placeholder="Nome cliente"
                                        @input="filterCallback"
                                    />
                                    <InputGroupAddon>
                                        <button class="btn-link link-danger" type="button" @click.prevent="filterModel.value = null; filterCallback">
                                            <i class="fa fa-x"></i>
                                        </button>
                                    </InputGroupAddon>
                                </InputGroup>
                            </template>
                        </Column>
                        <Column style="width: 25%" header="Nome" field="menu.name" :showFilterMenu="false">
                            <template #filter="{ filterModel, filterCallback }">
                                <InputGroup>
                                    <InputText 
                                        class="w-100"
                                        inputClass="w-100"
                                        v-model="filterModel.value"
                                        placeholder="Nome menù"
                                        @input="filterCallback"
                                    />
                                    <InputGroupAddon>
                                        <button class="btn-link link-danger" type="button" @click.prevent="filterModel.value = null; filterCallback">
                                            <i class="fa fa-x"></i>
                                        </button>
                                    </InputGroupAddon>
                                </InputGroup>
                            </template>
                        </Column>
                        <Column style="width: 20%" header="Giorno" field="order_date" :showFilterMenu="false">
                            <template #body="{ data }">
                                {{ moment(data.order_date).format('DD/MM/YYYY') }}
                            </template>
                            <template #filter="{ filterModel, filterCallback }">
                                <InputGroup>
                                    <DatePicker 
                                        inputClass="w-100"
                                        class="w-100"
                                        @date-change="console.log($event)"
                                        placeholder="Cerca per data"
                                    />
                                    <InputGroupAddon>
                                        <button class="btn-link link-danger" type="button" @click.prevent="filterModel.value = null; filterCallback">
                                            <i class="fa fa-x"></i>
                                        </button>
                                    </InputGroupAddon>
                                </InputGroup>
                            </template>
                        </Column>
                        <Column style="width: 20%" header="Stato" field="status_info.id" :showFilterMenu="false">
                            <template #body="{ data }">
                                <span :class="`badge text-bg-${data.status_info.color}`" v-text="data.status_info.label" />
                            </template>
                            <template #filter="{ filterModel, filterCallback }">
                                <InputGroup>
                                    <Dropdown 
                                        class="w-100"
                                        inputClass="w-100"
                                        placeholder="Cerca per stato"
                                    />
                                    <InputGroupAddon>
                                        <button class="btn-link link-danger" type="button" @click.prevent="filterModel.value = null; filterCallback">
                                            <i class="fa fa-x"></i>
                                        </button>
                                    </InputGroupAddon>
                                </InputGroup>
                            </template>
                        </Column>
                    </DataTable>
                </div>
            </BaseBlock>
            <!-- END Recent Orders -->
        </div>
        <!-- END Page Content -->
    </AuthenticatedLayout>
</template>
<style scoped>
    .clickable {
        cursor: pointer;
    }
</style>