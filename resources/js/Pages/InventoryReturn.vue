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

const props = defineProps<{
    outputs: Array<any>
}>()

const selectedOutputId = ref<string | number | null>(null)

const form = useForm({
    returns: [] as {
        output_detail_id: number;
        quantity: number;
        product_name: string;
        max_quantity: number;
    }[],
    comment: ''
})

const selectedOutput = computed(() => {
    if (!selectedOutputId.value) return null
    return props.outputs.find(o => o.id == selectedOutputId.value) || null
})

function addReturnItem(detail: any) {
    if (!form.returns.some(r => r.output_detail_id === detail.id)) {
        form.returns.push({
            output_detail_id: detail.id,
            quantity: 1,
            product_name: detail.inventory_entry.product_name,
            max_quantity: detail.quantity
        })
    }
}

function removeReturnItem(id: number) {
    form.returns = form.returns.filter(r => r.output_detail_id !== id)
}

function submitForm() {
    form.transform(data => ({
        ...data,
        returns: data.returns.map(item => ({
            output_detail_id: item.output_detail_id,
            quantity: item.quantity
        }))
    })).post(route('inventory-returns.store'))
}

// Watch for changes in return quantities to enforce validation
watch(() => form.returns, (newReturns) => {
    newReturns.forEach(item => {
        if (item.quantity > item.max_quantity) {
            item.quantity = item.max_quantity
        }
        if (item.quantity < 1) {
            item.quantity = 1
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
            <Select v-model="selectedOutputId">
                <SelectTrigger>
                    <SelectValue placeholder="Chiqim tanlang" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem
                        v-for="o in props.outputs"
                        :key="o.id"
                        :value="o.id"
                    >
                        {{ o.output_number }} - {{ o.formatted_date }}
                    </SelectItem>
                </SelectContent>
            </Select>

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
                                    variant="outline"
                                    size="sm"
                                    @click="addReturnItem(detail)"
                                >
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
                            <TableHead>Miqdor</TableHead>
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
                            <TableCell>
                                <Input
                                    type="number"
                                    v-model.number="item.quantity"
                                    min="1"
                                    :max="item.max_quantity"
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
                <Button @click="submitForm" :disabled="form.processing || form.returns.length === 0">
                    Saqlash
                </Button>
            </div>
        </div>
    </AppLayout>
</template>
