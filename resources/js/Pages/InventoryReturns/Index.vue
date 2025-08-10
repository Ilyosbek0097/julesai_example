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
    returns: {
        data: any[];
        links: any[];
    };
}>();

const showDeleteModal = ref(false);
const returnToDelete = ref<number | null>(null);

const confirmDelete = (id: number) => {
    returnToDelete.value = id;
    showDeleteModal.value = true;
};

const deleteReturn = () => {
    if (returnToDelete.value) {
        router.delete(route('inventory-returns.destroy', returnToDelete.value), {
            preserveScroll: true,
            onSuccess: () => {
                toast.success('Qaytarish muvaffaqiyatli bekor qilindi!');
                showDeleteModal.value = false;
                returnToDelete.value = null;
            },
            onError: () => {
                toast.error('Xatolik yuz berdi!');
            },
        });
    }
};
</script>

<template>
    <Head title="Qaytarishlar" />

    <AppLayout>
        <div class="p-4 md:p-8">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold">Qaytarishlar Ro'yxati</h1>
                <Link :href="route('inventory-returns.create')">
                    <Button>Yangi Qaytarish</Button>
                </Link>
            </div>

            <div class="border rounded-lg">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>#</TableHead>
                            <TableHead>Qaytarish Raqami</TableHead>
                            <TableHead>Sana</TableHead>
                            <TableHead>Izoh</TableHead>
                            <TableHead>Foydalanuvchi</TableHead>
                            <TableHead class="text-right">Amallar</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-if="returns.data.length === 0">
                            <TableCell :colspan="6" class="text-center py-8">
                                Ma'lumotlar topilmadi.
                            </TableCell>
                        </TableRow>
                        <TableRow v-for="(item, index) in returns.data" :key="item.id">
                            <TableCell>{{ index + 1 }}</TableCell>
                            <TableCell>{{ item.return_number }}</TableCell>
                            <TableCell>{{ item.formatted_date }}</TableCell>
                            <TableCell>{{ item.comment || '-' }}</TableCell>
                            <TableCell>{{ item.user?.name || 'N/A' }}</TableCell>
                            <TableCell class="text-right">
                                <Link :href="route('inventory-returns.show', item.id)" class="mr-2">
                                    <Button variant="secondary" size="sm">Ko'rish</Button>
                                </Link>
                                <!-- Edit button can be added later -->
                                <!-- <Link :href="route('inventory-returns.edit', item.id)" class="mr-2">
                                    <Button variant="outline" size="sm">Tahrirlash</Button>
                                </Link> -->
                                <Button variant="destructive" size="sm" @click="confirmDelete(item.id)">
                                    O'chirish
                                </Button>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <!-- Pagination -->
            <div v-if="returns.links.length > 3" class="flex justify-center mt-6">
                <div class="flex flex-wrap -mb-1">
                    <template v-for="(link, key) in returns.links" :key="key">
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
                        Bu amalni qaytarib bo'lmaydi. Bu qaytarishni bekor qiladi va ombordagi miqdorni qayta tiklaydi.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button variant="outline" @click="showDeleteModal = false">Bekor qilish</Button>
                    <Button variant="destructive" @click="deleteReturn">O'chirish</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
