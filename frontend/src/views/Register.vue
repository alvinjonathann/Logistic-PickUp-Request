<template>
  <div class="container">
    <h2>Register</h2>

    <form @submit.prevent="register">
      <label>Nama</label>
      <input type="text" v-model="name" required />

      <label>Email</label>
      <input type="email" v-model="email" required />

      <label>Password</label>
      <input type="password" v-model="password" required />

      <button type="submit">Daftar</button>
    </form>

    <p>Sudah punya akun? <router-link to="/login">Login</router-link></p>

    <p style="color:red">{{ error }}</p>
  </div>
</template>

<script>
import axios from "axios";

export default {
  data() {
    return {
      name: "",
      email: "",
      password: "",
      error: "",
    };
  },
  methods: {
    async register() {
      this.error = "";

      try {
        await axios.post("http://localhost:8000/api/register", {
          name: this.name,
          email: this.email,
          password: this.password,
        });

        this.$router.push("/login");
      } catch (err) {
        this.error = "Register gagal";
      }
    },
  },
};
</script>

<style>
.container {
  width: 300px;
  margin: 40px auto;
  display: flex;
  flex-direction: column;
}
input, button {
  margin-bottom: 10px;
  padding: 8px;
}
</style>
