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
    output: any;
}>();
</script>

<template>
    <Head :title="`Chiqim #${output.output_number}`" />

    <AppLayout>
        <div class="p-4 md:p-8">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold">Chiqim #{{ output.output_number }}</h1>
                <Link :href="route('inventory-outputs.index')">
                    <Button variant="outline">Ro'yxatga qaytish</Button>
                </Link>
            </div>

            <Card class="mb-6">
                <CardHeader>
                    <CardTitle>Umumiy Ma'lumot</CardTitle>
                </CardHeader>
                <CardContent class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <p class="text-sm text-muted-foreground">Chiqim Raqami</p>
                        <p class="font-medium">{{ output.output_number }}</p>
                    </div>
                     <div>
                        <p class="text-sm text-muted-foreground">Sana</p>
                        <p class="font-medium">{{ output.output_date }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">Umumiy Narx</p>
                        <p class="font-medium">{{ output.total_price }} so'm</p>
                    </div>
                     <div>
                        <p class="text-sm text-muted-foreground">Izoh</p>
                        <p class="font-medium">{{ output.comment || '-' }}</p>
                    </div>
                </CardContent>
            </Card>

            <Card>
                 <CardHeader>
                    <CardTitle>Chiqim Mahsulotlari</CardTitle>
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
                                <TableRow v-if="!output.output_details || output.output_details.length === 0">
                                    <TableCell :colspan="5" class="text-center py-8">
                                        Mahsulotlar topilmadi.
                                    </TableCell>
                                </TableRow>
                                <TableRow v-for="(detail, index) in output.output_details" :key="detail.id">
                                    <TableCell>{{ index + 1 }}</TableCell>
                                    <TableCell>{{ detail.inventory_entry?.product_name || 'N/A' }}</TableCell>
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
