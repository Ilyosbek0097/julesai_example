<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { toast } from 'vue-sonner';

// UI Components
import { Button } from '@/components/ui/button';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import { Plus, Minus, ArrowDownToLine } from 'lucide-vue-next';

const props = defineProps<{
    inventories: {
        id: number;
        name: string;
        max_quantity: number; // Changed from quantity to max_quantity
        unit?: { name: string };
    }[];
}>();

// Form state
const form = useForm({
    outputs: [
        {
            inventory_id: '',
            quantity: null,
        },
    ],
    comment: '',
});

// Search terms for each row's dropdown
const searchTerms = ref<string[]>(['']);

// IDs of all inventories that have been selected in any row
const selectedInventoryIds = computed(() =>
    form.outputs.map((o) => o.inventory_id).filter(Boolean)
);

// Function to get available inventories for a specific row
const availableInventoriesForRow = (rowIndex: number) => {
    const currentItemId = form.outputs[rowIndex]?.inventory_id;
    const currentSearchTerm = searchTerms.value[rowIndex]?.toLowerCase() ?? '';

    const unselected = props.inventories.filter(
        (inv) => !selectedInventoryIds.value.includes(inv.id.toString()) || inv.id.toString() === currentItemId
    );

    if (currentSearchTerm) {
        return unselected.filter(
            (inv) =>
                inv.name.toLowerCase().includes(currentSearchTerm) ||
                (inv.unit?.name ?? '').toLowerCase().includes(currentSearchTerm)
        );
    }
    return unselected;
};

// Get inventory object by ID
const getInventoryById = (id: string | number) => {
    if (!id) return null;
    return props.inventories.find((inv) => inv.id.toString() === id.toString());
};

// Add a new row
const addRow = () => {
    form.outputs.push({
        inventory_id: '',
        quantity: null,
    });
    searchTerms.value.push('');
};

// Remove a row
const removeRow = (index: number) => {
    form.outputs.splice(index, 1);
    searchTerms.value.splice(index, 1);
};

// Set quantity to max available
const setMaxQuantity = (index: number) => {
    const row = form.outputs[index];
    const inventory = getInventoryById(row.inventory_id);
    if (inventory) {
        row.quantity = inventory.max_quantity;
    }
};

// Watch for quantity changes to validate against stock
watch(() => form.outputs, (newOutputs, oldOutputs) => {
    newOutputs.forEach((output, index) => {
        if (output.quantity === null || !output.inventory_id) return;

        const inventory = getInventoryById(output.inventory_id);
        const maxQuantity = inventory?.max_quantity;

        if (maxQuantity !== undefined && output.quantity > maxQuantity) {
            toast.error("Xato: Omborda buncha mahsulot yo'q!", {
                description: `"${inventory.name}" uchun ${maxQuantity} dan ko'p qiymat kirita olmaysiz.`,
            });
            // Reset to max value
            form.outputs[index].quantity = maxQuantity;
        }
    });
}, { deep: true });


// Submit the form
const submit = () => {
    // Check for incomplete rows before submitting
    const hasIncompleteRows = form.outputs.some(
        (row) => !row.inventory_id || !row.quantity || row.quantity <= 0
    );

    if (hasIncompleteRows) {
        toast.warning('Ma’lumotlar to‘liq emas!', {
            description: 'Iltimos, har bir qatordagi mahsulot va miqdorni to‘ldiring.',
        });
        return; // Stop submission
    }

    form.post(route('inventory-outputs.store'), {
        onSuccess: () => {
            form.reset();
            searchTerms.value = [''];
            toast.success('Muvaffaqiyatli saqlandi!');
        },
    });
};
</script>
<template>
    <AppLayout>
        <div class="mx-auto w-4/5 p-6">
            <Card>
                <CardHeader>
                    <CardTitle>Yangi chiqimlar</CardTitle>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-4">
                        <div class="space-y-6">
                            <div v-for="(row, index) in form.outputs" :key="index" class="grid grid-cols-5 items-end gap-4">
                                <!-- Product -->
                                <FormField :name="`outputs.${index}.inventory_id`" class="col-span-2">
                                    <FormItem>
                                        <FormLabel>Mahsulot</FormLabel>
                                        <Select v-model="form.outputs[index].inventory_id">
                                            <SelectTrigger class="w-full">
                                                <SelectValue placeholder="Tanlang" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <div class="p-2">
                                                    <Input v-model="searchTerms[index]" type="text" placeholder="Qidirish..." class="w-full text-sm" @keydown.stop />
                                                </div>
                                                <SelectItem v-for="inv in availableInventoriesForRow(index)" :key="inv.id" :value="inv.id.toString()">
                                                    {{ inv.name }} ({{ inv.unit?.name ?? 'N/A' }})
                                                </SelectItem>
                                            </SelectContent>
                                        </Select>
                                        <FormMessage />
                                    </FormItem>
                                </FormField>

                                <!-- Available Quantity -->
                                <div>
                                    <label class="text-sm font-medium">Mavjud</label>
                                    <div class="mt-2 flex h-10 items-center rounded border bg-gray-100 px-3 py-2">
                                        {{ getInventoryById(row.inventory_id)?.max_quantity ?? '—' }}
                                    </div>
                                </div>

                                <!-- Output Quantity -->
                                <FormField :name="`outputs.${index}.quantity`">
                                    <FormItem>
                                        <FormLabel>Chiqim miqdori</FormLabel>
                                        <div class="flex items-center gap-1">
                                            <FormControl>
                                                <Input
                                                    type="number"
                                                    v-model="form.outputs[index].quantity"
                                                    min="1"
                                                    placeholder="Miqdori"
                                                    class="w-full"
                                                    :disabled="!row.inventory_id"
                                                />
                                            </FormControl>
                                            <Button type="button" @click="setMaxQuantity(index)" :disabled="!row.inventory_id" variant="outline" size="icon">
                                                <ArrowDownToLine class="h-4 w-4" />
                                            </Button>
                                        </div>
                                        <FormMessage />
                                    </FormItem>
                                </FormField>

                                <!-- Action Buttons -->
                                <div class="flex items-center gap-2">
                                    <Button type="button" @click="addRow" v-if="index === form.outputs.length - 1" size="icon">
                                        <Plus class="h-4 w-4" />
                                    </Button>
                                    <Button type="button" @click="removeRow(index)" v-if="form.outputs.length > 1" variant="destructive" size="icon">
                                        <Minus class="h-4 w-4" />
                                    </Button>
                                </div>
                            </div>
                            <FormField v-slot="{ componentField }" name="comment">
                                <FormItem>
                                    <FormLabel>Izoh</FormLabel>
                                    <FormControl>
                                        <Textarea
                                            v-bind="componentField"
                                            rows="2"
                                            placeholder="Chiqarish bo‘yicha qo‘shimcha ma’lumot yozing"
                                            class="w-full"
                                        />
                                    </FormControl>
                                    <FormMessage />
                                </FormItem>
                            </FormField>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-end pt-4">
                            <Button type="submit" :disabled="form.processing" class="w-1/6">
                                {{ form.processing ? 'Saqlanmoqda...' : 'Saqlash' }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
