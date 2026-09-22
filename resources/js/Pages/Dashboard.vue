<script setup>
import { Head, Link } from '@inertiajs/vue3';
import jsPDF from 'jspdf';
import { reactive, ref, computed, onMounted, watch } from "vue";
import axios from 'axios';

// vue-chartjs, for more info and examples you can check out https://vue-chartjs.org/ and http://www.chartjs.org/docs/ -->
import { Line, Bar } from "vue-chartjs";
import { Chart, registerables } from "chart.js";
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import InputText from 'primevue/inputtext';
import DatePicker from 'primevue/datepicker';
import Select from 'primevue/select';

import { FilterMatchMode } from '@primevue/core/api';

import moment from 'moment';

const MoneyFormat = new Intl.NumberFormat("it-IT", {
    style : 'currency',
    currency : 'EUR'
})

const props = defineProps({
    auth: Object,
    customers : Array,
    orders : Array,
    orders_month_ago : Array,
    ordered_food : Array,
    payments : Array,
    errors: Object,
});

const productFilters = reactive({
    name : '',
    selectOptions : [
        { label : 'Giorno', value : 0, placeholder : 'Seleziona un giorno'},
        { label : 'Settimana', value : 1, placeholder : 'Seleziona un giorno della settimana'},
        { label : 'Mese', value : 2, placeholder : 'Seleziona un giorno del mese'},
    ],
    filterType : 0,
    selectedDate : moment().format('YYYY-MM-DD')
})

const calculate_week_difference = computed(() => {
    const currentWeek = moment().isoWeek();
    const lastWeek = moment().subtract(1, 'weeks').isoWeek();

    const currentWeekTotal = props.orders.reduce((sum, order) => {
        return sum + (moment(order.order_date).isoWeek() === currentWeek && order.status == 1 ? parseFloat(order.total_amount - order.to_be_paid) : 0);
    }, 0);

    const lastWeekTotal = props.orders.reduce((sum, order) => {
        return sum + (moment(order.order_date).isoWeek() === lastWeek && order.status == 1 ? parseFloat(order.total_amount - order.to_be_paid) : 0);
    }, 0);

    if (lastWeekTotal === 0) {
        return currentWeekTotal === 0 ? 0 : 100; // If last week was 0 and this week is not, return 100% increase
    }

    const difference = ((currentWeekTotal - lastWeekTotal) / lastWeekTotal) * 100;
    return difference.toFixed(2);
});

const calculate_month_difference = computed(() => {
    const currentMonth = moment().month();
    const lastMonth = moment().subtract(1, 'months').month();

    const currentMonthTotal = props.orders.reduce((sum, order) => {
        return sum + (moment(order.order_date).month() === currentMonth && order.status == 1 ? parseFloat(order.total_amount - order.to_be_paid) : 0);
    }, 0);

    const lastMonthTotal = props.orders_month_ago.reduce((sum, order) => {
        return sum + (moment(order.order_date).month() === lastMonth && order.status == 1 ? parseFloat(order.total_amount - order.to_be_paid) : 0);
    }, 0);

    if (lastMonthTotal === 0) {
        return currentMonthTotal === 0 ? 0 : 100; // If last month was 0 and this month is not, return 100% increase
    }

    const difference = ((currentMonthTotal - lastMonthTotal) / lastMonthTotal) * 100;
    return difference.toFixed(2);
})

const total_payments = (method = null) => {
    if(method)
        return props.payments.reduce((sum, payment) => sum + (payment.status == 1 && payment.payment_method_id == method ? parseFloat(payment.amount) : 0), 0);
    return props.payments.reduce((sum, payment) => sum + (payment.status == 1 ? parseFloat(payment.amount) : 0), 0);
}

const productsFiltered    = ref([]);
const productCategories   = ref([]);
const exportColumns        = ref({});
const currentOrderView    = ref(1);
const currentPaymentsView = ref(1);
const paymentsFilterDate  = ref([]);

const paymentsView = ref([
    { label : 'Riassunto guadagni anno scolastico corrente', id : 1 },
    { label : 'Riassunto guadagni del mese', id : 2 },
    { label : 'Riassunto guadagni del giorno', id: 3 },
    { label : 'Riassunto guadagni per periodo', id: 4 }
]);

const ordersViews = ref([
    { label : 'Ordini del mese', id : 1 },
    { label : 'Ordini di oggi', id : 2 },
    { label : 'Prodotti venduti', id: 3}
]);

