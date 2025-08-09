<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import {
    Select,
    SelectTrigger,
    SelectValue,
    SelectContent,
    SelectItem
} from '@/components/ui/select'
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow
} from '@/components/ui/table'
import AppLayout from '@/layouts/AppLayout.vue'
import { toast } from 'vue-sonner'
import { IterationCcw } from 'lucide-vue-next'

const props = defineProps<{
    outputs: Array<any>
}>()

const selectedOutputId = ref<string | number | null>(null)
const outputSearchTerm = ref('')

const form = useForm({
    returns: [] as {
        output_detail_id: number;
        quantity: number;
        product_name: string;
        max_quantity: number;
    }[],
    comment: ''
})

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
    if (!selectedOutputId.value) return null
    return props.outputs.find(o => o.id == selectedOutputId.value) || null
})

const hasInvalidQuantities = computed(() => {
    return form.returns.some(item => !item.quantity || item.quantity < 0.01 || item.quantity > item.max_quantity);
});

function addReturnItem(detail: any) {
    if (form.returns.some(r => r.output_detail_id === detail.id)) {
        toast.warning('Mahsulot roʻyxatda mavjud!', {
            description: 'Bu mahsulot allaqachon qaytarish roʻyxatiga qoʻshilgan.'
        })
        return;
    }
    form.returns.push({
        output_detail_id: detail.id,
        quantity: 1,
        product_name: detail.inventory_entry.product_name,
        max_quantity: detail.quantity
    });
    toast.success('Mahsulot qoʻshildi!', {
        description: `"${detail.inventory_entry.product_name}" qaytarish roʻyxatiga qoʻshildi.`
    })
}

function removeReturnItem(id: number) {
    const itemToRemove = form.returns.find(r => r.output_detail_id === id);
    if (itemToRemove) {
        form.returns = form.returns.filter(r => r.output_detail_id !== id)
        toast.info('Mahsulot oʻchirildi!', {
            description: `"${itemToRemove.product_name}" qaytarish roʻyxatidan olib tashlandi.`
        })
    }
}

function submitForm() {
    form.transform(data => ({
        ...data,
        returns: data.returns.map(item => ({
            output_detail_id: item.output_detail_id,
            quantity: item.quantity
        }))
    })).post(route('inventory-returns.store'), {
        onSuccess: () => {
            form.reset();
            toast.success('Muvaffaqiyatli saqlandi!', {
                description: 'Qaytarish amaliyoti muvaffaqiyatli yakunlandi.'
            });
        },
        onError: () => {
            toast.error('Xatolik yuz berdi!', {
                description: 'Serverda xatolik yuz berdi. Iltimos, qaytadan urinib ko\'ring.'
            });
        }
    })
}

// Watch for changes in return quantities to enforce validation
watch(() => form.returns, (newReturns, oldReturns) => {
    newReturns.forEach((item, index) => {
        if (item.quantity === null || item.quantity === undefined) return;

        // Round to 2 decimal places to prevent more than 2 digits after comma
        const roundedQuantity = parseFloat(Number(item.quantity).toFixed(2));
        if (item.quantity !== roundedQuantity) {
            item.quantity = roundedQuantity;
        }

        const oldItem = oldReturns.find(o => o.output_detail_id === item.output_detail_id);
        const oldValue = oldItem ? oldItem.quantity : 0;

        if (item.quantity > item.max_quantity) {
            item.quantity = item.max_quantity;
            if (oldValue !== item.max_quantity) {
                toast.warning('Miqdor cheklandi!', {
                    description: `"${item.product_name}" uchun maksimal miqdor ${item.max_quantity} dona.`
                });
            }
        }
        if (item.quantity < 0.01) {
            item.quantity = 0.01;
             if (oldValue !== 0.01) {
                toast.warning('Miqdor cheklandi!', {
                    description: `Minimal miqdor 0.01 bo'lishi kerak.`
                });
            }
        }
    })
}, { deep: true })

</script>

<template>
    <Head title="Tovar Qaytarish" />

    <AppLayout>
        <div class="mx-auto w-full space-y-6 p-4">
            <h1 class="text-2xl font-bold">Tovar Qaytarish</h1>
            <!-- Chiqim tanlash -->
            <div class="w-full max-w-md">
                <Select v-model="selectedOutputId">
                    <SelectTrigger>
                        <SelectValue placeholder="Chiqim tanlang" />
                    </SelectTrigger>
                    <SelectContent>
                        <div class="p-2">
                           <Input v-model="outputSearchTerm" @keydown.stop placeholder="Qidiruv..." />
                        </div>
                        <SelectItem
                            v-for="o in filteredOutputs"
                            :key="o.id"
                            :value="o.id"
                        >
                            {{ o.output_number }} - {{ o.formatted_date }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <!-- Chiqim detallarini ko'rsatish -->
            <div v-if="selectedOutput">
                <h2 class="mt-4 text-lg font-semibold">Mahsulotlar</h2>
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Mahsulot</TableHead>
                            <TableHead>Miqdor</TableHead>
                            <TableHead>Narx</TableHead>
                            <TableHead>Sana</TableHead>
                            <TableHead></TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="detail in selectedOutput.output_details"
                            :key="detail.id"
                        >
                            <TableCell>{{ detail.inventory_entry.product_name }}</TableCell>
                            <TableCell>{{ detail.quantity }}</TableCell>
                            <TableCell>{{ detail.price }}</TableCell>
                            <TableCell>{{ detail.created_at }}</TableCell>
                            <TableCell>
                                <Button
                                    variant="default"
                                    size="sm"
                                    @click="addReturnItem(detail)"
                                >
                                    <IterationCcw class="mr-2 h-4 w-4" />
                                    Qaytarish
                                </Button>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <!-- Qaytariladigan mahsulotlar -->
            <div v-if="form.returns.length > 0">
                <h2 class="mt-4 text-lg font-semibold">Qaytariladiganlar</h2>
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Mahsulot</TableHead>
                            <TableHead>Sotilgan Miqdor</TableHead>
                            <TableHead>Qaytariladigan Miqdor</TableHead>
                            <TableHead></TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="item in form.returns"
                            :key="item.output_detail_id"
                        >
                            <TableCell>
                                {{ item.product_name }}
                            </TableCell>
                            <TableCell class="font-medium">
                                {{ item.max_quantity }}
                            </TableCell>
                            <TableCell>
                                <Input
                                    type="number"
                                    v-model.number="item.quantity"
                                    step="0.01"
                                    min="0.01"
                                    :max="item.max_quantity"
                                    class="w-24"
                                />
                            </TableCell>
                            <TableCell>
                                <Button
                                    variant="destructive"
                                    size="sm"
                                    @click="removeReturnItem(item.output_detail_id)"
                                >
                                    O'chirish
                                </Button>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <!-- Izoh -->
            <Input placeholder="Izoh" v-model="form.comment" />

            <!-- Yuborish -->
            <div class="flex justify-end">
                <Button @click="submitForm" :disabled="form.processing || form.returns.length === 0 || hasInvalidQuantities">
                    Saqlash
                </Button>
            </div>
        </div>
    </AppLayout>
</template>
