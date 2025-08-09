<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { Minus, MinusCircle, Plus } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { toast } from 'vue-sonner';

const props = defineProps({
    categories: Array,
    units: Array,
    products: Array,
    suppliers: Array,
});

const form = useForm({
    name: '',
    category_id: '',
    unit_id: '',
});

const detailForm = useForm({
    items: [] as any[],
    comment: '',
    search: '',
    supplier_id: '',
});

const toggleBlok = ref(false);
const categoryOptions = ref(props?.categories?.map((cat: any) => ({ label: cat.name, value: cat.id })));
const unitOptions = ref(props?.units?.map((unit: any) => ({ label: unit.name, value: unit.id })));
const supplierOptions = ref(props?.suppliers);
const productOptions = computed(() =>
    props.products?.map((prod: any) => ({ label: prod.name, value: prod.id }))
);
const showCategoryModal = ref(false);
const showUnitModal = ref(false);
const showSupplierModal = ref(false);

const newCategory = ref('');
const newUnit = ref('');

const newSupplierName = ref('');
const newSupplierAddress = ref('');
const newSupplierPhone = ref('');

const supplierSearchTerm = ref('');
const filteredSuppliers = computed(() =>
    (supplierOptions.value || []).filter(s => s.label.toLowerCase().includes(supplierSearchTerm.value.toLowerCase()))
);

const addCategory = async () => {
    const res = await axios.post(route('categories.store'), { name: newCategory.value });
    categoryOptions?.value.push({ label: res.data.name, value: res.data.id });
    form.category_id = res.data.id;
    showCategoryModal.value = false;
    newCategory.value = '';
};

const addUnit = async () => {
    const res = await axios.post(route('units.store'), { name: newUnit.value });
    unitOptions.value.push({ label: res.data.name, value: res.data.id });
    form.unit_id = res.data.id;
    showUnitModal.value = false;
    newUnit.value = '';
};

const addSupplier = async () => {
    if (!newSupplierName.value) {
        toast.error('Xatolik!', {
            description: 'Yetkazib beruvchi nomini kiritish majburiy.'
        });
        return;
    }
    try {
        const response = await axios.post(route('suppliers.store'), {
            name: newSupplierName.value,
            address: newSupplierAddress.value,
            phone: newSupplierPhone.value,
        });
        const newSupplier = response.data;
        supplierOptions.value.push({ label: newSupplier.name, value: newSupplier.id });
        detailForm.supplier_id = newSupplier.id;
        toast.success('Muvaffaqiyatli qo\'shildi!', {
            description: `"${newSupplier.name}" yetkazib beruvchisi ro'yxatga qo'shildi.`
        });
        showSupplierModal.value = false;
        newSupplierName.value = '';
        newSupplierAddress.value = '';
        newSupplierPhone.value = '';
    } catch (error) {
        toast.error('Xatolik!', {
            description: 'Yetkazib beruvchini qo\'shishda xatolik yuz berdi.'
        });
    }
};

const addItemRow = () => {
    detailForm.items.push({ product_id: '', quantity: '', price: '' });
};

const removeItemRow = (index: number) => {
    detailForm.items.splice(index, 1);
};

const selectedProductIds = computed(() => detailForm.items.map((item) => item.product_id).filter(Boolean));
const handleSaveProduct = () => {
    if (form.processing) return;
    form.post(route('inventories.store'), {
        onSuccess: () => {
            toast.success('Maʼlumot muvaffaqiyatli saqlandi!');
        },
        onError: () => {
            toast.error('Xatolik yuz berdi, iltimos tekshirib qayta urinib ko‘ring.');
        },
    });
};
const handleInventoryEntrySave = () => {
    // 1. Butun items massivi bo‘sh bo‘lsa — to‘xtatamiz
    if (detailForm.items.length === 0) {
        toast.error('Iltimos, kamida bitta mahsulot kiriting!');
        return;
    }

    // 2. Har bir itemni tekshiramiz
    const invalidRowIndex = detailForm.items.findIndex((item) => {
        return (
            !item.product_id ||
            !item.quantity ||
            Number(item.quantity) <= 0 ||
            !item.price ||
            Number(item.price) <= 0
        );
    });

    if (invalidRowIndex !== -1) {
        // toast.error(`Iltimos, bo‘sh qatorlarni to‘liq to‘ldiring!`);
        toast.error(`Iltimos, ${invalidRowIndex + 1}-qatorni to‘liq to‘ldiring!`);
        return;

    }
    // Agar barcha maydonlar to‘g‘ri bo‘lsa, formani jo‘natamiz
    detailForm.post(route('inventory-entries.store'), {
        onSuccess: () => {
            toast.success('Maʼlumot muvaffaqiyatli saqlandi!');
        },
        onError: () => {
            toast.error('Xatolik yuz berdi, iltimos tekshirib qayta urinib ko‘ring.');
        }
    });
};
</script>