const productsForOrderView = computed(() => [
    ...productsFiltered.value.map(product => ({
        ...product,
        is_special_menu: false,
    })),
    ...(exportColumns.value.special_menus || []).map(specialMenu => ({
        id: `special-menu-${specialMenu.id}`,
        special_menu_id: specialMenu.id,
        name: specialMenu.product_name,
        product_name: specialMenu.name,
        type: specialMenu.product_name ? `Menù special - ${specialMenu.name}` : 'Menù special',
        times_ordered: specialMenu.times_ordered,
        detail: specialMenu.detail,
        is_special_menu: true,
    })),
]);


const filteredOrders = computed(() => {
    return props.orders.filter(order => currentOrderView.value == 1 ? true : order.order_date == moment().format('YYYY-MM-DD')).filter(order => order_date_filter.value == null ? true : moment(order_date_filter.value).format('YYYY-MM-DD') == order.order_date)
})

const selectedDateType = computed(() => {
    if(productFilters.filterType)
        return productFilters.selectOptions.find(option => productFilters.filterType == option.value).placeholder
    return "Seleziona un giorno"
})

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

const filteredEarnings = computed(() => {
    const daysInMonth = moment().daysInMonth();
    let earningsData = Array(daysInMonth).fill(0);

    props.orders.forEach(order => {
        if (moment(order.order_date).month() === moment().month() && order.status == 1) {
            const day = moment(order.order_date).date() - 1; // -1 because array is 0-indexed
            earningsData[day] += (order.total_amount - order.to_be_paid);
        }
    });
    
    return earningsData;
})

const currentMonthDays = computed(() => {
    const daysInMonth = moment().daysInMonth();
    let days = [];
    for (let day = 1; day <= daysInMonth; day++) {
        days.push(`${day} ${moment().format('MMM')}`);
    }
    
    return days;
})

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

const expandedRows          = ref([]);
const productsExpandedRows  = ref([]);
const filters = ref({
    child_name : { value : null, matchMode : FilterMatchMode.CONTAINS },
    menu_name : { value : null, matchMode : FilterMatchMode.CONTAINS },
    order_date : { value : null, matchMode: FilterMatchMode.DATE_IS},
    'status_info.value' : { value : null, matchMode : FilterMatchMode.EQUALS}
})

const order_date_filter = ref(null)

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

const get_colors = (data) => {
    if(data == 0) {
        return {
            backgroundColor: "rgba(255, 193, 7, 0.15)",
            borderColor: "rgba(255, 193, 7, 0.15)",
            pointBackgroundColor: "rgba(255, 193, 7, 1)",
            hover: "rgba(255, 193, 7, 1)"
        }
    } else if(data > 0) {
        return {
            backgroundColor: "rgba(34, 197, 94, .15)",
            borderColor: "rgba(34, 197, 94, .15)",
            pointBackgroundColor: "rgba(34, 197, 94, 1)",
            hover : "rgba(34, 197, 94, 1)"
        }
    } else {
        return {
            backgroundColor: "rgba(220, 38, 38, .15)",
            borderColor: "rgba(220, 38, 38, .15)",
            pointBackgroundColor: "rgba(220, 38, 38, 1)",
            hover : "rgba(220, 38, 38, 1)"
        }
    }
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
        "DOM"
    ],
    datasets: [
        {
            label: `Settimana ${moment().isoWeek()}`,
            fill: true,
            backgroundColor: get_colors(calculate_week_difference.value).backgroundColor,
            borderColor: get_colors(calculate_week_difference.value).borderColor,
            pointBackgroundColor: get_colors(calculate_week_difference.value).hover,
            pointBorderColor: get_colors(calculate_week_difference.value).pointBackgroundColor,
            pointHoverBackgroundColor: get_colors(calculate_week_difference.value).pointBackgroundColor,
            pointHoverBorderColor: get_colors(calculate_week_difference.value).hover,
            data: get_order_data_by_week(moment().format('YYYY-MM-DD')),
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
                    return `${context.parsed.y} € venduto`;
                },
            },
        },
    },
});

