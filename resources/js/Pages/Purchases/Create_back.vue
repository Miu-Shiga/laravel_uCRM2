<script setup>
import { getToday } from '@/common'
import { reactive, onMounted, ref, computed } from 'vue'
import { Inertia } from "@inertiajs/inertia";

const props = defineProps({
    customers: Array,
    items: Array
})

const itemList = ref([])

const form = reactive({
    date: null,
    customer_id: null,
    status: true,
    items: []
})

const totalPrice = computed(() => {
    let total = 0; // total を定義
    itemList.value.forEach(item => {
        total += item.price * Number(item.quantity); // 数値に変換
    });
    return total;
})

const storePurchase = () =>{
    itemList.value.forEach( item =>{
        if( item.quantity > 0 ){
            form.items.push({
                id: item.id,
                quantity: item.quantity
            })
        }
    })
    Inertia.post(route('purchases.store'), form)
}

const quantity = ["0", "1", "2", "3", "4", "5", "6", "7", "8", "9"]

onMounted(() => {
    form.date = getToday()
    props.items.forEach(item => {
        itemList.value.push({
            id: item.id,
            name: item.name,
            price: item.price,
            quantity: 0
        })
    })
})

</script>
<template>
<form @submit.prevent="storePurchase">
    日付<br>
    <input type="date" name="date" v-model="form.date"><br>
    会員名<br>
    <select name="customer" v-model="form.customer_id">
        <option v-for="customer in props.customers" :value="customer.id" :key="customer.id">
            {{ customer.id }} : {{ customer.name }}
        </option>
    </select>
    <br>
    商品・サービス<br>
    <table>
        <thead>
            <tr>
                <td>ID</td>
                <td>商品名</td>
                <td>金額</td>
                <td>数量</td>
                <td>小計</td>
            </tr>
        </thead>
        <tbody>
            <tr v-for="item in itemList" :key="item.id">
                <td>{{ item.id }}</td>
                <td>{{ item.name }}</td>
                <td>{{ item.price }}</td>
                <td>
                    <select name="quantity" v-model.number="item.quantity"> 
                        <option v-for="q in quantity" :value="q" :key="q">{{ q }}</option>
                    </select>
                </td>
                <td>
                    {{ item.price * Number(item.quantity) }} <!-- 数値に変換 -->
                </td>
            </tr>
        </tbody>
    </table>
    <br>
    合計：{{ totalPrice }}円<br>
    <button>登録する</button>
    </form>
</template>