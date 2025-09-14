<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Label } from '@/components/ui/label';

const props = defineProps<{
    inventories: { id: number; name: string }[];
}>();

const form = useForm({
    inventory_id: null as number | null,
    quantity: null as number | null,
    comment: '',
});

const submit = () => {
    if (!form.inventory_id) {
        return toast.error('Iltimos, mahsulotni tanlang.');
    }
    form.post(route('product-returns.store'), {
        onSuccess: () => {
            toast.success('Tovar muvaffaqiyatli qaytarildi!');
            form.reset();
        },
        onError: (errors) => {
            Object.values(errors).forEach(e => toast.error(e as string));
        },
    });
};
</script>

<template>
    <Head title="Tovar bo'yicha Qaytarish" />
    <AppLayout>
        <div class="p-4 md:p-8">
            <Card class="max-w-2xl mx-auto">
                <CardHeader>
                    <CardTitle>Tovar bo'yicha Qaytarish</CardTitle>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <Label for="inventory">Mahsulot</Label>
                             <Select v-model="form.inventory_id">
                                <SelectTrigger id="inventory">
                                    <SelectValue placeholder="Mahsulotni tanlang..." />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="inventory in inventories" :key="inventory.id" :value="inventory.id">
                                        {{ inventory.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div>
                            <Label for="quantity">Miqdori</Label>
                            <Input
                                id="quantity"
                                type="number"
                                v-model="form.quantity"
                                placeholder="Qaytariladigan miqdorni kiriting"
                                required
                                min="0.01"
                                step="0.01"
                            />
                        </div>

                        <div>
                            <Label for="comment">Izoh</Label>
                            <Textarea
                                id="comment"
                                v-model="form.comment"
                                placeholder="Qo'shimcha izoh qoldirishingiz mumkin..."
                            />
                        </div>

                        <div class="flex justify-end">
                            <Button type="submit" :disabled="form.processing">
                                Qaytarishni Saqlash
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
