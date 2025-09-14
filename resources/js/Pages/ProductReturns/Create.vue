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
import { Label } from '@/components/ui/label';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { PlusCircle, Trash2 } from 'lucide-vue-next';

const props = defineProps<{
    inventories: { id: number; name: string }[];
}>();

// Form for adding a single item to the list
const addItemForm = ref({
    inventory_id: null as number | null,
    quantity: 1 as number | null,
});

// Main form for submitting the entire return list
const mainForm = useForm({
    returns: [] as {
        inventory_id: number;
        name: string;
        quantity: number | null;
    }[],
    comment: '',
});

const productSearchTerm = ref('');

// Filter inventories for the dropdown:
// 1. Based on the search term
// 2. Excluding items already in the mainForm.returns list
const filteredInventories = computed(() => {
    const returnIds = new Set(mainForm.returns.map(r => r.inventory_id));
    let available = props.inventories.filter(i => !returnIds.has(i.id));

    if (productSearchTerm.value) {
        available = available.filter(i =>
            i.name.toLowerCase().includes(productSearchTerm.value.toLowerCase())
        );
    }
    return available;
});

const addToList = () => {
    if (!addItemForm.value.inventory_id || !addItemForm.value.quantity || addItemForm.value.quantity <= 0) {
        return toast.error('Iltimos, mahsulot tanlang va to\'g\'ri miqdor kiriting.');
    }

    const selectedInventory = props.inventories.find(i => i.id === addItemForm.value.inventory_id);
    if (!selectedInventory) return;

    mainForm.returns.push({
        inventory_id: selectedInventory.id,
        name: selectedInventory.name,
        quantity: addItemForm.value.quantity,
    });

    toast.success(`"${selectedInventory.name}" ro'yxatga qo'shildi.`);

    // Reset add item form
    addItemForm.value.inventory_id = null;
    addItemForm.value.quantity = 1;
    productSearchTerm.value = '';
};

const removeItem = (inventory_id: number) => {
    mainForm.returns = mainForm.returns.filter(r => r.inventory_id !== inventory_id);
    toast.info("Mahsulot ro'yxatdan o'chirildi.");
};

const submit = () => {
    if (mainForm.returns.length === 0) {
        return toast.error("Qaytarish uchun kamida bitta mahsulot qo'shing.");
    }

    mainForm.post(route('product-returns.store'), {
        onSuccess: () => {
            toast.success('Barcha tovarlar muvaffaqiyatli qaytarildi!');
            mainForm.reset();
        },
        onError: (errors) => {
            Object.values(errors).forEach(e => toast.error(e as string));
        },
    });
};
</script>

<template>
    <Head title="Ko'p Tovarlarni Qaytarish" />
    <AppLayout>
        <div class="p-4 md:p-8 space-y-6">
            <Card>
                <CardHeader>
                    <CardTitle>Mahsulotlarni Qaytarish Ro'yxatiga Qo'shish</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                        <div class="md:col-span-2">
                            <Label for="inventory">Mahsulot</Label>
                            <Select v-model="addItemForm.inventory_id">
                                <SelectTrigger id="inventory" class="w-full">
                                    <SelectValue placeholder="Mahsulotni tanlang..." />
                                </SelectTrigger>
                                <SelectContent>
                                    <div class="p-2">
                                        <Input v-model="productSearchTerm" placeholder="Mahsulot nomini yozing..." @keydown.stop />
                                    </div>
                                    <SelectItem v-for="inventory in filteredInventories" :key="inventory.id" :value="inventory.id">
                                        {{ inventory.name }}
                                    </SelectItem>
                                    <div v-if="filteredInventories.length === 0" class="p-2 text-center text-sm text-muted-foreground">
                                        Mahsulot topilmadi.
                                    </div>
                                </SelectContent>
                            </Select>
                        </div>
                        <div>
                            <Label for="quantity">Miqdori</Label>
                            <Input id="quantity" type="number" v-model="addItemForm.quantity" required min="0.01" step="0.01" />
                        </div>
                        <div>
                            <Button @click="addToList" class="w-full">
                                <PlusCircle class="mr-2 h-4 w-4" /> Ro'yxatga Qo'shish
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <form @submit.prevent="submit" v-if="mainForm.returns.length > 0">
                <Card>
                    <CardHeader>
                        <CardTitle>Qaytariladigan Mahsulotlar Ro'yxati</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Mahsulot Nomi</TableHead>
                                    <TableHead class="w-48">Miqdori</TableHead>
                                    <TableHead class="w-20"></TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="(item, index) in mainForm.returns" :key="item.inventory_id">
                                    <TableCell>{{ item.name }}</TableCell>
                                    <TableCell>
                                        <Input type="number" v-model="item.quantity" min="0.01" step="0.01" required />
                                        <p v-if="mainForm.errors[`returns.${index}.quantity`]" class="text-red-500 text-xs mt-1">
                                            {{ mainForm.errors[`returns.${index}.quantity`] }}
                                        </p>
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <Button type="button" variant="destructive" size="icon" @click="removeItem(item.inventory_id)">
                                            <Trash2 class="h-4 w-4" />
                                        </Button>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                        <div class="mt-6">
                            <Label for="comment">Umumiy Izoh</Label>
                            <Textarea id="comment" v-model="mainForm.comment" placeholder="Barcha qaytarilayotgan tovarlar uchun umumiy izoh..." />
                        </div>
                        <div class="flex justify-end mt-6">
                            <Button type="submit" :disabled="mainForm.processing">
                                Barchasini Qaytarish
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </form>
        </div>
    </AppLayout>
</template>
