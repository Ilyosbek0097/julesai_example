<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { IterationCcw, Plus, Trash2 } from 'lucide-vue-next';

const props = defineProps<{
    outputs: any[];
}>();

const selectedOutputId = ref<number | null>(null);
const outputSearchTerm = ref('');

const form = useForm({
    returns: [] as {
        output_detail_id: number;
        quantity: number | null;
        product_name: string;
        max_quantity: number;
        price: number;
    }[],
    comment: '',
});

const filteredOutputs = computed(() => {
    if (!outputSearchTerm.value) {
        return props.outputs;
    }
    const searchTerm = outputSearchTerm.value.toLowerCase();
    return props.outputs.filter(o =>
        o.output_number.toLowerCase().includes(searchTerm) ||
        o.formatted_date.toLowerCase().includes(searchTerm)
    );
});

const selectedOutput = computed(() => {
    if (!selectedOutputId.value) return null;
    return props.outputs.find(o => o.id == selectedOutputId.value) || null;
});

const addReturnItem = (detail: any) => {
    if (form.returns.some(r => r.output_detail_id === detail.id)) {
        return toast.warning('Mahsulot roʻyxatda mavjud!');
    }
    form.returns.push({
        output_detail_id: detail.id,
        quantity: 1,
        product_name: detail.inventory_entry.product_name,
        max_quantity: detail.remaining_returnable, // Use remaining_returnable from backend
        price: detail.price,
    });
    toast.success('Mahsulot qaytarishga qoʻshildi!');
};

const removeReturnItem = (id: number) => {
    form.returns = form.returns.filter(r => r.output_detail_id !== id);
};

const submit = () => {
    form.post(route('inventory-returns.store'), {
        onSuccess: () => toast.success('Qaytarish muvaffaqiyatli amalga oshirildi!'),
        onError: (errors) => Object.values(errors).forEach(e => toast.error(e as string)),
    });
};
</script>

<template>
    <Head title="Tovar Qaytarish" />
    <AppLayout>
        <div class="p-4 md:p-8 space-y-6">
            <Card>
                <CardHeader>
                    <CardTitle>Tovar Qaytarish</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="w-full max-w-md mb-4">
                        <Select v-model="selectedOutputId">
                            <SelectTrigger>
                                <SelectValue placeholder="Chiqim tanlang" />
                            </SelectTrigger>
                            <SelectContent>
                                <div class="p-2">
                                    <Input v-model="outputSearchTerm" placeholder="Qidiruv..." @keydown.stop />
                                </div>
                                <SelectItem v-for="o in filteredOutputs" :key="o.id" :value="o.id">
                                    {{ o.output_number }} - {{ o.formatted_date }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div v-if="selectedOutput" class="border rounded-lg mb-6">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Mahsulot</TableHead>
                                    <TableHead>Qaytarish Mumkin</TableHead>
                                    <TableHead>Narxi</TableHead>
                                    <TableHead></TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="detail in selectedOutput.output_details" :key="detail.id">
                                    <TableCell>{{ detail.inventory_entry.product_name }}</TableCell>
                                    <TableCell>{{ detail.remaining_returnable }}</TableCell>
                                    <TableCell>{{ detail.price }}</TableCell>
                                    <TableCell class="text-right">
                                        <Button @click="addReturnItem(detail)">
                                            <IterationCcw class="mr-2 h-4 w-4" /> Qaytarish
                                        </Button>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>

                    <form @submit.prevent="submit" v-if="form.returns.length > 0">
                        <h3 class="text-lg font-medium mb-4">Qaytariladiganlar Ro'yxati</h3>
                        <div class="border rounded-lg mb-4">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Mahsulot</TableHead>
                                        <TableHead>Narxi</TableHead>
                                        <TableHead>Miqdori</TableHead>
                                        <TableHead></TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-for="(item, index) in form.returns" :key="item.output_detail_id">
                                        <TableCell>{{ item.product_name }}</TableCell>
                                        <TableCell>{{ item.price }}</TableCell>
                                        <TableCell>
                                            <Input
                                                type="number"
                                                v-model="item.quantity"
                                                :max="item.max_quantity"
                                                min="0.01"
                                                step="0.01"
                                                class="w-32"
                                            />
                                        </TableCell>
                                        <TableCell class="text-right">
                                            <Button type="button" variant="destructive" size="icon" @click="removeReturnItem(item.output_detail_id)">
                                                <Trash2 class="h-4 w-4" />
                                            </Button>
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </div>
                        <Textarea v-model="form.comment" placeholder="Izoh..." />
                        <div class="flex justify-end mt-4">
                            <Button type="submit" :disabled="form.processing">Saqlash</Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
