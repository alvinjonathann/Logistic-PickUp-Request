<template>
  <form @submit.prevent="submit">
    <input v-model="item_type" placeholder="Tipe Barang" required />
    <input v-model.number="quantity" type="number" min="1" placeholder="Jumlah" required />
    <input v-model.number="volume" type="number" step="0.001" placeholder="Volume" required />
    <input v-model="scheduled_at" type="datetime-local" required />
    <button type="submit">Buat Pickup</button>
  </form>
</template>
<script>
import api from '@/services/api';
export default {
  data(){ return { item_type:'', quantity:1, volume:0.1, scheduled_at:'' } },
  methods:{
    async submit(){
      await api.post('appointments', {
        item_type:this.item_type,
        quantity:this.quantity,
        volume:this.volume,
        scheduled_at:this.scheduled_at
      });
      this.$emit('created');
      this.item_type=''; this.quantity=1; this.volume=0.1; this.scheduled_at='';
    }
  }
}
</script>
