<script setup>
import {CheckmarkCircleOutline} from "@vicons/ionicons5";
import {computed, ref} from "vue";
import {useNumberFormatStore} from "@/Stores/numberFormatStore.js";
import {useNumberTextStore} from "@/Stores/numberTextStore.js";
import {router} from "@inertiajs/vue3";
import {useMessage} from "naive-ui";
import BaseSpinner from "@/Components/MyComponent/BaseSpinner.vue";

const formatNumber = useNumberFormatStore();
const textNumber = useNumberTextStore();
const message = useMessage();
const loading = ref(false);

const props = defineProps({
    rowData: {
        type: Object,
        required: true,
        default: () => ({})
    },
    operationSigns: {
        type: Object,
        required: true,
        default: () => ({}),
    }
});

const filteredSigns = computed(() => {
    return Object.values(props.operationSigns).filter(
        item =>
            item.operation_id === props.rowData.id &&
            item.template_id === props.rowData.template_id
    );
});

const formatDate = (iso) => {
    const date = new Date(iso);
    return `${date.getDate().toString().padStart(2, '0')}-${(date.getMonth() + 1).toString().padStart(2, '0')}-${date.getFullYear()} ${date.getHours().toString().padStart(2, '0')}:${date.getMinutes().toString().padStart(2, '0')}`;
}

const handleSignCheck = (signType) => {
    return filteredSigns.value.find(item => item.sign_type === signType) || null;
}

const formattedDate = (dateValue) => {
    if (!dateValue) return ''
    const parts = dateValue.split('-')
    if (parts.length !== 3) return ''

    const year = parseInt(parts[0], 10)
    const month = parseInt(parts[1], 10) - 1
    const day = parseInt(parts[2], 10)

    if (isNaN(year) || isNaN(month) || isNaN(day)) return ''

    const months = [
        'yanvar', 'fevral', 'mart', 'aprel', 'may', 'iyun',
        'iyul', 'avgust', 'sentabr', 'oktabr', 'noyabr', 'dekabr'
    ]

    if (month < 0 || month > 11) return ''

    return `${year}-yil ${day}-${months[month]}`
}

const handleSign = (signType) => {
    if (signType) {
        router.post(route('bank-operations.operationSignSave'), {
                operation_id: props?.rowData?.id,
                sign_type: signType,
                cashbox_id: props?.rowData?.local_cashbox_id,
                template_id: props?.rowData?.template_id,
            }, {
                onBefore: () => {
                    loading.value = true;
                },
                onSuccess: () => {
                    message.success('Imzo muvaffaqiyatli tasdiqlandi!');
                },
                onError: (errors) => {
                    console.error('Xatolik', errors)
                    message.error('Xatolik: ' + errors.message)
                },
                onFinish: () => {
                    loading.value = false;
                }
            }
        )
    }
}
</script>