// Chart Total Earnings data
const totalEarningsData = reactive({
    labels: currentMonthDays,
    datasets: [
        {
            label: "Total Earnings",
            fill: true,
            backgroundColor: get_colors(calculate_month_difference.value).backgroundColor,
            borderColor: get_colors(calculate_month_difference.value).borderColor,
            pointBackgroundColor: get_colors(calculate_month_difference.value).hover,
            pointBorderColor: get_colors(calculate_month_difference.value).pointBackgroundColor,
            pointHoverBackgroundColor: get_colors(calculate_month_difference.value).pointBackgroundColor,
            pointHoverBorderColor: get_colors(calculate_month_difference.value).hover,
            data: filteredEarnings,
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
                    return `${context.parsed.y} €`;
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

const filter_products_by_date = () => {
    axios.get(route('products.filter-by-date', {
        filterType : productFilters.filterType, selectedDate : productFilters.selectedDate
    })).then(response => {
        productsFiltered.value = response.data.products;
        productCategories.value = response.data.categories;
        exportColumns.value = response.data.export_columns;
    });
}

const export_products_filtered = (data) => {
    const selectedDate = moment(productFilters.selectedDate).format('DD/MM/YYYY');
    const doc = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' });
    const pageWidth = doc.internal.pageSize.getWidth();
    const pageHeight = doc.internal.pageSize.getHeight();
    const margin = 10;
    const columnGap = 4;
    const columnCount = 5;
    const columnWidth = (pageWidth - (margin * 2) - (columnGap * (columnCount - 1))) / columnCount;
    const contentTop = 42;
    const contentBottom = pageHeight - 10;
    const categoryColors = [
        [37, 99, 235],
        [5, 150, 105],
        [217, 119, 6],
        [147, 51, 234],
    ];
    const columns = [
        { key: 'first_dishes', title: 'PRIMO PIATTO' },
        { key: 'second_dishes', title: 'SECONDO PIATTO' },
        { key: 'side_dishes', title: 'CONTORNO' },
        { key: 'special_menus', title: 'MENÙ SPECIALE' },
        { key: 'beverages', title: 'BIBITA' },
    ].map(column => ({
        ...column,
        products: data
            ? (column.key === 'special_menus'
                ? (data.is_special_menu
                    ? (exportColumns.value?.[column.key] || []).filter(menu => menu.id === data.special_menu_id)
                    : [])
                : (data.is_special_menu || column.key === 'beverages'
                    ? []
                    : (exportColumns.value?.[column.key] || []).filter(product => product.id === data.id)))
            : (exportColumns.value?.[column.key] || []),
    }));

    const drawPageHeader = () => {
        doc.setFillColor(15, 23, 42);
        doc.rect(0, 0, pageWidth, 29, 'F');
        doc.setTextColor(255, 255, 255);
        doc.setFont('helvetica', 'bold');
        doc.setFontSize(19);
        doc.text('Riepilogo ordini', margin, 12);
        doc.setFont('helvetica', 'normal');
        doc.setFontSize(10);
        doc.setTextColor(203, 213, 225);
        doc.text(`Data: ${selectedDate}`, margin, 20);
        doc.text('Dettaglio ordini', pageWidth - margin, 20, { align: 'right' });

        columns.forEach((column, index) => {
            const x = margin + index * (columnWidth + columnGap);
            const [red, green, blue] = categoryColors[index % categoryColors.length];
            doc.setFillColor(red, green, blue);
            doc.roundedRect(x, 34, columnWidth, 5, 1.5, 1.5, 'F');
            doc.setTextColor(255, 255, 255);
            doc.setFont('helvetica', 'bold');
            doc.setFontSize(8);
            doc.text(column.title, x + 3, 37.5);
            doc.setFont('helvetica', 'normal');
            doc.text(`${column.products.length}`, x + columnWidth - 3, 37.5, { align: 'right' });
        });
    };

    const addPage = () => {
        doc.addPage();
        drawPageHeader();
    };

    drawPageHeader();

    columns.forEach((column, columnIndex) => {
        let y = contentTop;

        column.products.forEach(product => {
            const [red, green, blue] = categoryColors[columnIndex % categoryColors.length];
            const customers = product.detail?.length
                ? product.detail.map(detail => detail.customer?.child || 'Cliente non indicato')
                : ['Nessun cliente'];
            // Reserve space on the right for the portions count so long names never overlap it.
            const quantityWidth = 20;
            const productTextWidth = columnWidth - 8 - quantityWidth;
            const productLines = doc.splitTextToSize(product.name || 'Prodotto non indicato', productTextWidth);
            const subtitleLines = product.product_name
                ? doc.splitTextToSize(product.product_name, columnWidth - 8)
                : [];
            const customerLines = customers.flatMap(customer => doc.splitTextToSize(`• ${customer}`, columnWidth - 8));
            const cardHeight = 13 + (productLines.length * 4) + (subtitleLines.length * 3.2) + (customerLines.length * 3.5) + 4;

            if (y + cardHeight > contentBottom) {
                addPage();
                y = contentTop;
            }

            const cardX = margin + columnIndex * (columnWidth + columnGap);
            doc.setFillColor(248, 250, 252);
            doc.setDrawColor(226, 232, 240);
            doc.roundedRect(cardX, y, columnWidth, cardHeight, 2, 2, 'FD');
            doc.setFillColor(red, green, blue);
            doc.roundedRect(cardX, y, 2.5, cardHeight, 1, 1, 'F');

            doc.setTextColor(15, 23, 42);
            doc.setFont('helvetica', 'bold');
            doc.setFontSize(9);
            const nameY = y + 6;
            doc.text(productLines, cardX + 5, nameY, { lineHeightFactor: 1.05 });

            const quantityY = y + 5.5;
            doc.setTextColor(red, green, blue);
            doc.setFontSize(9);
            doc.text(`${product.times_ordered || 0} porzioni`, cardX + columnWidth - 4, quantityY, { align: 'right' });

            doc.setTextColor(71, 85, 105);
            doc.setFont('helvetica', 'normal');
            doc.setFontSize(product.product_name ? 6.8 : 7.5);
            const subtitleY = nameY + (productLines.length * 4);
            if (subtitleLines.length)
                doc.text(subtitleLines, cardX + 5, subtitleY, { lineHeightFactor: 1.05 });
            const customersY = subtitleY + (subtitleLines.length ? subtitleLines.length * 3.2 + 2 : 2);
            doc.setFontSize(7.5);
            doc.text(customerLines, cardX + 5, customersY, { lineHeightFactor: 1.05 });
            y += cardHeight + 4;
        });
        if (column.products.length === 0) {
            const x = margin + columnIndex * (columnWidth + columnGap);
            doc.setTextColor(100, 116, 139);
            doc.setFont('helvetica', 'italic');
            doc.setFontSize(8);
            doc.text('Nessun prodotto', x + 3, y + 6);
        }
    });

    const title = data ? `Ordini Prodotto ${data.name} ${selectedDate}.pdf` : `Ordini Prodotti ${selectedDate}.pdf`;
    doc.save(title);
}

onMounted (() => {
    filter_products_by_date();
})
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
                <div class="mt-3 mt-md-0 ms-md-3 space-x-1 d-none">
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
                                    <dt class="fs-3 fw-bold" v-if="ordered_food.length" v-text="`${mostOrderedDish.name} (${mostOrderedDish.count})`" />
                                    <dt class="fs-3 fw-bold" v-else>Nessun dato disponibile</dt>
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
                <div class="col-sm-6 col-xxl-3" v-show="false">
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
                    <BaseBlock :title="paymentsView.find(view => view.id == currentPaymentsView).label" class="flex-grow-1 d-flex flex-column">
                        <template #options>
                            <div class="d-flex align-items-center" v-if="false">
                                <DatePicker 
                                    v-show="currentPaymentsView == 4" 
                                    selectionMode="range"
                                    :manualInput="false"
                                    placeholder="Seleziona un intervallo di date"
                                    class="w-100"
                                    inputClass="w-100"
                                    v-model="paymentsFilterDate"
                                    dateFormat="dd/mm/yy"
                                    @hide="console.log(paymentsFilterDate)"
                                />
                                <button type="button" class="btn btn-link" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="si si-settings"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <template v-for="view in paymentsView" :key="view.id">
                                        <button class="dropdown-item clickable" @click="currentPaymentsView = view.id">
                                            {{ view.label }}
                                        </button>
                                    </template>
                                </ul>
                            </div>
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
                                                {{ MoneyFormat.format(total_payments()) }}
                                            </dt>
                                            <dd class="fs-sm fw-medium text-muted mb-0">
                                                Totale assoluto pagato
                                            </dd>
                                        </dl>
                                    </div>
                                    <div class="col-sm-4">
                                        <dl class="mb-0">
                                            <dt class="fs-3 fw-bold d-inline-flex align-items-center space-x-2">
                                                {{ MoneyFormat.format(total_payments(1)) }}
                                            </dt>
                                            <dd class="fs-sm fw-medium text-muted mb-0">
                                                Totale assoluto con credito
                                            </dd>
                                        </dl>
                                    </div>
                                    <div class="col-sm-4">
                                        <dl class="mb-0">
                                            <dt class="fs-3 fw-bold d-inline-flex align-items-center space-x-2">
                                                {{ MoneyFormat.format(total_payments(3)) }}
                                            </dt>
                                            <dd class="fs-sm fw-medium text-muted mb-0">
                                                Totale assoluto con Stripe
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
                                                Totale ordini della settimana
                                            </dd>
                                        </dl>
                                        <div>
                                            <div
                                                class="d-inline-block px-2 py-1 rounded-3 fs-xs fw-semibold" v-tooltip.top="'Percentuale rispetto la settimana passata'" :class="{ 'text-danger bg-danger-light' : calculate_week_difference < 0, 'text-success bg-success-light' : calculate_week_difference > 0, 'text-warning bg-warning-light' : calculate_week_difference == 0}">
                                                <i class="fa me-1" :class="{ 'fa-caret-down' : calculate_week_difference < 0, 'fa-caret-up' : calculate_week_difference > 0, 'fa-minus' : calculate_week_difference == 0}"></i>
                                                {{ calculate_week_difference }} %
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
                                                Guadagno totale del mese
                                            </dd>
                                        </dl>
                                        <div>
                                            <div
                                                class="d-inline-block px-2 py-1 rounded-3 fs-xs fw-semibold" v-tooltip.top="'Percentuale rispetto il mese passato'" :class="{ 'text-danger bg-danger-light' : calculate_month_difference < 0, 'text-success bg-success-light' : calculate_month_difference > 0, 'text-warning bg-warning-light' : calculate_month_difference == 0}">
                                                <i class="fa me-1" :class="{ 'fa-caret-down' : calculate_month_difference < 0, 'fa-caret-up' : calculate_month_difference > 0, 'fa-minus' : calculate_month_difference == 0}"></i>
                                                {{ calculate_month_difference }} %
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
                        <div class="col-xl-12" style="visibility: hidden;">
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
            <BaseBlock :title="ordersViews.find(view => view.id == currentOrderView).label">
                <template #options>
                    <div class="d-flex justify-content-center">
                        <button class="btn btn-link" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="si si-settings"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <template v-for="view in ordersViews" :key="view.id">
                                <button class="dropdown-item clickable" @click="currentOrderView = view.id">
                                    {{ view.label }}
                                </button>
                            </template>
                        </ul>
                    </div>
                </template>
                <div class="content pt-0">
                    <DataTable
                        paginator
                        :rows="10"
                        :value="filteredOrders"
                        v-model:expandedRows="expandedRows"
                        v-model:filters="filters"
                        filterDisplay="row"
                        v-show="[1,2].includes(currentOrderView)"
                    >
                        <template #empty>
                            <div class="p-4 text-center">
                                <i class="fa fa-exclamation-triangle fa-2x"></i>
                                <p class="mt-2">Nessun ordine trovato</p>
                            </div>
                        </template>
                        <template #expansion="{ data }">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <template v-if="data.menu">
                                            <th>Primo piatto</th>
                                            <th>Secondo piatto</th>
                                            <th>Contorno</th>
                                        </template>
                                        <template v-else-if="data.special_menu">
                                            <th>Nome Menù</th>
                                            <th>Prodotto</th>
                                        </template>
                                        <th>Bibita</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <template v-if="data.menu">
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
                                        </template>
                                        <template v-else-if="data.special_menu">
                                            <td>
                                                <span v-if="data.special_menu" v-text="data.special_menu.name" />
                                                <span v-else>---</span>
                                            </td>
                                            <td>
                                                <span v-if="data.special_menu.product" v-text="data.special_menu.product.name" />
                                                <span v-else>---</span>
                                            </td>
                                        </template>
                                        <td>
                                            <span v-if="data.beverage" v-text="data.beverage.name" />
                                            <span v-else>---</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="p-4" v-if="false">
                                <h5>Prodotti nel menu</h5>
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <template v-if="data.menu">
                                                <th>Primo piatto</th>
                                                <th>Secondo piatto</th>
                                                <th>Contorno</th>
                                            </template>
                                            <template v-else-if="data.special_menu">
                                                <th>Nome Menù</th>
                                                <th>Prodotto</th>
                                            </template>
                                            <th>Bibita</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <template v-if="data.menu">
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
                                            </template>
                                            <template v-else-if="data.special_menu">
                                                <td>
                                                    <span v-if="data.special_menu" v-text="data.special_menu.name" />
                                                    <span v-else>---</span>
                                                </td>
                                                <td>
                                                    <span v-if="data.special_menu.product" v-text="data.special_menu.product.name" />
                                                    <span v-else>---</span>
                                                </td>
                                            </template>
                                            <td>
                                                <span v-if="data.beverage" v-text="data.beverage.name" />
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
                                <div class="d-flex align-items-center gap-2">
                                    <InputText 
                                        class="w-100"
                                        inputClass="w-100"
                                        v-model="filterModel.value"
                                        placeholder="Nome cliente"
                                        @input="filterCallback"
                                    />
                                    <button class="btn btn-link link-danger" type="button" @click.prevent="filterModel.value = null; filterCallback">
                                        <i class="fa fa-times"></i>
                                    </button>
                                </div>
                            </template>
                        </Column>
                        <Column style="width: 25%" header="Nome" field="menu_name" :showFilterMenu="false">
                            <template #filter="{ filterModel, filterCallback }">
                                <div class="d-flex align-items-center gap-2">
                                    <InputText 
                                        class="w-100"
                                        inputClass="w-100"
                                        v-model="filterModel.value"
                                        placeholder="Nome menù"
                                        @input="filterCallback"
                                    />
                                    <button class="btn btn-link link-danger" type="button" @click.prevent="filterModel.value = null; filterCallback">
                                        <i class="fa fa-times"></i>
                                    </button>
                                </div>
                            </template>
                        </Column>
                        <Column style="width: 20%" header="Giorno" field="order_date" :showFilterMenu="false">
                            <template #body="{ data }">
                                {{ moment(data.order_date).format('DD/MM/YYYY') }}
                            </template>
                            <template #filter v-if="currentOrderView == 1">
                                <div class="d-flex align-items-center gap-2">
                                    <DatePicker 
                                        inputClass="w-100"
                                        class="w-100"
                                        placeholder="Cerca per data"
                                        date-format="dd/mm/yy"
                                        v-model="order_date_filter"
                                    />
                                    <button class="btn btn-link link-danger" type="button" @click.prevent="order_date_filter = null">
                                        <i class="fa fa-times"></i>
                                    </button>
                                </div>
                            </template>
                        </Column>
                        <Column style="width: 20%" header="Stato" field="status_info.value" :showFilterMenu="false">
                            <template #body="{ data }">
                                <span :class="`badge text-bg-${data.status_info.color}`" v-text="data.status_info.label" />
                            </template>
                            <template #filter="{ filterModel, filterCallback }">
                                <div class="d-flex align-items-center gap-2">
                                    <Select 
                                        class="w-75"
                                        inputClass="w-75"
                                        placeholder="Cerca per stato"
                                        optionLabel="label"
                                        optionValue="value"
                                        v-model="filterModel.value"
                                        @change="filterCallback"
                                        :options="[
                                            { label: 'In attesa', value: 0 },
                                            { label: 'Pagato', value: 1 },
                                            { label: 'Completato', value: 3 }
                                        ]"
                                    />
                                    <button class="btn btn-link link-danger" type="button" @click.prevent="filterModel.value = null; filterCallback">
                                        <i class="fa fa-times"></i>
                                    </button>
                                </div>
                            </template>
                        </Column>
                    </DataTable>
                    <DataTable
                        paginator
                        :rows="10"
                        v-if="currentOrderView === 3"
                        :value="productsForOrderView"
                        v-model:expandedRows="productsExpandedRows"
                    >
                        <template #expansion="{ data }">
                            <div class="table-responsive customer-orders-table-wrapper">
                                <table class="table table-sm align-middle mb-0 customer-orders-table">
                                    <thead>
                                        <tr>
                                            <th>Cliente</th>
                                            <th>Note</th>
                                            <th class="text-end">Data ordine</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(detail, index) in data.detail" :key="index">
                                            <td>
                                                <span class="fw-medium" v-text="detail.customer?.child || 'Cliente non indicato'" />
                                            </td>
                                            <td>
                                                <span v-if="detail.notes" class="customer-note" v-text="detail.notes" />
                                                <span v-else class="text-muted">Nessuna nota</span>
                                            </td>
                                            <td class="text-end text-muted small">
                                                {{ moment(detail.order_date).format('DD/MM/YYYY') }}
                                            </td>
                                        </tr>
                                        <tr v-if="!data.detail?.length">
                                            <td colspan="3" class="text-center text-muted py-3">Nessun cliente associato</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </template>
                        <template #header>
                            <div class="row align-items-center">
                                <div class="pb-2 col-12 col-md-4 order-md-2 order-1">
                                    <div class="d-md-flex align-items-center">
                                        <label class="w-100 d-md-none text-center" for="filter-for">Filtra per:</label>
                                        <label class="w-25 d-md-inline d-none" for="filter-for">Filtra per:</label>
                                        <Select
                                            class="w-100"
                                            inputClass="w-100"
                                            :options="productFilters.selectOptions"
                                            optionLabel="label"
                                            optionValue="value"
                                            v-model="productFilters.filterType"
                                        />
                                    </div>
                                </div>
                                
                                <div class="pb-2 col-md-4 order-md-3 order-2">
                                    <DatePicker 
                                        class="w-100"
                                        inputClass="w-100"
                                        date-format="dd/mm/yy"
                                        v-model="productFilters.selectedDate"
                                        :placeholder="selectedDateType"
                                    />
                                </div>
                                <div class="pb-2 col-md-2 order-3">
                                    <button 
                                        class="btn btn-sm btn-alt-success w-100" 
                                        @click.prevent="filter_products_by_date"
                                        :disabled="!productFilters.selectedDate"
                                    >
                                        <i class="fa fa-search me-1"></i>
                                        Cerca
                                    </button>
                                </div>
                                <div class="pb-2 col-md-2 order-4">
                                    <button 
                                        class="btn btn-sm btn-alt-danger w-100"
                                        type="button"
                                        @click="export_products_filtered(null)"
                                    >
                                        <i class="fa fa-file-pdf me-1"></i>
                                        Esporta
                                    </button>
                                </div>
                            </div>
                        </template>
                        <template #empty>
                            <div class="p-4 text-center">
                                <i class="fa fa-exclamation-triangle fa-2x"></i>
                                <p class="mt-2">Nessun prodotto o menù speciale trovato</p>
                            </div>
                        </template>
                        <Column style="width: 5%" expander />
                        <Column style="width: 5%">
                            <template #body="{ data }">
                                <button 
                                    class="btn btn-link link-danger"
                                    type="button"
                                    @click="export_products_filtered(data)"
                                >
                                    <i class="fa fa-file-pdf"></i>
                                </button>
                            </template>
                        </Column>
                        <Column style="width: 45%" header="Nome" field="name" />
                        <Column style="width: 30%" header="Tipologia" field="type" />
                        <Column style="width: 15%" header="Quantità" field="times_ordered" />
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

    .text-warning {
        color: #ffc107 !important;
    }

    .bg-warning-light {
        background-color: rgba(255, 193, 7, 0.15) !important;
    }

    .customer-orders-panel {
        padding: 1rem;
        border: 1px solid #e2e8f0;
        border-radius: .75rem;
        background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
    }

    .customer-orders-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.25rem;
        height: 2.25rem;
        border-radius: 50%;
        color: #2563eb;
        background-color: #dbeafe;
    }

    .customer-orders-table-wrapper {
        border: 1px solid #e2e8f0;
        border-radius: .5rem;
        background-color: #fff;
    }

    .customer-orders-table thead th {
        padding: .65rem .75rem;
        color: #64748b;
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        background-color: #f8fafc;
        border-bottom-color: #e2e8f0;
    }

    .customer-orders-table tbody td {
        padding: .7rem .75rem;
        border-bottom-color: #f1f5f9;
    }

    .customer-orders-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .customer-avatar {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 1.8rem;
        height: 1.8rem;
        flex: 0 0 1.8rem;
        border-radius: 50%;
        color: #1d4ed8;
        font-size: .75rem;
        font-weight: 700;
        background-color: #dbeafe;
    }

    .customer-note {
        display: inline-block;
        max-width: 100%;
        padding: .25rem .5rem;
        border-radius: .35rem;
        color: #475569;
        font-size: .8rem;
        background-color: #f1f5f9;
    }
</style>
