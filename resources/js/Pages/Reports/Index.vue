<script setup>
import MyLayout from "@/Layouts/MyLayout.vue";
import { Head } from "@inertiajs/vue3";
import { useI18n } from "vue-i18n";
import {
    NCard, NBreadcrumb, NBreadcrumbItem, NForm, NFormItem, NDatePicker,
    NSelect, NButton, NSpace, NDataTable, useMessage
} from "naive-ui";
import { ref, computed } from "vue";
import axios from 'axios';

const { t } = useI18n();
const message = useMessage();

const props = defineProps({
    cashBoxes: {
        type: Object,
        required: true
    }
});

const cashboxOptions = computed(() => props.cashBoxes);
const formRef = ref(null);
const loading = ref(false);
const reportData = ref([]);

const formModel = ref({
    dateRange: null,
    reportType: null,
    cashboxId: null,
});

const reportTypeOptions = [
    { label: 'Kirim Hisoboti', value: 'income' },
    { label: 'Chiqim Hisoboti', value: 'expense' },
    { label: 'Umumiy Hisobot', value: 'summary' },
];

const columns = computed(() => {
    const type = formModel.value.reportType;

    if (type === 'income' || type === 'expense') {
        return [
            { title: 'Sana', key: 'date' },
            { title: 'Tavsif', key: 'description' },
            { title: 'Kategoriya', key: 'category' },
            {
                title: 'Summa',
                key: 'amount',
                render: (row) => new Intl.NumberFormat('uz-UZ').format(row.amount)
            },
        ];
    }

    if (type === 'summary') {
        return [
            { title: 'Ko\'rsatkich', key: 'type' },
            {
                title: 'Jami Summa',
                key: 'total_amount',
                render: (row) => new Intl.NumberFormat('uz-UZ').format(row.total_amount)
            },
        ];
    }

    return [];
});

const rules = {
    cashboxId: { required: true,  message: 'Iltimos Kassani tanlang', trigger: 'change', type: 'number' },
    dateRange: { required: true, message: 'Iltimos, sana oralig\'ini tanlang', trigger: 'change', type: 'array' },
    reportType: { required: true, message: 'Iltimos, hisobot turini tanlang', trigger: 'change' },
};

const handleFormSubmit = () => {
    formRef.value?.validate(async (errors) => {
        if (errors) {
            message.error("Iltimos, formalarni to'g'ri to'ldiring.");
            return;
        }
        loading.value = true;
        reportData.value = [];
        try {
            const response = await axios.post('/reports/fetch', {
                start_date: formModel.value.dateRange[0],
                end_date: formModel.value.dateRange[1],
                report_type: formModel.value.reportType,
                cashbox_id: formModel.value.cashboxId, // Added cashboxId to the request
            });
            reportData.value = response.data;
            if (reportData.value.length === 0) {
                message.info("Tanlangan oraliqda ma'lumot topilmadi.");
            }
        } catch (error) {
            console.error("Error fetching report data:", error);
            message.error("Hisobot ma'lumotlarini olishda xatolik yuz berdi.");
        } finally {
            loading.value = false;
        }
    });
};

</script>

<template>
    <MyLayout>
        <Head title="Hisobotlar" />
        <n-card class="min-h-screen">
             <n-breadcrumb class="mb-4">
                <n-breadcrumb-item :href="route('dashboard')">{{ t("Sidebar.home") }}</n-breadcrumb-item>
                <n-breadcrumb-item>Hisobotlar</n-breadcrumb-item>
            </n-breadcrumb>

            <n-card title="Hisobot Filtrlari">
                <n-form ref="formRef" :model="formModel" :rules="rules" @submit.prevent="handleFormSubmit">
                    <n-space align="end">
                          <n-form-item label="Kassa" path="cashboxId">
                            <n-select
                                v-model:value="formModel.cashboxId"
                                :options="cashboxOptions"
                                placeholder="Kassani Tanlang"
                                style="width: 400px;"
                                clearable
                                filterable
                            />
                        </n-form-item>
                        <n-form-item label="Sana Oralig'i" path="dateRange">
                            <n-date-picker v-model:value="formModel.dateRange" type="daterange" clearable />
                        </n-form-item>
                        <n-form-item label="Hisobot Turi" path="reportType">
                            <n-select
                                v-model:value="formModel.reportType"
                                :options="reportTypeOptions"
                                placeholder="Hisobot turini tanlang"
                                style="width: 200px;"
                                clearable
                            />
                        </n-form-item>
                        <n-form-item>
                            <n-button type="primary" attr-type="submit" :loading="loading">
                                Hisobotni Ko'rish
                            </n-button>
                        </n-form-item>
                    </n-space>
                </n-form>
            </n-card>

            <n-card class="mt-4" title="Hisobot Natijalari">
                <n-data-table
                    :columns="columns"
                    :data="reportData"
                    :loading="loading"
                    :bordered="false"
                    :single-line="false"
                />
            </n-card>
        </n-card>
    </MyLayout>
</template>
