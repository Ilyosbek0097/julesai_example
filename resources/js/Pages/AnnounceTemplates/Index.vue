<script setup>
import { ref, watch, h } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { NDataTable, NInput, NDatePicker, NButton, NSpace, NCard, NPagination, NIcon, NSelect } from 'naive-ui';
import { PrintOutline as PrintIcon } from '@vicons/ionicons5';
import throttle from 'lodash/throttle';
import MyLayout from "@/Layouts/MyLayout.vue"; // Using MyLayout as per user's last snippet

// Props from Laravel
const props = defineProps({
    templates: {
        type: Object,
        required: true,
    },
    cashboxes: {
        type: Array,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

// Refs for filters
const search = ref(props.filters.search);
const date = ref(props.filters.date);
const cashboxId = ref(props.filters.cashbox_id);

// Ref for row selection
const checkedRowKeys = ref([]);

// Data table columns definition
const columns = [
    {
        type: 'selection',
        key: 'selection',
    },
    {
        title: 'Hujjat Raqami',
        key: 'docnumb',
        sorter: 'default',
    },
    {
        title: 'Mijoz Nomi',
        key: 'clname',
        sorter: 'default',
    },
    {
        title: 'Qarshi Tomon',
        key: 'coname',
    },
    {
        title: 'Summa',
        key: 'sumpay',
        sorter: 'default',
        render(row) {
            return new Intl.NumberFormat('fr-FR').format(row.sumpay); // Format as space-separated
        }
    },
    {
        title: 'Sana',
        key: 'currday',
        sorter: 'default',
        render(row) {
            return new Date(row.currday).toLocaleDateString();
        }
    },
    {
        title: 'Amallar',
        key: 'actions',
        render(row) {
            return h(
                NButton,
                {
                    size: 'small',
                    circle: true,
                    onClick: () => console.log(`Printing row ${row.announce_template_id}`),
                },
                {
                    icon: () => h(NIcon, null, { default: () => h(PrintIcon) })
                }
            );
        }
    }
];

// Watch for filter changes and reload data from server
watch([search, date, cashboxId], throttle(() => {
    router.get(route('announce-templates.index'), {
        search: search.value,
        date: date.value,
        cashbox_id: cashboxId.value,
    }, {
        preserveState: true,
        replace: true,
    });
}, 300));

// Function to handle pagination change
const handlePageChange = (page) => {
    router.get(props.templates.path, { page }, { preserveState: true });
};

</script>

<template>
    <MyLayout>
        <Head title="E'lonlar Shabloni" />
        <div class="p-4 sm:p-6 lg:p-8">
            <NCard title="E'lonlar ro'yxati">
                <template #header-extra>
                    <NButton
                        type="primary"
                        :disabled="checkedRowKeys.length === 0"
                        @click="() => console.log('Generating document for:', checkedRowKeys)"
                    >
                        Hujjat Chiqarish
                    </NButton>
                </template>

                <NSpace :vertical="true" :size="12" class="mb-4">
                    <NSpace>
                        <NSelect
                            v-model:value="cashboxId"
                            :options="cashboxes"
                            placeholder="Kassani tanlang"
                            clearable
                            filterable
                            style="width: 250px;"
                        />
                        <NInput
                            v-model:value="search"
                            placeholder="Qidiruv..."
                            clearable
                            style="width: 300px;"
                        />
                        <NDatePicker
                            v-model:formatted-value="date"
                            value-format="yyyy-MM-dd"
                            type="date"
                            clearable
                            placeholder="Sanani tanlang"
                        />
                    </NSpace>
                </NSpace>

                <NDataTable
                    :columns="columns"
                    :data="templates.data"
                    :bordered="false"
                    :single-line="false"
                    :remote="true"
                    v-model:checked-row-keys="checkedRowKeys"
                    :row-key="row => row.announce_template_id"
                />

                <div v-if="templates.total > templates.per_page" class="flex justify-center mt-4">
                     <NPagination
                        :item-count="templates.total"
                        :page="templates.current_page"
                        :page-size="templates.per_page"
                        @update:page="handlePageChange"
                        show-quick-jumper
                     >
                        <template #prefix="{ itemCount }">
                            Jami: {{ itemCount }}
                        </template>
                     </NPagination>
                </div>
            </NCard>
        </div>
    </MyLayout>
</template>
