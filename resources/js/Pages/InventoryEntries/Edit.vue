<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

const props = defineProps<{
    entry: any;
    inventories: any[];
    suppliers: any[];
}>();

const form = useForm({
    inventory_id: props.entry.inventory_id,
    supplier_id: props.entry.supplier_id,
    quantity: props.entry.quantity,
    unit_price: props.entry.unit_price,
    entry_date: props.entry.entry_date,
    comment: props.entry.comment,
});

const inventoryOptions = computed(() =>
    props.inventories.map(i => ({ label: i.name, value: i.id }))
);
const supplierOptions = computed(() =>
    props.suppliers.map(s => ({ label: s.name, value: s.id }))
);

const inventorySearchTerm = ref('');
const supplierSearchTerm = ref('');

const filteredInventories = computed(() =>
    inventoryOptions.value.filter(i => i.label.toLowerCase().includes(inventorySearchTerm.value.toLowerCase()))
);
const filteredSuppliers = computed(() =>
    supplierOptions.value.filter(s => s.label.toLowerCase().includes(supplierSearchTerm.value.toLowerCase()))
);

const submit = () => {
    form.put(route('inventory-entries.update', props.entry.id), {
        onSuccess: () => {
            toast.success('Kirim muvaffaqiyatli yangilandi!');
        },
        onError: (errors) => {
             Object.values(errors).forEach(error => {
                toast.error(error);
            });
        }
    });
};
</script>

<template>
    <Head :title="`Kirimni Tahrirlash #${entry.entry_number}`" />

    <AppLayout>
        <div class="max-w-2xl mx-auto py-8 px-4">
            <Card>
                <CardHeader>
                    <CardTitle>Kirimni Tahrirlash</CardTitle>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">

                        <div>
                            <label for="inventory">Mahsulot</label>
                            <Select v-model="form.inventory_id">
                                <SelectTrigger>
                                    <SelectValue placeholder="Mahsulotni tanlang" />
                                </SelectTrigger>
                                <SelectContent>
                                    <div class="p-2">
                                        <Input v-model="inventorySearchTerm" placeholder="Qidiruv..." @keydown.stop />
                                    </div>
                                    <SelectItem v-for="option in filteredInventories" :key="option.value" :value="option.value">
                                        {{ option.label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <div v-if="form.errors.inventory_id" class="text-sm text-red-600 mt-1">{{ form.errors.inventory_id }}</div>
                        </div>

                        <div>
                            <label for="supplier">Yetkazib Beruvchi</label>
                            <Select v-model="form.supplier_id">
                                <SelectTrigger>
                                    <SelectValue placeholder="Yetkazib beruvchini tanlang" />
                                </SelectTrigger>
                                <SelectContent>
                                    <div class="p-2">
                                        <Input v-model="supplierSearchTerm" placeholder="Qidiruv..." @keydown.stop />
                                    </div>
                                    <SelectItem v-for="option in filteredSuppliers" :key="option.value" :value="option.value">
                                        {{ option.label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                             <div v-if="form.errors.supplier_id" class="text-sm text-red-600 mt-1">{{ form.errors.supplier_id }}</div>
                        </div>

                        <div>
                            <label for="quantity">Miqdori</label>
                            <Input id="quantity" type="number" step="0.01" min="0.01" v-model="form.quantity" class="mt-1" />
                             <div v-if="form.errors.quantity" class="text-sm text-red-600 mt-1">{{ form.errors.quantity }}</div>
                        </div>

                        <div>
                            <label for="unit_price">Narxi (1 dona)</label>
                            <Input id="unit_price" type="number" min="0" v-model="form.unit_price" class="mt-1" />
                            <div v-if="form.errors.unit_price" class="text-sm text-red-600 mt-1">{{ form.errors.unit_price }}</div>
                        </div>

                        <div>
                            <label for="entry_date">Kirim Sanasi</label>
                            <Input id="entry_date" type="date" v-model="form.entry_date" class="mt-1" />
                            <div v-if="form.errors.entry_date" class="text-sm text-red-600 mt-1">{{ form.errors.entry_date }}</div>
                        </div>

                        <div>
                            <label for="comment">Izoh</label>
                            <Textarea id="comment" v-model="form.comment" class="mt-1" />
                             <div v-if="form.errors.comment" class="text-sm text-red-600 mt-1">{{ form.errors.comment }}</div>
                        </div>

                        <div class="flex justify-end">
                            <Button type="submit" :disabled="form.processing">
                                Yangilash
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
