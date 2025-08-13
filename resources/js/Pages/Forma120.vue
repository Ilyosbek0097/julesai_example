<script setup>
import MyLayout from "@/Layouts/MyLayout.vue";
import { Head, router } from "@inertiajs/vue3";
import { useI18n } from "vue-i18n";
import { computed, h, ref } from "vue";
import { CheckmarkCircleOutline as Check, AddOutline as Plus, RemoveOutline as Minus, Reload, Alert, TrashOutline } from "@vicons/ionicons5";
import axios from "axios";
import { NAlert, NButton, NCard, NForm, NFormItem, NGrid, NGi, NIcon, NInputNumber, NSelect, NSpace, NTable, NText, useMessage, NDatePicker } from "naive-ui";
import { useNumberFormatStore } from "@/Stores/numberFormatStore.js";
import {useAuthStore} from "@/Stores/AuthStore.js";
import  BaseSpinner from "@/Components/MyComponent/BaseSpinner.vue";
// Utils
const formatDate = (inputDate, type = 'date') => {
    if (!inputDate) return '-';
    const date = new Date(inputDate);
    const [day, month, year] = [date.getDate(), date.getMonth() + 1, date.getFullYear()].map(n => String(n).padStart(2, '0'));
    if (type === 'datetime') {
        const [hours, minutes, seconds] = [date.getHours(), date.getMinutes(), date.getSeconds()].map(n => String(n).padStart(2, '0'));
        return `${day}.${month}.${year} ${hours}:${minutes}:${seconds}`;
    }
    return `${day}.${month}.${year}`;
};
const numberFormat = num => (num ?? 0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ');

// State va Store
const authStore = useAuthStore();
const numberStore = useNumberFormatStore();
const { t } = useI18n();
const message = useMessage();
const props = defineProps({ cash_boxes: { type: Object, default: () => [] } });
const cash_boxes = ref(props.cash_boxes || []);
const cashBoxChange = ref(null);
const filterDate = ref(null);
const cash_banknotes = ref(0);
const cash_coins = ref(0);
const forma120Data = ref(null);
const isBlock = ref(false);
const isContentVisible = ref(false);
const loading = ref(false);


const optionsCashBoxes = computed(() => {
    if (!authStore.user?.local_code) {
        return [];
    }

    const mapItem = item => ({
        label: `${item.localCode} ${item.label || ''}`,
        value: item.id
    });

    if (authStore.hasRole('admin') || authStore.hasRole('superadmin')) {
        return cash_boxes.value.map(mapItem);
    }

    return cash_boxes.value
        .filter(item => item.localCode === authStore.user.local_code)
        .map(mapItem);
});

const totalCash = computed(() => parseFloat(cash_banknotes.value || 0) + parseFloat(cash_coins.value || 0));

const dataAll = computed(() => {
    const histories = forma120Data.value?.filteredAccountsHistories120 ?? {};
    const cashiers = forma120Data.value?.cashiers?.[0] ?? {};
    const forma120 = forma120Data.value?.forma120 ?? {};
    const f120Data = forma120Data.value ?? {};

    return {
        totalSaldoIn: histories.total_saldoin ?? 0,
        totalSaldoOut: histories.total_saldoout ?? 0,
        totalTurnOverDebit: histories.total_turnoverdebit ?? 0,
        totalTurnOverCredit: histories.total_turnovercredit ?? 0,
        totalDocCountIncoming: histories.total_doc_count_incoming ?? 0,
        totalDocCountOutgoing: histories.total_doc_count_outgoing ?? 0,
        operDay: f120Data.operDay ?? '-',
        cashier_cbid: cashiers.userId ?? '-',
        cashier_name: cashiers.userName ?? '-',
        order_2: f120Data.order_2 ?? '',
        order_3: f120Data.order_3 ?? '',
        respOrder2: f120Data.respOrder2 ?? '',
        respOrder3: f120Data.respOrder3 ?? '',
        cash_banknotes: cash_banknotes.value,
        cash_coins: cash_coins.value,
        total_coins: totalCash.value,
        cashboxId: f120Data.id,
        manager_approval: forma120.manager_approval ?? false,
        manager_approval_time: forma120.manager_approval_time ?? '',
        chief_approval: forma120.chief_approval ?? false,
        chief_approval_time: forma120.chief_approval_time ?? '',
        cashier_approval: forma120.cashier_approval ?? false,
        cashier_approval_time: forma120.cashier_approval_time ?? '',
    };
});

const toggleContent = () => isContentVisible.value = !isContentVisible.value;

const handleFilter = async () => {
    toggleContent();
    loading.value = true;
    isBlock.value = true;
    try {
        const response = await axios.post(route('forma120.sendData120'), { cashBoxesId: cashBoxChange.value, date: filterDate.value });
        forma120Data.value = response.data[0];
        const oldValue = forma120Data.value?.forma120;
        cash_coins.value = parseFloat(oldValue?.cash_coins ?? 0);
        cash_banknotes.value = parseFloat(oldValue?.cash_banknotes ?? 0);
        console.log('Forma 120 data', forma120Data.value);
    } catch (error) {
        console.error('Error:', error);
        message.error(t('Xatolik yuz berdi!'), { render: renderMessage, closable: true });
    } finally {
        loading.value = false;
    }
};

const handleConfirm = (userType) => {

    const onlineSaldoOut = parseFloat(dataAll.value.totalSaldoOut);
    const total = parseFloat(totalCash.value);
    if (onlineSaldoOut !== total) {
        const diff = onlineSaldoOut - total;
        message.error(`Farq mavjud: ${numberFormat(diff)}. Summalarni tekshiring!`, { render: renderMessage, closable: true });
        return;
    }
    const confirmData = {

        forma120Data: {
            operDay: dataAll.value.operDay,
            cashboxId: dataAll.value.cashboxId,
            totalSaldoIn: dataAll.value.totalSaldoIn,
            totalSaldoOut: dataAll.value.totalSaldoOut,
            totalTurnOverDebit: dataAll.value.totalTurnOverDebit,
            totalTurnOverCredit: dataAll.value.totalTurnOverCredit,
            totalDocCountIncoming: dataAll.value.totalDocCountIncoming,
            totalDocCountOutgoing: dataAll.value.totalDocCountOutgoing,
            cash_banknotes: dataAll.value.cash_banknotes,
            cash_coins: dataAll.value.cash_coins,
            total_coins: dataAll.value.total_coins,
        },
        signData: {
            operDay: dataAll.value.operDay ?? "",
            cashboxId: dataAll.value.cashboxId ?? "",
            userType: userType ?? "",
            formaType: "120",
            manager_cbid: dataAll.value?.order_3 ? dataAll.value?.order_3 : dataAll.value?.order_2,
            manager_name: dataAll.value?.order_3 ? dataAll.value?.respOrder3 : dataAll.value?.respOrder2,
            chief_cbid: dataAll.value?.order_3 ? dataAll.value?.order_2 : "",
            chief_name: dataAll.value?.order_3 ? dataAll.value?.respOrder2 : "",
            cashier_cbid: dataAll.value?.cashier_cbid ?? "",
            cashier_name: dataAll.value?.cashier_name ?? ""
        }
    }

    router.post(route('forma120.confirmData120'), { confirmData }, {
        onSuccess: () => {
            isContentVisible.value = true;
            handleFilter();
            message.success(t('save'), { render: renderMessage, closable: true });
        },
        onError: () => message.error(t('Xatolik yuz berdi!'), { render: renderMessage, closable: true }),
    });
};

const renderMessage = (props) => {
    const { type } = props;
    return h(
        NAlert,
        {
            closable: props.closable,
            onClose: props.onClose,
            type: type === "loading" ? "default" : type,
            title: t('message_title'),
            style: {
                boxShadow: "var(--n-box-shadow)",
                maxWidth: "calc(100vw - 32px)",
                width: "480px",
            },
        },
        {
            default: () => props.content,
        }
    );
};
const signData = computed(() => {
    const sign = forma120Data.value?.signs || {};
    return {
        cashboxId: sign.cashboxId || null,

        manager_cbid: sign.manager_cbid || null,
        manager_name: sign.manager_name || null,
        manager_approval: sign.manager_approval || false,
        manager_approval_date: sign.manager_approval_date || null,

        chief_cbid: sign.chief_cbid || null,
        chief_name: sign.chief_name || null,
        chief_approval: sign.chief_approval || false,
        chief_approval_date: sign.chief_approval_date || null,

        cashier_cbid: sign.cashier_cbid || null,
        cashier_name: sign.cashier_name || null,
        cashier_approval: sign.cashier_approval || false,
        cashier_approval_date: sign.cashier_approval_date || null,
    };
});

const handleNegativeClick = () => {
}
const handlePositiveClick = () => {
    router.post(route('forma.removeData'), {
        operDay: dataAll.value.operDay,
        cashboxId: dataAll.value.cashboxId,
        formaType: '120'
    }, {
        // preserveState: true, // Joriy holatni saqlash
        onSuccess: (response) => {
            console.log('reee', response);
            message.success(t('delete'));
            // Agar kerak bo'lsa, sahifani qayta yuklash
            isContentVisible.value = true;
            handleFilter()
        },
        onError: (errors) => {
            message.error('Xatolik yuz berdi: ' + (errors.message || 'Noma\'lum xato'));
            console.error(errors);
        }
    });
};

</script>

<template>
    <MyLayout>
        <Head title="Jadval" />
        <BaseSpinner :show="loading" >
        <n-card class="min-h-screen">
            <n-breadcrumb>
                <n-breadcrumb-item :href="route('dashboard')">{{ t("Sidebar.home") }}</n-breadcrumb-item>
                <n-breadcrumb-item>{{ t('forma120')}}</n-breadcrumb-item>
            </n-breadcrumb>
            <n-card>
                <n-grid x-gap="12" :cols="12">
                    <n-gi :span="6" :offset="3">
                        <n-card :hoverable="true" class="mt-4">
                            <template #header>
                                <div class="flex justify-between items-center">
                                    <span>{{ t('filtrlash')}}</span>
                                    <n-button size="medium" @click="toggleContent">
                                        <n-icon size="20"><component :is="isContentVisible ? Minus : Plus" /></n-icon>
                                    </n-button>
                                </div>
                            </template>
                            <n-form v-if="isContentVisible">
                                <n-form-item :label="t('kassalar')">
                                    <n-select filterable v-model:value="cashBoxChange" :options="optionsCashBoxes" :placeholder="t('kassalar')" />
                                </n-form-item>
                                <n-form-item :label="t('f120.sana')">
                                    <n-date-picker v-model:value="filterDate" type="date" clearable :placeholder="t('f120.sana')"/>
                                </n-form-item>
                                <n-form-item>
                                    <n-button @click="handleFilter" type="primary">{{t('filtrlash')}}</n-button>
                                </n-form-item>
                            </n-form>
                        </n-card>
                    </n-gi>
                </n-grid>
            </n-card>
            <n-card v-if="isBlock" :hoverable="true" class="mt-4 text-center" :title="t('forma120')">
                <n-space align="center" justify="center">
                    <n-text type="success" strong class="text-xl mb-2">({{ forma120Data?.localCode}}) {{ forma120Data?.label}}</n-text>
                    <n-popconfirm
                        v-if="authStore.hasRole('kassa_mudiri')"
                        @positive-click="handlePositiveClick"
                        @negative-click="handleNegativeClick"
                        :positive-text="t('Button.confirm')"
                        :negative-text="t('Button.cancel')"
                        :style="{
                          width: '400px',
                          borderRadius: '8px',
                          boxShadow: '0 4px 12px rgba(0, 0, 0, 0.15)',
                          backgroundColor: '#fff',
                          padding: '12px'
                        }">
                            <template #icon>
                                <n-icon size="24" color="#FF2D20">
                                    <Alert />
                                </n-icon>
                            </template>
                            <template #trigger>
                                <n-button size="small" type="error" ghost circle>
                                    <n-icon size="20">
                                        <TrashOutline />
                                    </n-icon>
                                </n-button>
                            </template>

                          <div style="font-size: 14px; color: #333; line-height: 1.5;">
                              <span style="font-weight: bold; color: #FF2D20; margin-right: 5px">
                                {{ formatDate(dataAll.operDay) }}
                              </span>
                              <span>
                               {{ t('removeTitle')}}
                              </span>
                          </div>
                    </n-popconfirm>


                </n-space>
                <n-table class="text-center mt-2" :bordered="true" :single-line="false" :striped="true">
                    <thead>
                    <tr>
                        <th rowspan="2" width="30%">{{ t('f120title')}}</th>
                        <th>{{ t('f120.sana') }}</th>
                        <th>{{ dataAll.operDay }}</th>
                    </tr>
                    <tr>
                        <th width="30%">{{ t('f120.docCount')}}</th>
                        <th>{{ t('f120.summa')}}</th>
                    </tr>
                    </thead>
                    <tbody >
                    <tr><td class="font-semibold">{{ t('f120.saldoIn')}}</td><td>-</td><td>{{ numberFormat(dataAll.totalSaldoIn) }}</td></tr>
                    <tr><td>{{ t('f120.turnDebet')}}</td><td>{{ dataAll.totalDocCountIncoming }}</td><td>{{ numberFormat(dataAll.totalTurnOverDebit) }}</td></tr>
                    <tr><td>{{ t('f120.turnCredit')}}</td><td>{{ dataAll.totalDocCountOutgoing }}</td><td>{{ numberFormat(dataAll.totalTurnOverCredit) }}</td></tr>
                    <tr><td class="font-semibold">{{ t('f120.saldoOut')}}</td><td>-</td><td>{{ numberFormat(dataAll.totalSaldoOut) }}</td></tr>
                    <tr><td class="font-semibold">{{ t('f120.banknotes')}}</td><td colspan="2" class="font-semibold">{{ t('f120.saldoOutBanknotes')}}</td></tr>
                    <tr>
                        <td class="font-semibold">{{ t('f120.banknotaSum')}}</td>
                        <td colspan="2" class="text-center">
                            <n-space align="center" justify="center">
                                <n-input-number :disabled="!authStore.hasRole('kassa_mudiri')"   v-model:value="cash_banknotes" :format="numberStore.formatNumber" :parse="numberStore.parseNumber" :default-value="0" style="width: 300px;" placeholder="Banknotlar summasi" />
                            </n-space>
                        </td>
                    </tr>
                    <tr><td class="font-semibold">{{ t('f120.coins')}}</td><td colspan="2" class="font-semibold">{{ t('f120.saldoOutCoins')}}</td></tr>
                    <tr>
                        <td class="font-semibold">{{ t('f120.coinsSum')}}</td>
                        <td colspan="2">
                            <n-space align="center" justify="center">
                                <n-input-number :disabled="!authStore.hasRole('kassa_mudiri')" v-model:value="cash_coins" :format="numberStore.formatNumber" :parse="numberStore.parseNumber" :default-value="0" style="width: 300px;" placeholder="Tangalar summasi" />
                            </n-space>
                        </td>
                    </tr>
                    <tr>
                        <td class="font-semibold">{{ t('f120.totalSum')}}</td>
                        <td colspan="2">
                            <n-space align="center" justify="center">
                                <n-input-number :disabled="!authStore.hasRole('kassa_mudiri')" :value="totalCash" :format="numberStore.formatNumber" :parse="numberStore.parseNumber" :readonly="true" style="width: 300px;" />
                            </n-space>
                        </td>
                    </tr>
                    <tr>
                        <td class="font-semibold" :colspan="2">
                            {{ t('manager')}}:
                            {{
                                dataAll.order_3
                                    ? `(${dataAll.order_3}) ${dataAll.respOrder3}`
                                    : `(${dataAll.order_2}) ${dataAll.respOrder2}`
                            }}
                        </td>
                        <td class="font-semibold">
                            <n-space align="center" justify="center" v-if="dataAll.order_3 ? (signData.cashier_approval && signData.chief_approval) : signData.cashier_approval">
                                <n-button v-if="!signData.manager_approval && authStore.hasRole('boshqaruvchi')" type="primary" :ghost="true" @click="handleConfirm('manager')">{{ t('checked') }}</n-button>
                                <n-tag type="success" v-if="signData.manager_approval">
                                    <n-flex align="center" justify="center">
                                        <n-icon size="20"><Check/></n-icon>
                                        {{signData.manager_approval ? formatDate(signData.manager_approval_date, 'datetime') : ''  }}
                                    </n-flex>
                                </n-tag>
                            </n-space>
                            <n-space v-else>
                                <n-text v-if="!signData.cashier_approval" type="error">1. {{ t('cashier')}} {{t('notChecked')}}!</n-text>
                                <n-text v-if="!signData.chief_approval && dataAll.order_3" type="error">2. {{ t('chief')}} {{ t('notChecked')}}!</n-text>
                            </n-space>

                        </td>
                    </tr>
                    <tr v-if="dataAll.order_3">
                        <td class="font-semibold" colspan="2">
                            {{ t('chief') }}: {{ dataAll.order_2 ? `(${dataAll.order_2}) ${dataAll.respOrder2}` : "" }}
                        </td>
                        <td class="font-semibold">
                            <n-space align="center" justify="center" v-if="signData.cashier_approval">
                                <n-button v-if="!signData.chief_approval && authStore.hasRole('bosh_hisobchi')" type="primary" :ghost="true" @click="handleConfirm('chief')">{{ t('checked') }}</n-button>
                                <n-tag type="success" v-if="signData.chief_approval">
                                    <n-flex align="center" justify="center">
                                        <n-icon size="20"><Check/></n-icon>
                                        {{signData.chief_approval ? formatDate(signData.chief_approval_date, 'datetime') : ''  }}
                                    </n-flex>
                                </n-tag>
                            </n-space>
                            <n-space v-else>
                                <n-text v-if="!signData.cashier_approval" type="error">1. {{ t('cashier')}} {{ t('notChecked')}}!</n-text>
                            </n-space>
                        </td>
                    </tr>

                    <tr>
                        <td class="font-semibold" colspan="2">

                            <n-text>
                                {{t('cashier')}}:{{ dataAll.cashier_cbid ? `(${dataAll.cashier_cbid}) ${dataAll.cashier_name}` : "-" }}
                            </n-text>

                        </td>
                        <td class="font-semibold">
                            <n-space align="center" justify="center">
                                <n-button v-if="!signData.cashier_approval && authStore.hasRole('kassa_mudiri') " type="primary" :ghost="true" @click="handleConfirm('cashier')">{{ t('checked') }}</n-button>
                                <n-tag type="success" v-if="signData.cashier_approval">
                                    <n-flex align="center" justify="center">
                                        <n-icon size="20"><Check/></n-icon>
                                        {{signData.cashier_approval ? formatDate(signData.cashier_approval_date, 'datetime') : ''  }}
                                    </n-flex>
                                </n-tag>
                            </n-space>

                        </td>
                    </tr>
                    </tbody>
                </n-table>
            </n-card>
        </n-card>
        </BaseSpinner>
    </MyLayout>
</template>

<style scoped>
.font-semibold { font-weight: bold; }
.text-center { text-align: center; }
.flex { display: flex; }
.justify-between { justify-content: space-between; }
.items-center { align-items: center; }
</style>
