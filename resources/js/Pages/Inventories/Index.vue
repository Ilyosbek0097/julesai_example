<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { toast } from 'vue-sonner';

const props = defineProps<{
    inventories: {
        data: any[];
        links: any[];
    };
}>();

const showDeleteModal = ref(false);
const inventoryToDelete = ref<number | null>(null);

const confirmDelete = (id: number) => {
    inventoryToDelete.value = id;
    showDeleteModal.value = true;
};

const deleteInventory = () => {
    if (inventoryToDelete.value) {
        router.delete(route('inventories.destroy', inventoryToDelete.value), {
            preserveScroll: true,
            onSuccess: () => {
                toast.success('Muvaffaqiyatli oʻchirildi!');
                showDeleteModal.value = false;
                inventoryToDelete.value = null;
            },
            onError: () => {
                toast.error('Xatolik yuz berdi!');
            },
        });
    }
};
</script>

<template>
    <Head title="Mahsulotlar" />

    <AppLayout>
        <div class="p-4 md:p-8">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold">Mahsulotlar Ro'yxati</h1>
                <Link :href="route('inventories.create')">
                    <Button>Yangi Qo'shish</Button>
                </Link>
            </div>

            <div class="border rounded-lg">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>#</TableHead>
                            <TableHead>Nomi</TableHead>
                            <TableHead>Kategoriyasi</TableHead>
                            <TableHead>Birligi</TableHead>
                            <TableHead class="text-right">Amallar</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-if="inventories.data.length === 0">
                            <TableCell :colspan="5" class="text-center py-8">
                                Ma'lumotlar topilmadi.
                            </TableCell>
                        </TableRow>
                        <TableRow v-for="(inventory, index) in inventories.data" :key="inventory.id">
                            <TableCell>{{ index + 1 }}</TableCell>
                            <TableCell>{{ inventory.name }}</TableCell>
                            <TableCell>{{ inventory.category?.name || 'N/A' }}</TableCell>
                            <TableCell>{{ inventory.unit?.name || 'N/A' }}</TableCell>
                            <TableCell class="text-right">
                                <Link :href="route('inventories.edit', inventory.id)" class="mr-2">
                                    <Button variant="outline" size="sm">Tahrirlash</Button>
                                </Link>
                                <Button variant="destructive" size="sm" @click="confirmDelete(inventory.id)">
                                    O'chirish
                                </Button>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <!-- Pagination -->
            <div v-if="inventories.links.length > 3" class="flex justify-center mt-6">
                <div class="flex flex-wrap -mb-1">
                    <template v-for="(link, key) in inventories.links" :key="key">
                        <div
                            v-if="link.url === null"
                            class="mr-1 mb-1 px-4 py-3 text-sm leading-4 text-gray-400 border rounded"
                            v-html="link.label"
                        />
                        <Link
                            v-else
                            class="mr-1 mb-1 px-4 py-3 text-sm leading-4 border rounded hover:bg-white focus:border-indigo-500 focus:text-indigo-500"
                            :class="{ 'bg-blue-700 text-white': link.active }"
                            :href="link.url"
                            v-html="link.label"
                        />
                    </template>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <Dialog v-model:open="showDeleteModal">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Haqiqatan ham o'chirmoqchimisiz?</DialogTitle>
                    <DialogDescription>
                        Bu amalni qaytarib bo'lmaydi. Bu mahsulotni ro'yxatdan butunlay o'chiradi.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button variant="outline" @click="showDeleteModal = false">Bekor qilish</Button>
                    <Button variant="destructive" @click="deleteInventory">O'chirish</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
