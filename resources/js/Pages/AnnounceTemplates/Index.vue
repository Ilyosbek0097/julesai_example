<script setup>
import { ref, watch, h, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { NDataTable, NInput, NDatePicker, NButton, NSpace, NCard, NPagination, NIcon, NSelect, useMessage, NDropdown, NModal, NForm, NFormItem } from 'naive-ui';
import { PrintOutline as PrintIcon, DocumentTextOutline as ExcelIcon, EllipsisHorizontal as ActionsIcon, CreateOutline as EditIcon, PersonAddOutline as BatchEditIcon } from '@vicons/ionicons5';
import throttle from 'lodash/throttle';
import axios from 'axios';
import { saveAs } from 'file-saver';
import MyLayout from "@/Layouts/MyLayout.vue";

// Props
const props = defineProps({
    templates: { type: Object, required: true },
    cashboxes: { type: Array, required: true },
    filters: { type: Object, default: () => ({}) },
});

// Refs
const message = useMessage();
const loadingUpdate = ref(false); // For modal updates
const loadingExport = ref(false); // For Excel export
const loadingPrint = ref(false);  // For printing
const search = ref(props.filters.search);
const date = ref(props.filters.date);
const cashboxId = ref(props.filters.cashbox_id);
const checkedRowKeys = ref([]);

// --- Modal State ---
const showSingleEditModal = ref(false);
const showBatchEditModal = ref(false);
const singleEditPayerName = ref('');
const batchEditPayerName = ref('');
const currentTemplate = ref(null);

// --- Modal Logic ---
const openSingleEditModal = (row) => {
    currentTemplate.value = row;
    singleEditPayerName.value = row.payer_name || '';
    showSingleEditModal.value = true;
};

const openBatchEditModal = () => {
    batchEditPayerName.value = '';
    showBatchEditModal.value = true;
};

const handleSingleUpdate = async () => {
    loadingUpdate.value = true;
    try {
        await axios.post(route('announce-templates.update-payer', { announceTemplate: currentTemplate.value.announce_template_id }), {
            payer_name: singleEditPayerName.value,
        });
        message.success("Muvaffaqiyatli saqlandi!");
        showSingleEditModal.value = false;
        router.reload({ only: ['templates'] });
    } catch (error) {
        console.error("Xatolik:", error);
        message.error("Saqlashda xatolik yuz berdi.");
    } finally {
        loadingUpdate.value = false;
    }
};

const handleBatchUpdate = async () => {
    if (checkedRowKeys.value.length === 0) {
        message.warning("Hech qanday qator tanlanmagan.");
        return;
    }
    loadingUpdate.value = true;
    try {
        await axios.post(route('announce-templates.batch-update-payer'), {
            ids: checkedRowKeys.value,
            payer_name: batchEditPayerName.value,
        });
        message.success("Barcha tanlangan qatorlar muvaffaqiyatli saqlandi!");
        showBatchEditModal.value = false;
        checkedRowKeys.value = [];
        router.reload({ only: ['templates'] });
    } catch (error) {
        console.error("Xatolik:", error);
        message.error("Saqlashda xatolik yuz berdi.");
    } finally {
        loadingUpdate.value = false;
    }
};

// --- Action Handlers (Export/Print) ---
const templatesById = computed(() => {
    return props.templates.data.reduce((acc, template) => {
        acc[template.announce_template_id] = template;
        return acc;
    }, {});
});

const handleExport = async (ids) => {
    if (!ids || ids.length === 0) {
        message.warning("Eksport qilish uchun kamida bitta qator tanlanishi kerak.");
        return;
    }
    loadingExport.value = true;
    try {
        const response = await axios.post(route('announce-templates.export'), { ids }, { responseType: 'blob' });
        saveAs(response.data, 'e_lonlar.xlsx');
        message.success("Fayl muvaffaqiyatli eksport qilindi.");
    } catch (error) {
        console.error("Eksport qilishda xatolik:", error);
        message.error("Eksport qilishda xatolik yuz berdi.");
    } finally {
        loadingExport.value = false;
    }
};

const handlePrint = async (ids) => {
    if (!ids || ids.length === 0) {
        message.warning("Chop etish uchun kamida bitta qator tanlanishi kerak.");
        return;
    }
    for (const id of ids) {
        const template = templatesById.value[id];
        if (!template || !template.payer_name) {
            message.error(`Siz №${template.docnumb} operatsiyada pul topshirgan shaxsni kiritmagansiz!`);
            return;
        }
    }
    loadingPrint.value = true;
    try {
        const response = await axios.post(route('announce-templates.print'), { ids });
        const printFrame = document.createElement('iframe');
        printFrame.style.display = 'none';
        document.body.appendChild(printFrame);
        printFrame.contentDocument.write(response.data);
        printFrame.contentDocument.close();
        printFrame.contentWindow.print();
        document.body.removeChild(printFrame);
        message.success("Hujjat chop etishga yuborildi.");
    } catch (error) {
        console.error("Chop etishda xatolik:", error);
        message.error("Chop etishda xatolik yuz berdi.");
    } finally {
        loadingPrint.value = false;
    }
};

const handleRowAction = (key, row) => {
    if (key === 'excel') handleExport([row.announce_template_id]);
    else if (key === 'print') handlePrint([row.announce_template_id]);
    else if (key === 'edit') openSingleEditModal(row);
};

// --- Data Table Columns ---
const columns = [
    { type: 'selection', key: 'selection' },
    { title: 'Hujjat Raqami', key: 'docnumb', sorter: 'default' },
    { title: 'Mijoz Nomi', key: 'clname', sorter: 'default' },
    { title: 'Pul topshirgan shaxs', key: 'payer_name' },
    { title: 'Summa', key: 'sumpay', sorter: 'default', render: (row) => new Intl.NumberFormat('fr-FR').format(row.sumpay) },
    { title: 'Sana', key: 'currday', sorter: 'default', render: (row) => new Date(row.currday).toLocaleDateString() },
    {
        title: 'Amallar',
        key: 'actions',
        render(row) {
            return h( NDropdown, { trigger: 'click', onSelect: (key) => handleRowAction(key, row), options: [ { label: 'Tahrirlash', key: 'edit', icon: () => h(NIcon, null, { default: () => h(EditIcon) }) }, { label: 'Excelga Yuklash', key: 'excel', icon: () => h(NIcon, null, { default: () => h(ExcelIcon) }) }, { label: 'Chop etish', key: 'print', icon: () => h(NIcon, null, { default: () => h(PrintIcon) }) } ] }, { default: () => h(NButton, { size: 'small', circle: true }, { icon: () => h(NIcon, null, { default: () => h(ActionsIcon) }) }) } );
        }
    }
];

// --- Watchers & Pagination ---
watch([search, date, cashboxId], throttle(() => {
    router.get(route('announce-templates.index'), {
        search: search.value,
        date: date.value,
        cashbox_id: cashboxId.value,
    }, { preserveState: true, replace: true });
}, 300));

const handlePageChange = (page) => router.get(props.templates.path, { page }, { preserveState: true });

</script>

<template>
    <MyLayout>
        <Head title="E'lonlar Shabloni" />
        <div class="p-4 sm:p-6 lg:p-8">
            <NCard title="E'lonlar ro'yxati">
                <template #header-extra>
                    <NSpace>
                        <NButton secondary :disabled="checkedRowKeys.length === 0" @click="openBatchEditModal" :loading="loadingUpdate">
                            <template #icon><NIcon><BatchEditIcon /></NIcon></template>
                            To'lovchini belgilash
                        </NButton>
                        <NButton type="primary" ghost :disabled="checkedRowKeys.length === 0" @click="() => handlePrint(checkedRowKeys)" :loading="loadingPrint">
                            <template #icon><NIcon><PrintIcon /></NIcon></template>
                            Chop etish
                        </NButton>
                        <NButton type="primary" :disabled="checkedRowKeys.length === 0" @click="() => handleExport(checkedRowKeys)" :loading="loadingExport">
                            <template #icon><NIcon><ExcelIcon /></NIcon></template>
                            Excelga Yuklash
                        </NButton>
                    </NSpace>
                </template>

                <NSpace :vertical="true" :size="12" class="mb-4">
                    <NSpace>
                        <NSelect v-model:value="cashboxId" :options="cashboxes" placeholder="Kassani tanlang" clearable filterable style="width: 250px;" />
                        <NInput v-model:value="search" placeholder="Qidiruv..." clearable style="width: 300px;" />
                        <NDatePicker v-model:formatted-value="date" value-format="yyyy-MM-dd" type="date" clearable placeholder="Sanani tanlang" />
                    </NSpace>
                </NSpace>

                <NDataTable :columns="columns" :data="templates.data" :bordered="false" :single-line="false" :remote="true" v-model:checked-row-keys="checkedRowKeys" :row-key="row => row.announce_template_id" />

                <div v-if="templates.total > templates.per_page" class="flex justify-center mt-4">
                     <NPagination :item-count="templates.total" :page="templates.current_page" :page-size="templates.per_page" @update:page="handlePageChange" show-quick-jumper>
                        <template #prefix="{ itemCount }">Jami: {{ itemCount }}</template>
                     </NPagination>
                </div>
            </NCard>
        </div>

        <NModal v-model:show="showSingleEditModal" preset="card" style="width: 600px;" title="Pul topshirgan shaxsni tahrirlash">
            <NForm @submit.prevent="handleSingleUpdate">
                <NFormItem label="Pul topshirgan shaxs F.I.O">
                    <NInput v-model:value="singleEditPayerName" placeholder="Ism-sharifni kiriting" />
                </NFormItem>
                <NSpace justify="end">
                    <NButton @click="showSingleEditModal = false">Bekor qilish</NButton>
                    <NButton type="primary" attr-type="submit" :loading="loadingUpdate">Saqlash</NButton>
                </NSpace>
            </NForm>
        </NModal>

        <NModal v-model:show="showBatchEditModal" preset="card" style="width: 600px;" title="Tanlangan qatorlar uchun pul topshirgan shaxsni belgilash">
            <NForm @submit.prevent="handleBatchUpdate">
                <NFormItem label="Pul topshirgan shaxs F.I.O">
                    <NInput v-model:value="batchEditPayerName" placeholder="Ism-sharifni kiriting" />
                </NFormItem>
                <NSpace justify="end">
                    <NButton @click="showBatchEditModal = false">Bekor qilish</NButton>
                    <NButton type="primary" attr-type="submit" :loading="loadingUpdate">Barchasiga saqlash</NButton>
                </NSpace>
            </NForm>
        </NModal>

    </MyLayout>
</template>
