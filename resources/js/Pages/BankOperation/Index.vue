<script setup>
import MyLayout from "@/Layouts/MyLayout.vue";
import {computed, h, ref} from "vue";
import {InformationCircleOutline, PrintOutline} from "@vicons/ionicons5";
import {NButton, NIcon} from "naive-ui";
import {useNumberFormatStore} from "@/Stores/numberFormatStore.js";
import OperationTemplate from "@/Pages/BankOperation/OperationTemplate.vue";

const numberFormat = useNumberFormatStore();
const showModal = ref(false);
const rowData = ref(null);

const props = defineProps({
    operations: {
        type: Array,
        required: true,
        default: []
    },
    operationSigns: {
        type: Array,
        required: true,
        default: []
    }
})

const safeOperations = computed(() => {
    return Array.isArray(props.operations) ? props.operations : [];
});

const operationColumn = computed(() => [
    {
        title: 'ID',
        key: 'id'
    },
    {
        title: 'Transaction ID',
        key: 'transaction_id'
    },
    {
        title: 'Lokal Kassa',
        key: 'local_cashbox.label',
        render: (row) => row.local_cashbox?.label || '-'
    },
    {
        title: 'Document №',
        key: 'doc_num'
    },
    {
        title: 'Summa',
        key: 'amount',
        render(row) {
            return numberFormat.formatNumber(numberFormat.parseNumber(row.amount));
        }
    },
    {
        title: 'Amallar',
        key: 'actions',
        render(row) {
            return h('div', {style: 'display: flex; gap: 8px'}, [
                h(
                    NButton,
                    {
                        size: 'small',
                        type: 'info',
                        onClick: () => handleInfo(row),
                        circle: true
                    },
                    {
                        icon: () =>
                            h(NIcon, null, {default: () => h(InformationCircleOutline)})
                    }
                )
            ])
        }
    }
]);

const handleInfo = (row) => {
    showModal.value = true;
    rowData.value = row;
}

const handlePrint = () => {
    if (rowData.value) {
        // Biroz kutib print qilamiz
        setTimeout(() => {
            window.print();
        }, 200);
    }
}
</script>

<template>
    <MyLayout>
        <n-card class="no-print">
            <n-grid cols="12" x-gap="12" y-gap="12">
                <n-gi span="12">
                    <n-gradient-text>Operatsiyalarni tasdiqlash</n-gradient-text>
                </n-gi>
                <n-gi span="12">
                    <n-data-table
                        :columns="operationColumn"
                        :data="props?.operations"
                    />
                </n-gi>
            </n-grid>
        </n-card>

        <n-modal
            v-model:show="showModal"
            style="width: 90%; height:95vh"
            class="print-modal"
            preset="card"
            draggable
            title="Chiqim Kassa Orderi">
            <template #header-extra>
                <n-button
                    @click="handlePrint"
                    type="primary"
                    class="no-print">
                    <template #icon>
                        <n-icon>
                            <PrintOutline />
                        </n-icon>
                    </template>
                    Chop etish
                </n-button>
            </template>
            <OperationTemplate
                v-if="rowData"
                :rowData="rowData"
                :operationSigns="props?.operationSigns"
            />
        </n-modal>
    </MyLayout>
</template>

<style scoped>

@media screen {
    .print-modal {
        width: 90%;
        height: 95vh;
    }
}
</style>