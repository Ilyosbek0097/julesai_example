<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { toast } from 'vue-sonner';
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
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

const props = defineProps<{
    inventory: any;
    categories: any[];
    units: any[];
}>();

const form = useForm({
    name: props.inventory.name,
    category_id: props.inventory.category_id,
    unit_id: props.inventory.unit_id,
});

// Options for selects
const categoryOptions = ref(props.categories.map(c => ({ label: c.name, value: c.id })));
const unitOptions = ref(props.units.map(u => ({ label: u.name, value: u.id })));

// State for modals
const showCategoryModal = ref(false);
const showUnitModal = ref(false);
const newCategoryName = ref('');
const newUnitName = ref('');

// State for searchable selects
const categorySearchTerm = ref('');
const unitSearchTerm = ref('');

const filteredCategories = computed(() =>
    categoryOptions.value.filter(c => c.label.toLowerCase().includes(categorySearchTerm.value.toLowerCase()))
);

const filteredUnits = computed(() =>
    unitOptions.value.filter(u => u.label.toLowerCase().includes(unitSearchTerm.value.toLowerCase()))
);

const addCategory = async () => {
    if (!newCategoryName.value) {
        return toast.error('Kategoriya nomi kiritilishi shart.');
    }
    try {
        const response = await axios.post(route('categories.store'), { name: newCategoryName.value });
        const newCategory = response.data;
        categoryOptions.value.push({ label: newCategory.name, value: newCategory.id });
        form.category_id = newCategory.id;
        showCategoryModal.value = false;
        newCategoryName.value = '';
        toast.success('Kategoriya muvaffaqiyatli qo\'shildi!');
    } catch (error) {
        toast.error('Kategoriya qo\'shishda xatolik yuz berdi.');
    }
};

const addUnit = async () => {
    if (!newUnitName.value) {
        return toast.error('Birlik nomi kiritilishi shart.');
    }
    try {
        const response = await axios.post(route('units.store'), { name: newUnitName.value });
        const newUnit = response.data;
        unitOptions.value.push({ label: newUnit.name, value: newUnit.id });
        form.unit_id = newUnit.id;
        showUnitModal.value = false;
        newUnitName.value = '';
        toast.success('Birlik muvaffaqiyatli qo\'shildi!');
    } catch (error) {
        toast.error('Birlik qo\'shishda xatolik yuz berdi.');
    }
};

const submit = () => {
    form.put(route('inventories.update', props.inventory.id), {
        onSuccess: () => {
            toast.success('Mahsulot muvaffaqiyatli yangilandi!');
        },
    });
};
</script>

<template>
    <Head title="Mahsulotni Tahrirlash" />

    <AppLayout>
        <div class="max-w-2xl mx-auto py-8 px-4">
            <Card>
                <CardHeader>
                    <CardTitle>Mahsulotni Tahrirlash</CardTitle>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Mahsulot Nomi</label>
                            <Input id="name" v-model="form.name" class="mt-1" required />
                            <div v-if="form.errors.name" class="text-sm text-red-600 mt-1">{{ form.errors.name }}</div>
                        </div>

                        <div>
                            <label for="category" class="block text-sm font-medium text-gray-700">Kategoriyasi</label>
                            <div class="flex items-center gap-2 mt-1">
                                <Select v-model="form.category_id">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Kategoriya tanlang" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <div class="p-2">
                                            <Input v-model="categorySearchTerm" placeholder="Qidiruv..." @keydown.stop />
                                        </div>
                                        <SelectItem v-for="option in filteredCategories" :key="option.value" :value="option.value">
                                            {{ option.label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <Button type="button" @click="showCategoryModal = true" size="icon" variant="outline">+</Button>
                            </div>
                            <div v-if="form.errors.category_id" class="text-sm text-red-600 mt-1">{{ form.errors.category_id }}</div>
                        </div>

                        <div>
                            <label for="unit" class="block text-sm font-medium text-gray-700">O'lchov Birligi</label>
                             <div class="flex items-center gap-2 mt-1">
                                <Select v-model="form.unit_id">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Birlik tanlang" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <div class="p-2">
                                            <Input v-model="unitSearchTerm" placeholder="Qidiruv..." @keydown.stop />
                                        </div>
                                        <SelectItem v-for="option in filteredUnits" :key="option.value" :value="option.value">
                                            {{ option.label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <Button type="button" @click="showUnitModal = true" size="icon" variant="outline">+</Button>
                            </div>
                            <div v-if="form.errors.unit_id" class="text-sm text-red-600 mt-1">{{ form.errors.unit_id }}</div>
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

        <!-- Add Category Modal -->
        <Dialog v-model:open="showCategoryModal">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Yangi Kategoriya Qo‘shish</DialogTitle>
                </DialogHeader>
                <div class="py-4">
                    <Input v-model="newCategoryName" placeholder="Kategoriya nomi" @keydown.enter.prevent="addCategory" />
                </div>
                <DialogFooter>
                    <Button variant="outline" @click="showCategoryModal = false">Bekor qilish</Button>
                    <Button @click="addCategory">Qo‘shish</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Add Unit Modal -->
        <Dialog v-model:open="showUnitModal">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Yangi Birlik Qo‘shish</DialogTitle>
                </DialogHeader>
                 <div class="py-4">
                    <Input v-model="newUnitName" placeholder="Birlik nomi" @keydown.enter.prevent="addUnit" />
                </div>
                <DialogFooter>
                    <Button variant="outline" @click="showUnitModal = false">Bekor qilish</Button>
                    <Button @click="addUnit">Qo‘shish</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
