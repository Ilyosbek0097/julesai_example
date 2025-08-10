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
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

const props = defineProps<{
    return: any;
}>();

const totalAmount = computed(() => {
    return props.return.return_details.reduce((sum, detail) => sum + parseFloat(detail.total), 0).toFixed(2);
});
</script>

<template>
    <Head :title="`Qaytarish #${return.return_number}`" />

    <AppLayout>
        <div class="p-4 md:p-8">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold">Qaytarish #{{ return.return_number }}</h1>
                <Link :href="route('inventory-returns.index')">
                    <Button variant="outline">Ro'yxatga qaytish</Button>
                </Link>
            </div>

            <Card class="mb-6">
                <CardHeader>
                    <CardTitle>Umumiy Ma'lumot</CardTitle>
                </CardHeader>
                <CardContent class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <p class="text-sm text-muted-foreground">Qaytarish Raqami</p>
                        <p class="font-medium">{{ return.return_number }}</p>
                    </div>
                     <div>
                        <p class="text-sm text-muted-foreground">Sana</p>
                        <p class="font-medium">{{ return.formatted_date }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">Umumiy Summa</p>
                        <p class="font-medium">{{ totalAmount }} so'm</p>
                    </div>
                     <div>
                        <p class="text-sm text-muted-foreground">Izoh</p>
                        <p class="font-medium">{{ return.comment || '-' }}</p>
                    </div>
                </CardContent>
            </Card>

            <Card>
                 <CardHeader>
                    <CardTitle>Qaytarilgan Mahsulotlar</CardTitle>
                </CardHeader>
                <CardContent>
                     <div class="border rounded-lg">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>#</TableHead>
                                    <TableHead>Mahsulot</TableHead>
                                    <TableHead>Miqdori</TableHead>
                                    <TableHead>Narxi</TableHead>
                                    <TableHead>Umumiy</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-if="!return.return_details || return.return_details.length === 0">
                                    <TableCell :colspan="5" class="text-center py-8">
                                        Mahsulotlar topilmadi.
                                    </TableCell>
                                </TableRow>
                                <TableRow v-for="(detail, index) in return.return_details" :key="detail.id">
                                    <TableCell>{{ index + 1 }}</TableCell>
                                    <TableCell>{{ detail.output_detail.inventory_entry.inventory.name }}</TableCell>
                                    <TableCell>{{ detail.quantity }}</TableCell>
                                    <TableCell>{{ detail.price }} so'm</TableCell>
                                    <TableCell>{{ detail.total }} so'm</TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
