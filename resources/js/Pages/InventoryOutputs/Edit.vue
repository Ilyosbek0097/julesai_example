<script setup lang="ts">
import { ref, computed, watch } from 'vue';
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
import { Plus, Trash2 } from 'lucide-vue-next';

const props = defineProps<{
    output: any;
    entries: any[];
}>();

const form = useForm({
    output_date: props.output.output_date,
    comment: props.output.comment,
    items: props.output.output_details.map((detail: any) => ({
        inventory_entry_id: detail.inventory_entry_id,
        quantity: detail.quantity,
        price: detail.price,
        max_quantity: props.entries.find(e => e.value === detail.inventory_entry_id)?.quantity + detail.quantity || detail.quantity,
    })),
});

const entryOptions = ref(props.entries);

const addItemRow = () => {
    form.items.push({
        inventory_entry_id: null,
        quantity: null,
        price: null,
        max_quantity: 0,
    });
};

const removeItemRow = (index: number) => {
    form.items.splice(index, 1);
};

const selectedEntryIds = computed(() =>
    form.items.map((item) => item.inventory_entry_id).filter(Boolean)
);

const onEntrySelect = (item: any, selectedEntryId: number) => {
    const selectedEntry = entryOptions.value.find(e => e.value === selectedEntryId);
    if (selectedEntry) {
        item.max_quantity = selectedEntry.quantity;
        item.quantity = 1;
    }
};

watch(() => form.items, (newItems) => {
    newItems.forEach(item => {
        if (item.quantity && item.max_quantity && item.quantity > item.max_quantity) {
            toast.warning('Miqdor cheklandi!', {
                description: `Mavjud miqdor ${item.max_quantity} dan oshmasligi kerak.`
            });
            item.quantity = item.max_quantity;
        }
    });
}, { deep: true });

const submit = () => {
    if (form.items.length === 0) {
        return toast.error('Kamida bitta mahsulot qo\'shing.');
    }
    const hasIncompleteRows = form.items.some(
        item => !item.inventory_entry_id || !item.quantity || !item.price || item.quantity <= 0 || item.price <= 0
    );
    if (hasIncompleteRows) {
        return toast.error('Iltimos, barcha qatorlarni to\'liq to\'ldiring.');
    }

    form.put(route('inventory-outputs.update', props.output.id), {
        onSuccess: () => {
            toast.success('Chiqim muvaffaqiyatli yangilandi!');
        },
        onError: (errors) => {
             Object.values(errors).forEach(error => {
                toast.error(error);
            });
        }
    });
};
</script>

<template>
    <Head :title="`Chiqimni Tahrirlash #${output.output_number}`" />

    <AppLayout>
        <div class="p-4 md:p-8">
            <Card>
                <CardHeader>
                    <CardTitle>Chiqimni Tahrirlash</CardTitle>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="output_date">Chiqim Sanasi</label>
                                <Input id="output_date" type="date" v-model="form.output_date" class="mt-1" />
                            </div>
                            <div>
                                <label for="comment">Izoh</label>
                                <Textarea id="comment" v-model="form.comment" class="mt-1" />
                            </div>
                        </div>

                        <div class="border rounded-lg">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead class="w-2/5">Mahsulot</TableHead>
                                        <TableHead>Miqdori</TableHead>
                                        <TableHead>Narxi (1 dona)</TableHead>
                                        <TableHead></TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-for="(item, index) in form.items" :key="index">
                                        <TableCell>
                                            <Select v-model="item.inventory_entry_id" @update:modelValue="(value) => onEntrySelect(item, value)">
                                                <SelectTrigger>
                                                    <SelectValue placeholder="Mahsulotni tanlang..." />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem
                                                        v-for="entry in entryOptions"
                                                        :key="entry.value"
                                                        :value="entry.value"
                                                        :disabled="selectedEntryIds.includes(entry.value) && item.inventory_entry_id !== entry.value"
                                                    >
                                                        {{ entry.label }}
                                                    </SelectItem>
                                                </SelectContent>
                                            </Select>
                                        </TableCell>
                                        <TableCell>
                                            <Input
                                                type="number"
                                                v-model.number="item.quantity"
                                                :max="item.max_quantity"
                                                min="0.01"
                                                step="0.01"
                                                placeholder="Miqdori"
                                                :disabled="!item.inventory_entry_id"
                                            />
                                        </TableCell>
                                        <TableCell>
                                            <Input
                                                type="number"
                                                v-model.number="item.price"
                                                min="0"
                                                placeholder="Narxi"
                                                :disabled="!item.inventory_entry_id"
                                            />
                                        </TableCell>
                                        <TableCell class="text-right">
                                            <Button type="button" variant="destructive" size="icon" @click="removeItemRow(index)">
                                                <Trash2 class="h-4 w-4" />
                                            </Button>
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </div>

                        <div class="flex items-center justify-between">
                            <Button type="button" variant="outline" @click="addItemRow">
                                <Plus class="mr-2 h-4 w-4" />
                                Mahsulot Qo'shish
                            </Button>
                            <Button type="submit" :disabled="form.processing">
                                Yangilash
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
