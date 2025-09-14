<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';

const props = defineProps<{
    inventories?: any[];
    report_type?: string;
    report_data?: any[];
    report_columns?: string[];
    request_params?: any;
}>();

const form = useForm({
    type: props.request_params?.type || 'stock',
    from_date: props.request_params?.from_date || new Date().toISOString().slice(0, 10),
    to_date: props.request_params?.to_date || new Date().toISOString().slice(0, 10),
    product_id: props.request_params?.product_id || null,
});

const reportOptions = [
    { value: 'stock', label: 'Ombor qoldig\'i' },
    { value: 'consolidated', label: 'Svodniy hisobot' },
    { value: 'entries', label: 'Kirimlar hisoboti' },
    { value: 'outputs', label: 'Chiqimlar hisoboti' },
    { value: 'product_outputs', label: 'Mahsulot bo\'yicha chiqimlar' },
    { value: 'returns', label: 'Qaytarishlar (umumiy)' },
    { value: 'return_details', label: 'Qaytarishlar (batafsil)' },
];

const inventoryOptions = computed(() =>
    (props.inventories || []).map(i => ({ label: i.name, value: i.id }))
);

const showDatePickers = computed(() => {
    return !['stock', 'consolidated'].includes(form.type);
});

const submit = () => {
    form.get(route('reports.generate'));
};

const downloadExcel = () => {
    const url = new URL(route('reports.export'));
    url.searchParams.append('type', form.type);
    if (showDatePickers.value) {
        url.searchParams.append('from_date', form.from_date);
        url.searchParams.append('to_date', form.to_date);
    }
    if (form.type === 'product_outputs' && form.product_id) {
        url.searchParams.append('product_id', form.product_id);
    }
    window.location.href = url.toString();
};
</script>

<template>
    <Head title="Hisobotlar" />

    <AppLayout>
        <div class="p-4 md:p-8">
            <Card>
                <CardHeader>
                    <CardTitle>Hisobotlar Sahifasi</CardTitle>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                        <div>
                            <label for="type">Hisobot Turi</label>
                            <Select v-model="form.type">
                                <SelectTrigger>
                                    <SelectValue placeholder="Hisobot turini tanlang" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="option in reportOptions" :key="option.value" :value="option.value">
                                        {{ option.label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div v-if="showDatePickers">
                             <label for="from_date">Dan</label>
                             <Input id="from_date" type="date" v-model="form.from_date" />
                        </div>

                        <div v-if="showDatePickers">
                            <label for="to_date">Gacha</label>
                            <Input id="to_date" type="date" v-model="form.to_date" />
                        </div>

                        <div v-if="form.type === 'product_outputs'">
                             <label for="product">Mahsulot</label>
                             <Select v-model="form.product_id">
                                <SelectTrigger>
                                    <SelectValue placeholder="Mahsulotni tanlang" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="option in inventoryOptions" :key="option.value" :value="option.value">
                                        {{ option.label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="flex gap-2">
                             <Button type="submit" :disabled="form.processing">Hisobot Yaratish</Button>
                             <Button
                                type="button"
                                variant="secondary"
                                @click="downloadExcel"
                                :disabled="!report_data || report_data.length === 0"
                             >
                                Excel'ga Yuklash
                             </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>

            <div v-if="Array.isArray(report_data)" class="mt-8">
                <Card>
                    <CardHeader>
                        <CardTitle>Hisobot Natijasi</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p v-if="report_data.length === 0">Ma'lumotlar topilmadi.</p>
                        <!-- The actual table structure will depend on the report type -->
                        <!-- This is a placeholder -->
                        <Table v-else>
                           <TableHeader>
                                <TableRow>
                                    <TableHead v-for="column in report_columns" :key="column">{{ column }}</TableHead>
                                </TableRow>
                           </TableHeader>
                           <TableBody>
                                <TableRow v-for="(row, index) in report_data" :key="index">
                                    <TableCell v-for="column in report_columns" :key="column">
                                        {{ row[column] }}
                                    </TableCell>
                                </TableRow>
                           </TableBody>
                        </Table>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
