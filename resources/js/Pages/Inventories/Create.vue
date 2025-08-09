<script setup lang="ts">
import { ref, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import Layout from '@/Components/Layout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogFooter,
    DialogTitle,
} from '@/components/ui/dialog'

const props = defineProps({
    categories: Array,
    units: Array,
    suppliers: Array
})

const form = useForm({
    name: '',
    category_id: '',
    unit_id: '',
    supplier_id: ''
})

const categoryOptions = ref(props.categories.map(cat => ({ label: cat.name, value: cat.id })))
const unitOptions = ref(props.units.map(unit => ({ label: unit.name, value: unit.id })))
const supplierOptions = ref(props.suppliers.map(sup => ({ label: sup.name, value: sup.id })))

const showCategoryModal = ref(false)
const showUnitModal = ref(false)
const showSupplierModal = ref(false)

const newCategory = ref('')
const newUnit = ref('')
const newSupplier = ref('')

const categorySearchTerm = ref('')
const unitSearchTerm = ref('')
const supplierSearchTerm = ref('')

const filteredCategories = computed(() =>
    categoryOptions.value.filter(c => c.label.toLowerCase().includes(categorySearchTerm.value.toLowerCase()))
)
const filteredUnits = computed(() =>
    unitOptions.value.filter(u => u.label.toLowerCase().includes(unitSearchTerm.value.toLowerCase()))
)
const filteredSuppliers = computed(() =>
    supplierOptions.value.filter(s => s.label.toLowerCase().includes(supplierSearchTerm.value.toLowerCase()))
)

const submit = () => {
    form.post(route('inventories.store'))
}

const addCategory = async () => {
    // Note: axios is not defined in the original script. Assuming it's globally available.
    const res = await axios.post(route('categories.store'), { name: newCategory.value })
    categoryOptions.value.push({ label: res.data.name, value: res.data.id })
    form.category_id = res.data.id
    showCategoryModal.value = false
    newCategory.value = ''
}

const addUnit = async () => {
    // Note: axios is not defined in the original script. Assuming it's globally available.
    const res = await axios.post(route('units.store'), { name: newUnit.value })
    unitOptions.value.push({ label: res.data.name, value: res.data.id })
    form.unit_id = res.data.id
    showUnitModal.value = false
    newUnit.value = ''
}

const addSupplier = async () => {
    // Note: axios is not defined in the original script. Assuming it's globally available.
    const res = await axios.post(route('suppliers.store'), { name: newSupplier.value })
    supplierOptions.value.push({ label: res.data.name, value: res.data.id })
    form.supplier_id = res.data.id
    showSupplierModal.value = false
    newSupplier.value = ''
}
</script>

<template>
    <Layout>
        <div class="max-w-xl mx-auto space-y-6">
            <h1 class="text-2xl font-bold">Yangi Mahsulot Qo‘shish</h1>

            <form @submit.prevent="submit">
                <div class="space-y-4">
                    <!-- Nomi -->
                    <Input v-model="form.name" placeholder="Mahsulot nomi" />

                    <!-- Kategoriya tanlash -->
                    <div class="flex items-center gap-2">
                        <Select v-model="form.category_id">
                            <SelectTrigger>
                                <SelectValue placeholder="Kategoriya tanlang" />
                            </SelectTrigger>
                            <SelectContent>
                                <div class="p-2">
                                    <Input v-model="categorySearchTerm" placeholder="Qidiruv..." @keydown.stop />
                                </div>
                                <SelectItem v-for="option in filteredCategories" :key="option.value" :value="option.value">
                                    {{ option.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <Button type="button" @click="showCategoryModal = true" size="sm">+ Yangi</Button>
                    </div>

                    <!-- Unit tanlash -->
                    <div class="flex items-center gap-2">
                        <Select v-model="form.unit_id">
                            <SelectTrigger>
                                <SelectValue placeholder="Birlik tanlang" />
                            </SelectTrigger>
                            <SelectContent>
                                <div class="p-2">
                                    <Input v-model="unitSearchTerm" placeholder="Qidiruv..." @keydown.stop />
                                </div>
                                <SelectItem v-for="option in filteredUnits" :key="option.value" :value="option.value">
                                    {{ option.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <Button type="button" @click="showUnitModal = true" size="sm">+ Yangi</Button>
                    </div>

                    <!-- Supplier tanlash -->
                    <div class="flex items-center gap-2">
                        <Select v-model="form.supplier_id">
                            <SelectTrigger>
                                <SelectValue placeholder="Yetkazib beruvchi tanlang" />
                            </SelectTrigger>
                            <SelectContent>
                                <div class="p-2">
                                    <Input v-model="supplierSearchTerm" placeholder="Qidiruv..." @keydown.stop />
                                </div>
                                <SelectItem v-for="option in filteredSuppliers" :key="option.value" :value="option.value">
                                    {{ option.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <Button type="button" @click="showSupplierModal = true" size="sm">+ Yangi</Button>
                    </div>

                    <!-- Submit -->
                    <Button type="submit">Saqlash</Button>
                </div>
            </form>
        </div>

        <!-- Category Modal -->
        <Dialog v-model:open="showCategoryModal">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Yangi Kategoriya Qo‘shish</DialogTitle>
                </DialogHeader>
                <Input v-model="newCategory" placeholder="Kategoriya nomi" />
                <DialogFooter>
                    <Button @click="addCategory">Qo‘shish</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Unit Modal -->
        <Dialog v-model:open="showUnitModal">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Yangi Birlik Qo‘shish</DialogTitle>
                </DialogHeader>
                <Input v-model="newUnit" placeholder="Birlik nomi" />
                <DialogFooter>
                    <Button @click="addUnit">Qo‘shish</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Supplier Modal -->
        <Dialog v-model:open="showSupplierModal">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Yangi Yetkazib Beruvchi Qo‘shish</DialogTitle>
                </DialogHeader>
                <Input v-model="newSupplier" placeholder="Yetkazib beruvchi nomi" />
                <DialogFooter>
                    <Button @click="addSupplier">Qo‘shish</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </Layout>
</template>