<template>
    <AppLayout>
        <div class="mx-auto mt-2 w-1/2">
            <Card>
                <CardHeader>
                    <div class="mb-4 flex items-center justify-between">
                        <!-- Chap taraf: Sarlavha -->
                        <CardTitle class="text-lg font-semibold text-gray-800">Yangi Mahsulot Qo‘shish</CardTitle>

                        <!-- O‘ng taraf: Tugma -->
                        <Button @click="toggleBlok = !toggleBlok" class="flex items-center gap-2">
                            <span v-if="toggleBlok">
                                <Minus />
                            </span>
                            <span v-else>
                                <Plus />
                            </span>
                            <!--                                <span>{{ toggleBlok ? 'Yopish' : 'Ochish' }}</span>-->
                        </Button>
                    </div>
                </CardHeader>
                <CardContent v-if="toggleBlok">
                    <form @submit.prevent="handleSaveProduct" class="space-y-4">
                        <div class="flex gap-2">
                            <div class="flex-1">
                                <Select v-model="form.category_id">
                                    <SelectTrigger class="w-full">
                                        <SelectValue placeholder="Kategoriya tanlang" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="cat in categoryOptions" :key="cat.value" :value="cat.value">
                                            {{ cat.label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <Button type="button" @click="showCategoryModal = true" size="icon">+</Button>
                        </div>
                        <Input v-model="form.name" placeholder="Mahsulot nomi" />
                        <div class="flex gap-2">
                            <div class="flex-1">
                                <Select v-model="form.unit_id">
                                    <SelectTrigger class="w-full">
                                        <SelectValue placeholder="Maxsulot birligini tanlang" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="unit in unitOptions" :key="unit.value" :value="unit.value">
                                            {{ unit.label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <Button type="button" @click="showUnitModal = true" size="icon">+</Button>
                        </div>

                        <Button @click="handleSaveProduct">Saqlash</Button>
                    </form>
                </CardContent>
            </Card>
        </div>
        <div class="flex flex-col gap-4 overflow-x-auto p-4">
            <Card class="p-4">
                <CardHeader>
                    <CardTitle>Mahsulot detali</CardTitle>
                </CardHeader>

                <CardContent class="overflow-x-auto">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Maxsulot</TableHead>
                                <TableHead>Miqdori</TableHead>
                                <TableHead>Narxi</TableHead>
                                <TableHead class="text-center">#</TableHead>
                            </TableRow>
                        </TableHeader>

                        <TableBody>
                            <TableRow v-for="(item, index) in detailForm.items" :key="index">
                                <TableCell>
                                    <Select v-model="item.product_id">
                                        <SelectTrigger class="w-full">
                                            <SelectValue placeholder="Mahsulot tanlang" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <!-- Qidiruv inputi -->
                                            <div class="px-2 py-2">
                                                <Input v-model="item.search" placeholder="Qidirish..." class="w-full" @keydown.stop/>
                                            </div>

                                            <!-- Filtrlangan variantlar -->
                                            <SelectItem
                                                v-for="prod in productOptions?.filter(
                                                    (p) =>
                                                        (!selectedProductIds.includes(p.value) || p.value === item.product_id) &&
                                                        (!item.search || p.label.toLowerCase().includes(item.search.toLowerCase())),
                                                )"
                                                :key="prod.value"
                                                :value="prod.value"
                                            >
                                                {{ prod.label }}
                                            </SelectItem>

                                            <!-- Topilmadi holati -->
                                            <div
                                                v-if="
                                                    productOptions?.filter(
                                                        (p) =>
                                                            (!selectedProductIds.includes(p.value) || p.value === item.product_id) &&
                                                            (!item.search || p.label.toLowerCase().includes(item.search.toLowerCase())),
                                                    ).length === 0
                                                "
                                                class="px-2 py-2 text-sm text-gray-500"
                                            >
                                                Hech narsa topilmadi
                                            </div>
                                        </SelectContent>
                                    </Select>
                                </TableCell>

                                <TableCell>
                                    <Input v-model="item.quantity" type="number" placeholder="Miqdori" />
                                </TableCell>

                                <TableCell>
                                    <Input v-model="item.price" type="number" placeholder="Narxi" />
                                </TableCell>

                                <TableCell class="text-center">
                                    <Button variant="destructive" size="icon" @click.prevent="removeItemRow(index)">
                                        <MinusCircle class="h-4 w-4" />
                                    </Button>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>

                <CardFooter class="mt-4 flex flex-col items-center gap-4">
                     <div class="flex w-full items-start gap-2">
                        <div class="flex flex-1 items-center gap-2">
                            <Select v-model="detailForm.supplier_id">
                                <SelectTrigger>
                                    <SelectValue placeholder="Yetkazib beruvchi tanlang" />
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
                            <Button type="button" @click="showSupplierModal = true" size="icon" class="flex-shrink-0">+</Button>
                        </div>
                        <div class="flex-1">
                            <Textarea v-model="detailForm.comment" placeholder="Izoh......." />
                        </div>
                    </div>
                    <div class="flex w-full items-center justify-between">
                        <Button type="button" @click="addItemRow" class="flex items-center gap-2">
                            <Plus />
                            Qator qo‘shish
                        </Button>
                        <Button type="button" @click="handleInventoryEntrySave"> Saqlash </Button>
                    </div>
                </CardFooter>
            </Card>
        </div>
        <!-- Category Modal -->
        <Dialog v-model:open="showCategoryModal">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Yangi Kategoriya Qo‘shish</DialogTitle>
                </DialogHeader>
                <Input v-model="newCategory" placeholder="Kategoriya nomi" />
                <DialogFooter>
                    <Button @click="addCategory">Qo‘shish</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Unit Modal -->
        <Dialog v-model:open="showUnitModal">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Yangi Birlik Qo‘shish</DialogTitle>
                </DialogHeader>
                <Input v-model="newUnit" placeholder="Birlik nomi" />
                <DialogFooter>
                    <Button @click="addUnit">Qo‘shish</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Supplier Modal -->
        <Dialog v-model:open="showSupplierModal">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Yangi Yetkazib Beruvchi Qo‘shish</DialogTitle>
                </DialogHeader>
                <div class="space-y-4 py-4">
                    <Input v-model="newSupplierName" placeholder="Yetkazib beruvchi nomi (Majburiy)" />
                    <Input v-model="newSupplierAddress" placeholder="Manzil" />
                    <Input v-model="newSupplierPhone" placeholder="Telefon raqami" />
                </div>
                <DialogFooter>
                    <Button @click="addSupplier">Qo‘shish</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
