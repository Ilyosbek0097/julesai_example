<script setup>
import MyLayout from "@/Layouts/MyLayout.vue";
import {useI18n} from "vue-i18n";
import {Head, router} from "@inertiajs/vue3";
import {computed, onMounted, ref, h} from "vue";
import {NButton, NIcon} from "naive-ui";
import {Add as AddIcon} from "@vicons/ionicons5";

const {t} = useI18n();
const props = defineProps({
    cashboxes: {
        type: Array,
        required: true,
        default: () => []
    },
    employes: {
        type: Array,
        required: true,
        default: () => []
    },
    existingSigns: {
        type: Array,
        required: true,
        default: () => []
    }
});

const selectCashBox = ref(null);
const showModal = ref(false);
const selectedCashboxKey = ref(null);
const selectedSignType = ref(null);
const selectedEmployee = ref(null);

// Har bir cashbox uchun imzolarni saqlash (bir nechta xodim bo'lishi mumkin)
const cashboxSigns = ref({});

const optionsCashBoxes = computed(() =>
    props.cashboxes.map(cashbox => ({
        label: cashbox.label,
        value: cashbox.value
    }))
);

// Modal uchun xodimlar ro'yxati
const employeeOptions = computed(() => {
    if (!selectedCashboxKey.value) return [];

    // Tanlangan cashboxni topish
    const selectedCashbox = props.cashboxes.find(cb => cb.value === selectedCashboxKey.value);
    if (!selectedCashbox) return [];

    // Cashboxning branch_id bo'yicha xodimlarni filtrlash
    return props.employes
        .filter(emp => emp.branch_id === selectedCashbox.branch)
        .map(emp => ({
            label: emp.name,
            value: emp.id
        }));
});

// Filtrlangan cashboxlar
const filteredCashboxes = computed(() => {
    if (!selectCashBox.value) {
        return props.cashboxes;
    }
    return props.cashboxes.filter(cb => cb.value === selectCashBox.value);
});

// Jadval ustunlari
const columns = [
    {
        title: 'Cashbox',
        key: 'cashbox',
        width: 250,
        render: (row) => row.label
    },
    {
        title: '1-imzo Bosh hisobchi',
        key: 'sign1',
        width: 300,
        render: (row) => {
            const signs = cashboxSigns.value[row.value]?.sign1 || [];
            return h('div', { class: 'sign-cell' }, [
                ...signs.map((sign, index) =>
                    h('div', { class: 'sign-item', key: sign.id }, [
                        h('span', { class: 'sign-name' }, `${index + 1}. ${sign.name}`),
                        h(NButton, {
                            size: 'tiny',
                            type: 'error',
                            text: true,
                            onClick: () => removeSign(row.value, 'sign1', sign.id)
                        }, { default: () => '✕' })
                    ])
                ),
                h(NButton, {
                    size: 'small',
                    circle: true,
                    class: 'add-btn',
                    onClick: () => openModal(row.value, 'sign1')
                }, {
                    icon: () => h(NIcon, null, { default: () => h(AddIcon) })
                })
            ]);
        }
    },
    {
        title: '2-imzo Hisobchi',
        key: 'sign2',
        width: 300,
        render: (row) => {
            const signs = cashboxSigns.value[row.value]?.sign2 || [];
            return h('div', { class: 'sign-cell' }, [
                ...signs.map((sign, index) =>
                    h('div', { class: 'sign-item', key: sign.id }, [
                        h('span', { class: 'sign-name' }, `${index + 1}. ${sign.name}`),
                        h(NButton, {
                            size: 'tiny',
                            type: 'error',
                            text: true,
                            onClick: () => removeSign(row.value, 'sign2', sign.id)
                        }, { default: () => '✕' })
                    ])
                ),
                h(NButton, {
                    size: 'small',
                    circle: true,
                    class: 'add-btn',
                    onClick: () => openModal(row.value, 'sign2')
                }, {
                    icon: () => h(NIcon, null, { default: () => h(AddIcon) })
                })
            ]);
        }
    },
    {
        title: '3-imzo Kassa mudiri',
        key: 'sign3',
        width: 300,
        render: (row) => {
            const signs = cashboxSigns.value[row.value]?.sign3 || [];
            return h('div', { class: 'sign-cell' }, [
                ...signs.map((sign, index) =>
                    h('div', { class: 'sign-item', key: sign.id }, [
                        h('span', { class: 'sign-name' }, `${index + 1}. ${sign.name}`),
                        h(NButton, {
                            size: 'tiny',
                            type: 'error',
                            text: true,
                            onClick: () => removeSign(row.value, 'sign3', sign.id)
                        }, { default: () => '✕' })
                    ])
                ),
                h(NButton, {
                    size: 'small',
                    circle: true,
                    class: 'add-btn',
                    onClick: () => openModal(row.value, 'sign3')
                }, {
                    icon: () => h(NIcon, null, { default: () => h(AddIcon) })
                })
            ]);
        }
    }
];

// Modal ochish
const openModal = (cashboxKey, signType) => {
    selectedCashboxKey.value = cashboxKey;
    selectedSignType.value = signType;
    selectedEmployee.value = null;
    showModal.value = true;
};

