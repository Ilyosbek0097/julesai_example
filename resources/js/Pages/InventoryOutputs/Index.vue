<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
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

const props = defineProps<{
    outputs: {
        data: any[];
        links: any[];
    };
}>();
</script>

<template>
    <Head title="Chiqimlar" />

    <AppLayout>
        <div class="p-4 md:p-8">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold">Chiqimlar Ro'yxati</h1>
                <Link :href="route('inventory-outputs.create')">
                    <Button>Yangi Chiqim</Button>
                </Link>
            </div>

            <div class="border rounded-lg">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>#</TableHead>
                            <TableHead>Chiqim Raqami</TableHead>
                            <TableHead>Sana</TableHead>
                            <TableHead>Umumiy Narx</TableHead>
                            <TableHead>Izoh</TableHead>
                            <TableHead class="text-right">Amallar</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-if="outputs.data.length === 0">
                            <TableCell :colspan="6" class="text-center py-8">
                                Ma'lumotlar topilmadi.
                            </TableCell>
                        </TableRow>
                        <TableRow v-for="(output, index) in outputs.data" :key="output.id">
                            <TableCell>{{ index + 1 }}</TableCell>
                            <TableCell>{{ output.output_number }}</TableCell>
                            <TableCell>{{ output.output_date }}</TableCell>
                            <TableCell>{{ output.total_price }} so'm</TableCell>
                            <TableCell>{{ output.comment }}</TableCell>
                            <TableCell class="text-right">
                                <Link :href="route('inventory-outputs.show', output.id)" class="mr-2">
                                    <Button variant="secondary" size="sm">Ko'rish</Button>
                                </Link>
                                <Link :href="route('inventory-outputs.edit', output.id)">
                                    <Button variant="outline" size="sm">Tahrirlash</Button>
                                </Link>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <!-- Pagination -->
            <div v-if="outputs.links.length > 3" class="flex justify-center mt-6">
                <div class="flex flex-wrap -mb-1">
                    <template v-for="(link, key) in outputs.links" :key="key">
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
    </AppLayout>
</template>
