import './bootstrap';
import { createApp } from 'vue';
import MasterHierarki from './components/MasterHierarki.vue';
import MasterVendor from './components/MasterVendor.vue';
import MasterProduk from './components/MasterProduk.vue';
import PurchaseOrder from './components/PurchaseOrder.vue';
import ReceivingGood from './components/ReceivingGood.vue';
import WarehousePanel from './components/WarehousePanel.vue';
import EventHistoryPanel from './components/EventHistoryPanel.vue';
import EventManagePanel from './components/EventManagePanel.vue';
import ReportPanel from './components/ReportPanel.vue';

const appElement = document.getElementById('master-hierarki-app');
if (appElement) {
    createApp(MasterHierarki).mount('#master-hierarki-app');
}

const vendorAppElement = document.getElementById('master-vendor-app');
if (vendorAppElement) {
    createApp(MasterVendor).mount('#master-vendor-app');
}

const produkAppElement = document.getElementById('master-produk-app');
if (produkAppElement) {
    createApp(MasterProduk).mount('#master-produk-app');
}

const poAppElement = document.getElementById('purchase-order-app');
if (poAppElement) {
    createApp(PurchaseOrder).mount('#purchase-order-app');
}

const warehouseAppElement = document.getElementById('warehouse-app');
if (warehouseAppElement) {
    createApp(WarehousePanel).mount('#warehouse-app');
}

const receivingAppElement = document.getElementById('receiving-good-app');
if (receivingAppElement) {
    createApp(ReceivingGood).mount('#receiving-good-app');
}

const eventHistoryAppElement = document.getElementById('event-history-app');
if (eventHistoryAppElement) {
    createApp(EventHistoryPanel).mount('#event-history-app');
}

const eventManageAppElement = document.getElementById('event-manage-app');
if (eventManageAppElement) {
    createApp(EventManagePanel).mount('#event-manage-app');
}

const reportAppElement = document.getElementById('report-app');
if (reportAppElement) {
    createApp(ReportPanel).mount('#report-app');
}
