<template>
  <div class="container">
    <h2>Dashboard</h2>

    <button @click="logout">Logout</button>

    <h3>Buat Appointment</h3>
    <form @submit.prevent="createAppointment">
      <label>Tipe Barang</label>
      <input v-model="item_type" />

      <label>Jumlah</label>
      <input type="number" v-model="quantity" />

      <label>Volume</label>
      <input type="number" step="0.01" v-model="volume" />

      <label>Tanggal Pickup</label>
      <input type="date" v-model="pickup_date" />

      <label>Jam Pickup</label>
      <input type="time" v-model="pickup_time" />

      <button type="submit">Buat</button>
    </form>

    <hr />

    <h3>Ongoing</h3>
    <ul>
      <li v-for="o in ongoing" :key="o.id">
        {{ o.item_type }} - {{ o.pickup_date }} {{ o.pickup_time }}
        <button @click="cancel(o.id)">Cancel</button>
      </li>
    </ul>

    <hr />

    <h3>History</h3>
    <ul>
      <li v-for="h in history" :key="h.id">
        {{ h.item_type }} - {{ h.status }}
      </li>
    </ul>

  </div>
</template>

<script>
import axios from "axios";

export default {
  data() {
    return {
      item_type: "",
      quantity: "",
      volume: "",
      pickup_date: "",
      pickup_time: "",
      ongoing: [],
      history: [],
    };
  },

  async mounted() {
    const token = localStorage.getItem("token");
    if (!token) return this.$router.push("/login");

    axios.defaults.headers.common["Authorization"] = "Bearer " + token;

    this.loadData();
  },

  methods: {
    async loadData() {
      const ong = await axios.get("http://localhost:8000/api/appointments/ongoing");
      const his = await axios.get("http://localhost:8000/api/appointments/history");

      this.ongoing = ong.data;
      this.history = his.data;
    },

    async createAppointment() {
      await axios.post("http://localhost:8000/api/appointments", {
        item_type: this.item_type,
        quantity: this.quantity,
        volume: this.volume,
        pickup_date: this.pickup_date,
        pickup_time: this.pickup_time,
      });

      this.loadData();
    },

    async cancel(id) {
      await axios.delete(`http://localhost:8000/api/appointments/${id}`);
      this.loadData();
    },

    async logout() {
      await axios.post("http://localhost:8000/api/logout");
      localStorage.removeItem("token");
      this.$router.push("/login");
    },
  },
};
</script>

<style>
.container {
  width: 500px;
  margin: 20px auto;
}
input, button {
  margin-bottom: 10px;
  padding: 8px;
}
</style>