// Xodimni qo'shish
const addEmployee = () => {
    if (!selectedEmployee.value) return;

    const employee = props.employes.find(e => e.id === selectedEmployee.value);
    if (!employee) return;

    if (!cashboxSigns.value[selectedCashboxKey.value]) {
        cashboxSigns.value[selectedCashboxKey.value] = {};
    }
    if (!cashboxSigns.value[selectedCashboxKey.value][selectedSignType.value]) {
        cashboxSigns.value[selectedCashboxKey.value][selectedSignType.value] = [];
    }

    const isAlreadyAdded = cashboxSigns.value[selectedCashboxKey.value][selectedSignType.value].some(
        sign => sign.id === employee.id
    );

    if (isAlreadyAdded) {
        console.warn('Employee already added for this sign type.');
        closeModal();
        return;
    }

    cashboxSigns.value[selectedCashboxKey.value][selectedSignType.value].push({
        id: employee.id,
        name: employee.name
    });

    closeModal();
};

// Imzoni o'chirish
const removeSign = (cashboxKey, signType, employeeId) => {
    if (cashboxSigns.value[cashboxKey] && cashboxSigns.value[cashboxKey][signType]) {
        cashboxSigns.value[cashboxKey][signType] =
            cashboxSigns.value[cashboxKey][signType].filter(sign => sign.id !== employeeId);
    }
};

// Modal yopish
const closeModal = () => {
    showModal.value = false;
    selectedCashboxKey.value = null;
    selectedSignType.value = null;
    selectedEmployee.value = null;
};

// Imzo turini olish
const getSignTypeLabel = () => {
    const labels = {
        sign1: '1-imzo Bosh hisobchi',
        sign2: '2-imzo Hisobchi',
        sign3: '3-imzo Kassa mudiri'
    };
    return labels[selectedSignType.value] || '';
};

// Ma'lumotlarni saqlash
const saveData = () => {
    const dataToSave = [];

    Object.keys(cashboxSigns.value).forEach(cashboxId => {
        const signs = cashboxSigns.value[cashboxId];

        ['sign1', 'sign2', 'sign3'].forEach(signType => {
            if (signs[signType] && signs[signType].length > 0) {
                signs[signType].forEach((employee, index) => {
                    dataToSave.push({
                        cashbox_id: cashboxId,
                        employee_id: employee.id,
                        sign_type: signType,
                        order: index + 1
                    });
                });
            }
        });
    });

    if (dataToSave.length === 0) {
        console.warn('No data to save.');
        return;
    }

    router.post('/nobalance-store', { signs: dataToSave }, {
        preserveState: true,
        onSuccess: () => {
            console.log('Data saved successfully!');
            // You can add a success notification here
        },
        onError: (errors) => {
            console.error('Error saving data:', errors);
            // You can add an error notification here
        }
    });
};

onMounted(() => {
    const signsByCashbox = {};
    props.existingSigns.forEach(sign => {
        const employee = props.employes.find(e => e.id === sign.employee_id);
        if (employee) {
            if (!signsByCashbox[sign.cashbox_id]) {
                signsByCashbox[sign.cashbox_id] = {};
            }
            if (!signsByCashbox[sign.cashbox_id][sign.sign_type]) {
                signsByCashbox[sign.cashbox_id][sign.sign_type] = [];
            }
            signsByCashbox[sign.cashbox_id][sign.sign_type].push({
                id: employee.id,
                name: employee.name,
                order: sign.order
            });
        }
    });

    // Sort the signs by order
    Object.keys(signsByCashbox).forEach(cashboxId => {
        Object.keys(signsByCashbox[cashboxId]).forEach(signType => {
            signsByCashbox[cashboxId][signType].sort((a, b) => a.order - b.order);
        });
    });

    cashboxSigns.value = signsByCashbox;
});
</script>
<template>
    <MyLayout>
        <Head :title="t('Sidebar.nobalance_signs')"/>
        <n-card class="min-h-screen">
            <n-grid x-gap="12" y-gap="12" cols="12" class="mb-4">
                <n-gi offset="4" span="4">
                    <n-form-item :label="t('form.select_cashbox')">
                        <n-select
                            :options="optionsCashBoxes"
                            :placeholder="t('form.select_cashbox')"
                            v-model:value="selectCashBox"
                            filterable
                            clearable
                        />
                    </n-form-item>
                </n-gi>
            </n-grid>

            <n-data-table
                :columns="columns"
                :data="filteredCashboxes"
                :row-key="row => row.value"
                :bordered="true"
            />

            <n-grid x-gap="12" y-gap="12" cols="12" class="mt-4">
                <n-gi offset="10" span="2">
                    <n-button type="primary" @click="saveData">Saqlash</n-button>
                </n-gi>
            </n-grid>

            <!-- Modal xodim tanlash uchun -->
            <n-modal
                v-model:show="showModal"
                preset="dialog"
                :title="getSignTypeLabel()"
                positive-text="Qo'shish"
                negative-text="Bekor qilish"
                @positive-click="addEmployee"
                @negative-click="closeModal"
            >
                <n-form-item label="Xodimni tanlang">
                    <n-select
                        v-model:value="selectedEmployee"
                        :options="employeeOptions"
                        placeholder="Xodimni tanlang"
                        filterable
                        clearable
                    />
                </n-form-item>

                <n-alert
                    v-if="employeeOptions.length === 0"
                    type="warning"
                    class="mt-3"
                >
                    Bu cashbox uchun xodimlar topilmadi
                </n-alert>
            </n-modal>
        </n-card>
    </MyLayout>
</template>

<style scoped>
.flex {
    display: flex;
}
.items-center {
    align-items: center;
}
.justify-between {
    justify-content: space-between;
}
.sign-cell {
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.sign-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 4px 8px;
    background: #f5f5f5;
    border-radius: 4px;
}
.sign-name {
    flex: 1;
    font-size: 13px;
}
.add-btn {
    align-self: flex-start;
}
</style>