<template>
    <div id="print-content" class="print-container">
        <BaseSpinner :show="loading" class="no-print-spinner">
            <table class="border-2 border-black w-full text-sm operation-table">
                <thead>
                <tr></tr>
                </thead>
                <tbody>
                <tr class="h-[25px]">
                    <td class="w-[5%]"></td>
                    <td class="w-[10%]"></td>
                    <td class="w-[15%]"></td>
                    <td class="w-[15%]"></td>
                    <td class="w-[10%]"></td>
                    <td class="w-[5%]"></td>
                    <td class="w-[10%] text-end font-bold text-lg">27-ilova</td>
                    <td class="w-[5%]"></td>
                    <td class="w-[15%]"></td>
                    <td class="w-[5%]"></td>
                </tr>
                <tr class="h-[25px]">
                    <td></td>
                    <td rowspan="2" colspan="4" class="font-bold">
                        "KOʻZDA TUTILMAGAN XOLATLAR XISOB-<br>
                        VARAGʻI BOʻYICHA CHIQIM KASSA ORDERI"
                    </td>
                    <td></td>
                    <td class="border-2 border-black font-bold">№ {{ props?.rowData?.doc_num }}</td>
                    <td></td>
                    <td class="border-2 border-black font-bold text-center">Boshqaruvchi:</td>
                    <td></td>
                </tr>
                <tr class="h-[35px]">
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td class="text-center border-2 border-black">
                        <n-button
                            v-if="handleSignCheck('sign4') === null"
                            type="success"
                            size="small"
                            class="w-[80%] align-middle no-print"
                            @click="handleSign('sign4')"
                        >
                            <n-icon size="20">
                                <CheckmarkCircleOutline/>
                            </n-icon>
                        </n-button>
                        <n-tag v-else type="success" size="small" class="text-center print-sign">
                            <span style="font-size: 0.85em;">
                                {{ handleSignCheck('sign4')?.users?.name }}<br>
                                <small>{{ formatDate(handleSignCheck('sign4')?.signed_at) }}</small>
                            </span>
                        </n-tag>
                    </td>
                    <td></td>
                </tr>
                <tr class="h-[25px]">
                    <td colspan="10"></td>
                </tr>
                <tr class="h-[25px]">
                    <td></td>
                    <td colspan="2">
                        {{ formattedDate(props?.rowData?.doc_date) }}
                    </td>
                    <td colspan="2" class="font-bold text-center">
                        DEBET
                    </td>
                    <td></td>
                    <td colspan="3" class="font-bold text-center border-2 border-black">Summa</td>
                    <td></td>
                </tr>
                <tr class="h-[25px]">
                    <td></td>
                    <td rowspan="3" class="font-bold">
                        Bank nomi:
                    </td>
                    <td rowspan="3" class="font-bold italic text-center">
                        {{ props?.rowData?.local_cashbox?.label ?? '' }}
                    </td>
                    <td class="border-2 border-black text-center text-red-600 font-bold" colspan="2" rowspan="2">
                        {{ props?.rowData?.recipient_account ?? '' }}
                    </td>
                    <td></td>
                    <td colspan="3" rowspan="2" class="text-center text-red-600 border-2 border-black">
                        {{ formatNumber.formatNumber(formatNumber.parseNumber(props?.rowData?.amount)) ?? '' }}
                    </td>
                    <td></td>
                </tr>
                <tr class="h-[25px]">
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr class="h-[25px]">
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td class="border-2 border-black">Xususan</td>
                    <td colspan="2" class="border-2 border-black">Kod</td>
                    <td></td>
                </tr>
                <tr class="h-[30px]">
                    <td></td>
                    <td>Bank kodi:</td>
                    <td class="border-2 border-black text-center font-bold">00083</td>
                    <td class="font-bold text-center">
                        BXM kodi
                    </td>
                    <td class="text-blue-800">
                        {{ props?.rowData?.local_cashbox?.branch }}
                    </td>
                    <td></td>
                    <td class="border-2 border-black"></td>
                    <td colspan="2" class="border-2 border-black"></td>
                    <td></td>
                </tr>
                <tr class="h-[25px]">
                    <td></td>
                    <td></td>
                    <td></td>
                    <td colspan="2" class="font-bold text-center">Kredit</td>
                    <td></td>
                    <td class="border-2 border-black"></td>
                    <td colspan="2" class="border-2 border-black"></td>
                    <td></td>
                </tr>
                <tr class="h-[25px]">
                    <td></td>
                    <td rowspan="3" class="font-bold">
                        Bank nomi:
                    </td>
                    <td rowspan="3" class="font-bold italic text-center">
                        {{ props?.rowData?.cashbox?.label ?? '' }}
                    </td>
                    <td colspan="2" rowspan="2" class="border-2 border-black text-center text-red-600 font-bold">
                        {{ props?.rowData?.sender_account }}
                    </td>
                    <td></td>
                    <td class="border-2 border-black"></td>
                    <td colspan="2" class="border-2 border-black"></td>
                    <td></td>
                </tr>
                <tr class="h-[25px]">
                    <td></td>
                    <td></td>
                    <td class="border-2 border-black"></td>
                    <td colspan="2" class="border-2 border-black"></td>
                    <td></td>
                </tr>
                <tr class="h-[25px]">
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td class="border-2 border-black"></td>
                    <td colspan="2" class="border-2 border-black"></td>
                    <td></td>
                </tr>
                <tr class="h-[30px]">
                    <td></td>
                    <td>Bank kodi:</td>
                    <td class="border-2 border-black font-bold text-center">00083</td>
                    <td class="text-center font-bold">BXM kodi</td>
                    <td colspan="2" class="text-blue-800">{{ props?.rowData?.cashbox?.branch }}</td>
                    <td class="border-2 border-black">Operatsiya turi</td>
                    <td class="border-2 border-black" colspan="2"></td>
                    <td></td>
                </tr>
                <tr class="h-[25px]">
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td class="border-2 border-black"> Bank guruxi №</td>
                    <td class="border-2 border-black" colspan="2"></td>
                    <td></td>
                </tr>
                <tr class="h-[25px]">
                    <td></td>
                    <td class="border-b border-black"></td>
                    <td class="border-b border-black"></td>
                    <td class="border-b border-black"></td>
                    <td class="border-b border-black"></td>
                    <td class="border-b border-black"></td>
                    <td class="border-b border-black"></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr class="h-[25px]">
                    <td></td>
                    <td>Summa so'z bilan:</td>
                    <td class="text-red-600" colspan="7">
                        {{ textNumber.numberToTextLotin(formatNumber.parseNumber(props?.rowData?.amount)) }} so'm
                    </td>
                    <td></td>
                </tr>
                <tr class="h-[25px]">
                    <td colspan="10"></td>
                </tr>
                <tr class="h-[25px]">
                    <td></td>
                    <td>Chiqim maqsadi:</td>
                    <td colspan="7">{{ props?.rowData?.purpose_name }}</td>
                    <td></td>
                </tr>
                <tr class="h-[25px]">
                    <td colspan="10"></td>
                </tr>
                <tr class="h-[25px]">
                    <td></td>
                    <td class="font-bold">Bosh buxgalter:</td>
                    <td>
                        <n-button
                            v-if="handleSignCheck('sign1') === null"
                            type="success"
                            size="small"
                            class="w-[60%] align-middle no-print"
                            @click="handleSign('sign1')"
                        >
                            <n-icon size="20">
                                <CheckmarkCircleOutline/>
                            </n-icon>
                        </n-button>
                        <n-tag v-else type="success" size="small" class="text-center print-sign">
                            <span style="font-size: 0.85em;">
                                {{ handleSignCheck('sign1')?.users?.name }}<br>
                                <small>{{ formatDate(handleSignCheck('sign1')?.signed_at) }}</small>
                            </span>
                        </n-tag>
                    </td>
                    <td class="text-center font-bold">
                        Buxgalter:
                    </td>
                    <td>
                        <n-button
                            v-if="handleSignCheck('sign2') === null"
                            type="success"
                            size="small"
                            class="w-[100%] align-middle no-print"
                            @click="handleSign('sign2')"
                        >
                            <n-icon size="20">
                                <CheckmarkCircleOutline/>
                            </n-icon>
                        </n-button>
                        <n-tag v-else type="success" size="small" class="text-center print-sign">
                            <span style="font-size: 0.85em;">
                                {{ handleSignCheck('sign2')?.users?.name }}<br>
                                <small>{{ formatDate(handleSignCheck('sign2')?.signed_at) }}</small>
                            </span>
                        </n-tag>
                    </td>
                    <td></td>
                    <td class="font-bold text-center">Kassa mudiri:</td>
                    <td colspan="2">
                        <n-button
                            v-if="handleSignCheck('sign3') === null"
                            type="success"
                            size="small"
                            class="w-[50%] align-middle no-print"
                            @click="handleSign('sign3')"
                        >
                            <n-icon size="20">
                                <CheckmarkCircleOutline/>
                            </n-icon>
                        </n-button>
                        <n-tag v-else type="success" size="small" class="text-center print-sign">
                            <span style="font-size: 0.85em;">
                                {{ handleSignCheck('sign3')?.users?.name }}<br>
                                <small>{{ formatDate(handleSignCheck('sign3')?.signed_at) }}</small>
                            </span>
                        </n-tag>
                    </td>
                </tr>
                <tr class="h-[25px]">
                    <td colspan="10"></td>
                </tr>
                </tbody>
            </table>
        </BaseSpinner>
    </div>
