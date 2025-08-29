<script setup>
import MyLayout from "@/Layouts/MyLayout.vue";
import {Head, router} from "@inertiajs/vue3";
import { useI18n } from "vue-i18n";
import {
    NButton,
    NCard,
    NForm,
    NFormItem,
    NGi,
    NGrid,
    NIcon,
    NSelect,
    NSpace,
    NModal,
    NInput,
    useMessage,
    NTag,
    NBreadcrumb,
    NBreadcrumbItem,
    NInputGroup,
    NSwitch,
    NCheckbox,
    NDataTable
} from "naive-ui";
import { AddOutline as Plus, RemoveOutline as Minus, Search, Pencil, Trash, Settings, CheckmarkCircle } from "@vicons/ionicons5";
import {ref, h, computed, watch, onMounted} from "vue";
import BaseSpinner from "@/Components/MyComponent/BaseSpinner.vue";
import axios from 'axios';

const props = defineProps({
    cashBoxes: {
        type: Object,
        default: () => ({})
    },
    dataTemplates: {
        type: Array,
        default: () => [],
    },
    // i18n translations can be passed as props if needed
    translations: {
        type: Object,
        default: () => ({})
    }
});

const { t } = useI18n();
const message = useMessage();

// Reactive State
const isContentVisible = ref(false);
const cashBoxChange = ref(null);
const isLoading = ref(false);
const showAddModal = ref(false);
const showEditModal = ref(false);
const showSettingModal = ref(false);
const templateId = ref(null);
const incomeAccountOptions = ref([]);

// Form Refs
const cashBoxFormRef = ref(null);
const addFormRef = ref(null);
const editFormRef = ref(null);
const settingFormRef = ref(null);

// Form Models
const newEntryForm = ref({ name: '', status: true, cashBoxId: null, checkbook: false });
const editForm = ref({ id: null, name: '', status: null, cashBoxId: null, checkbook: null });
const settingForm = ref({ incomeAccount: null, outgoingAccount: '' });

// Computed Properties
const optionsCashBoxes = computed(() => props.cashBoxes);

const dataTemplatesFilter = computed(() => {
    if (cashBoxChange.value) {
        return props.dataTemplates.filter(d => d.cashboxId === cashBoxChange.value);
    }
    return [];
});

const railStyle = ({ focused, checked}) => {
    const style = {};
    style.background = checked ? "#18a058" : "#d03050";
    if(focused) {
        style.boxShadow = `0 0 0 2px ${checked ? '#18a05840' : '#d0305040'}`;
    }
    return style;
};

const columns = computed(() => [
    { title: t('entryTemplate.id'), key: "id" },
    { title: t('entryTemplate.name'), key: 'templateName' },
    {
        title: t('entryTemplate.status'),
        key: 'status',
        render: (row) => h(NTag, { type: row.status ? 'success' : 'error', bordered: false }, { default: () => row.status ? t('entryTemplate.active') : t('entryTemplate.passive') })
    },
    {
        title: t('entryTemplate.cashboxName'),
        key: 'cashboxId',
        render: (row) => optionsCashBoxes.value.find(cb => cb.value === row.cashboxId)?.label || 'Noma’lum'
    },
    {
        title: t('entryTemplate.accounts'),
        render: (row) => {
            if (!row.template_account) return null;
            return [
                h(NTag, { type: 'success', size: 'small', bordered: false, style: { marginRight: '4px' } }, { default: () => row.template_account.income_account?.accExternal }),
                h(NTag, { type: 'error', size: 'small', bordered: false }, { default: () => row.template_account.outgoing_account?.accExternal })
            ];
        }
    },
    {
        title: t('entryTemplate.checkbook'),
        key: 'checkbook',
        align: 'center',
        render: (row) => row.checkbook ? h(NTag, { type: 'success', bordered: false }, { default: () => h(NIcon, { size: 20 }, { default: () => h(CheckmarkCircle) }) }) : null
    },
    {
        title: t('entryTemplate.action'),
        key: 'action',
        render: (row) => h('div', { style: 'display: flex; gap: 8px;' }, [
            h(NButton, { size: 'medium', text: true, type: 'info', onClick: () => onEdit(row.id) }, { icon: () => h(NIcon, null, { default: () => h(Pencil) }) }),
            h(NButton, { size: 'medium', type: 'warning', text: true, onClick: () => onSettings(row.id) }, { icon: () => h(NIcon, null, { default: () => h(Settings) }) })
        ])
    }
]);

