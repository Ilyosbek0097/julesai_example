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
    entries: {
        data: any[];
        links: any[];
    };
}>();

const showDeleteModal = ref(false);
const entryToDelete = ref<number | null>(null);

const confirmDelete = (id: number) => {
    entryToDelete.value = id;
    showDeleteModal.value = true;
};

const deleteEntry = () => {
    if (entryToDelete.value) {
        router.delete(route('inventory-entries.destroy', entryToDelete.value), {
            preserveScroll: true,
            onSuccess: () => {
                toast.success('Kirim muvaffaqiyatli oʻchirildi!');
                showDeleteModal.value = false;
                entryToDelete.value = null;
            },
            onError: () => {
                toast.error('Xatolik yuz berdi!');
            },
        });
    }
};
</script>

<template>
    <Head title="Kirimlar" />

    <AppLayout>
        <div class="p-4 md:p-8">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold">Kirimlar Ro'yxati</h1>
                <Link :href="route('inventory-entries.create')">
                    <Button>Yangi Kirim</Button>
                </Link>
            </div>

            <div class="border rounded-lg">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>#</TableHead>
                            <TableHead>Kirim Raqami</TableHead>
                            <TableHead>Mahsulot</TableHead>
                            <TableHead>Miqdori</TableHead>
                            <TableHead>Narxi</TableHead>
                            <TableHead>Yetkazib Beruvchi</TableHead>
                            <TableHead>Sana</TableHead>
                            <TableHead class="text-right">Amallar</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-if="entries.data.length === 0">
                            <TableCell :colspan="8" class="text-center py-8">
                                Ma'lumotlar topilmadi.
                            </TableCell>
                        </TableRow>
                        <TableRow v-for="(entry, index) in entries.data" :key="entry.id">
                            <TableCell>{{ index + 1 }}</TableCell>
                            <TableCell>{{ entry.entry_number }}</TableCell>
                            <TableCell>{{ entry.inventory?.name || 'N/A' }}</TableCell>
                            <TableCell>{{ entry.quantity }}</TableCell>
                            <TableCell>{{ entry.unit_price }} so'm</TableCell>
                            <TableCell>{{ entry.supplier?.name || 'N/A' }}</TableCell>
                            <TableCell>{{ new Date(entry.entry_date).toLocaleDateString() }}</TableCell>
                            <TableCell class="text-right">
                                <Link :href="route('inventory-entries.edit', entry.id)" class="mr-2">
                                    <Button variant="outline" size="sm">Tahrirlash</Button>
                                </Link>
                                <Button variant="destructive" size="sm" @click="confirmDelete(entry.id)">
                                    O'chirish
                                </Button>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <!-- Pagination -->
            <div v-if="entries.links.length > 3" class="flex justify-center mt-6">
                <div class="flex flex-wrap -mb-1">
                    <template v-for="(link, key) in entries.links" :key="key">
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
                        Bu amalni qaytarib bo'lmaydi. Bu kirimni ro'yxatdan butunlay o'chiradi.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button variant="outline" @click="showDeleteModal = false">Bekor qilish</Button>
                    <Button variant="destructive" @click="deleteEntry">O'chirish</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