</template>

<style>
/* Ekran ko'rinishi */
.print-container {
    width: 100%;
    overflow-x: auto;
}

/* Print stillari */
@media print {
    /* Sahifa sozlamalari */
    @page {
        size: A4 portrait;
        margin: 10mm;
    }

    /* Print container */
    .print-container {
        width: 390mm;
        height: auto;
        transform: scale(0.48);
        transform-origin: top left;
        position: absolute;
        top: 0;
        left: 0;
    }

    /* Table uchun page break yo'q */
    .operation-table {
        font-size: 10px !important;
        border-collapse: collapse !important;
        width: 100% !important;
        page-break-before: avoid !important;
        page-break-after: avoid !important;
        page-break-inside: avoid !important;
    }

    .operation-table tbody {
        page-break-inside: avoid !important;
    }

    .operation-table tr {
        page-break-inside: avoid !important;
        page-break-before: avoid !important;
        page-break-after: avoid !important;
    }

    .operation-table td {
        border-color: black !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    .operation-table .border-2 {
        border-width: 2px !important;
        border-color: black !important;
    }

    .operation-table .border-black {
        border-color: black !important;
    }

    .operation-table .border-b {
        border-bottom: 1px solid black !important;
    }

    /* Balandlik sozlamalari */
    .h-\[25px\] {
        height: 20px !important;
    }

    .h-\[30px\] {
        height: 24px !important;
    }

    .h-\[35px\] {
        height: 28px !important;
    }

    /* Buttonlarni yashirish */
    .no-print {
        display: none !important;
    }

    /* Imzolarni ko'rsatish */
    .print-sign {
        background-color: #f0f9ff !important;
        border: 1px solid #3b82f6 !important;
        padding: 2px 4px !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        font-size: 8px !important;
    }

    /* Ranglarni saqlash */
    .text-red-600 {
        color: #dc2626 !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    .text-blue-800 {
        color: #1e40af !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    /* Font qalinliklarini saqlash */
    .font-bold {
        font-weight: 700 !important;
    }

    /* Text o'lchamlarini moslash */
    .text-lg {
        font-size: 1rem !important;
    }

    .text-sm {
        font-size: 0.8rem !important;
    }

    /* BaseSpinner komponentini olib tashlash */
    :deep(.n-spin-container) {
        position: static !important;
        min-height: auto !important;
    }

    :deep(.n-spin-content) {
        position: static !important;
    }

    :deep(.n-spin-body) {
        display: none !important;
    }
    :deep(.no-print) {
        display: none !important;
    }
}
</style>