// Methods
const onEdit = (rowId) => {
    const template = props.dataTemplates.find(item => item.id === rowId);
    if (template) {
        editForm.value = {
            id: template.id,
            name: template.templateName,
            status: template.status,
            cashBoxId: template.cashboxId,
            checkbook: template.checkbook
        };
        showEditModal.value = true;
    }
};

const onSettings = async (rowId) => {
    const template = props.dataTemplates.find(item => item.id === rowId);
    if (!template) return message.error('Shablon topilmadi');

    isLoading.value = true;
    try {
        const response = await axios.post(route('entry-templates.accExternalData'), template);
        templateId.value = rowId;
        incomeAccountOptions.value = Object.values(response.data).map(account => ({
            label: account.accExternal,
            value: account.id,
        }));
        showSettingModal.value = true;
    } catch (err) {
        message.error('Hisob raqamlar ma\'lumotlarini olishda xatolik.');
        console.error('Error fetching account data:', err);
    } finally {
        isLoading.value = false;
    }
};

const handleFilter = () => {
    if (cashBoxChange.value) {
        newEntryForm.value.cashBoxId = cashBoxChange.value;
        isContentVisible.value = false;
    }
};

const handleAddSubmit = () => {
    if (!newEntryForm.value.cashBoxId) return message.info("Iltimos, avval kassani tanlang!");

    addFormRef.value?.validate(errors => {
        if (errors) return;
        router.post(route('entry-templates.store'), newEntryForm.value, {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                message.success(t('save'));
                showAddModal.value = false;
                newEntryForm.value.name = '';
                newEntryForm.value.status = true;
            },
            onError: () => message.error(t('save_error'))
        });
    });
};

const handleEditSubmit = () => {
    editFormRef.value?.validate(errors => {
        if (errors) return;
        router.put(route('entry-templates.update', editForm.value.id), editForm.value, {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                message.success(t('update'));
                showEditModal.value = false;
            },
            onError: () => message.error(t('save_error'))
        });
    });
};

const handleSettingConfirm = async () => {
    try {
        await settingFormRef.value?.validate();
        const payload = {
            ...settingForm.value,
            cashboxId: cashBoxChange.value,
            templateId: templateId.value
        };

        await axios.post(route('entry-templates.storeAccountTemplate'), payload);

        message.success(t('save'));
        showSettingModal.value = false;

        // **THE FIX**: Reload props from server to show updated data
        router.reload({ preserveScroll: true });

    } catch (error) {
        message.error(t('error'));
        console.error('Error saving template settings:', error);
    }
};

// Validation Rules
const addFormRules = {
    name: { required: true, min: 2, message: () => t('input_empty'), trigger: ['input', 'blur'] },
    status: { required: true, type: 'boolean', message: () => t('input_empty'), trigger: 'change' }
};
const editFormRules = {
    name: { required: true, message: () => t('input_empty'), trigger: ['input', 'blur'] },
    status: { required: true, type: 'boolean', message: () => t('input_empty'), trigger: 'change' },
    cashBoxId: { required: true, type: 'number', message: () => t('input_empty'), trigger: 'change' },
};
const settingFormRules = {
    incomeAccount: { required: true, type: 'number', message: () => t('input_empty'), trigger: 'change' },
    outgoingAccount: { required: true, trigger: ['input', 'blur'] },
};

</script>

<template>
    <MyLayout>
        <Head title="Provodkalar shablonlari"/>
        <n-card class="min-h-screen">
            <n-breadcrumb class="mb-4">
                <n-breadcrumb-item :href="route('dashboard')">{{ t("Sidebar.home") }}</n-breadcrumb-item>
                <n-breadcrumb-item>Provodkalar shablonlari</n-breadcrumb-item>
            </n-breadcrumb>

            <!-- Modals -->
            <n-modal v-model:show="showAddModal" preset="dialog" :title="t('entryTemplate.add')">
                <n-form ref="addFormRef" :model="newEntryForm" :rules="addFormRules" class="mt-4">
                    <n-form-item label="Nomi" path="name">
                        <n-input v-model:value="newEntryForm.name" placeholder="Nomini kiriting" />
                    </n-form-item>
                    <n-form-item label="Status" path="status">
                       <n-switch size="large" v-model:value="newEntryForm.status" :rail-style="railStyle">
                           <template #checked>{{ t('entryTemplate.active')}}</template>
                           <template #unchecked>{{ t('entryTemplate.passive')}}</template>
                       </n-switch>
                    </n-form-item>
                    <n-form-item label="Kassa" path="cashBoxId">
                        <n-select disabled v-model:value="newEntryForm.cashBoxId" :options="optionsCashBoxes" />
                    </n-form-item>
                    <n-form-item label="" path="checkbook">
                        <n-checkbox v-model:checked="newEntryForm.checkbook">Omonat Daftarchami?</n-checkbox>
                    </n-form-item>
                    <n-space justify="end">
                        <n-button round ghost type="error" @click="showAddModal = false">{{ t('Button.cancel')}}</n-button>
                        <n-button round ghost type="success" @click="handleAddSubmit">{{ t('Button.confirm')}}</n-button>
                    </n-space>
                </n-form>
            </n-modal>

            <n-modal v-model:show="showEditModal" preset="dialog" :title="t('entryTemplate.edit')">
                <n-form ref="editFormRef" :model="editForm" :rules="editFormRules" class="mt-4">
                    <n-form-item :label="t('entryTemplate.name')" path="name">
                        <n-input v-model:value="editForm.name" />
                    </n-form-item>
                    <n-form-item :label="t('entryTemplate.status')" path="status">
                       <n-switch size="large" v-model:value="editForm.status" :rail-style="railStyle">
                           <template #checked>{{ t('entryTemplate.active')}}</template>
                           <template #unchecked>{{ t('entryTemplate.passive')}}</template>
                       </n-switch>
                    </n-form-item>
                    <n-form-item :label="t('entryTemplate.cashboxName')" path="cashBoxId">
                        <n-select v-model:value="editForm.cashBoxId" :options="optionsCashBoxes" />
                    </n-form-item>
                    <n-form-item label="" path="checkbook">
                        <n-checkbox v-model:checked="editForm.checkbook">Omonat Daftarchami?</n-checkbox>
                    </n-form-item>
                    <n-space justify="end">
                        <n-button ghost round type="error" @click="showEditModal = false">{{ t('Button.cancel')}}</n-button>
                        <n-button ghost round type="success" @click="handleEditSubmit">{{ t('Button.confirm')}}</n-button>
                    </n-space>
                </n-form>
            </n-modal>

            <n-modal v-model:show="showSettingModal" preset="dialog" :title="t('entryTemplate.setting')" style="width: 600px">
                <BaseSpinner :show="isLoading" />
                <n-form ref="settingFormRef" :rules="settingFormRules" :model="settingForm" class="mt-4">
                    <n-form-item path="incomeAccount" :label="t('entryTemplate.incomeAccount')">
                        <n-select :options="incomeAccountOptions" v-model:value="settingForm.incomeAccount" filterable />
                    </n-form-item>
                    <n-form-item path="outgoingAccount" :label="t('entryTemplate.outgoingAccount')">
                        <n-input :placeholder="t('entryTemplate.outgoingAccount')" maxlength="20" v-model:value="settingForm.outgoingAccount" />
                    </n-form-item>
                    <n-space justify="end">
                        <n-button ghost round type="error" @click="showSettingModal = false">{{ t('Button.cancel') }}</n-button>
                        <n-button ghost round type="success" @click="handleSettingConfirm">{{ t('Button.confirm') }}</n-button>
                    </n-space>
                </n-form>
            </n-modal>

            <!-- Main Content -->
            <n-grid x-gap="12" :cols="12">
                <n-gi :span="6" :offset="3">
                    <n-card class="mb-4">
                        <template #header>
                            <div class="flex justify-between items-center">
                                <span>{{ t('filtrlash')}}</span>
                                <n-button size="medium" @click="isContentVisible = !isContentVisible">
                                    <n-icon size="20"><component :is="isContentVisible ? Minus : Plus" /></n-icon>
                                </n-button>
                            </div>
                        </template>
                        <n-form v-if="isContentVisible" ref="cashBoxFormRef">
                            <n-form-item :label="t('kassalar')">
                                <n-select filterable v-model:value="cashBoxChange" :options="optionsCashBoxes" :placeholder="t('kassalar')" @update:value="handleFilter" />
                            </n-form-item>
                        </n-form>
                    </n-card>
                </n-gi>
            </n-grid>

            <n-card v-if="cashBoxChange" class="mt-3">
                <n-space align="center" justify="end" class="mb-4">
                    <n-button round type="success" @click="showAddModal = true">
                        <template #icon><n-icon><Plus /></n-icon></template>
                        {{ t('entryTemplate.add') }}
                    </n-button>
                </n-space>
                <n-data-table
                    :columns="columns"
                    :data="dataTemplatesFilter"
                    :pagination="{ pageSize: 10 }"
                    :bordered="false"
                />
            </n-card>
        </n-card>
    </MyLayout>
</template>
